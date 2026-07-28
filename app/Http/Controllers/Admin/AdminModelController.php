<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ModelVersion;
use App\Models\RecognitionRecord;
use App\Services\AiServiceHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdminModelController extends Controller
{
    public function index(AiServiceHealthService $health)
    {
        $performance = RecognitionRecord::query()
            ->select([
                'model_name',
                'model_version',
                DB::raw('count(*) as scans'),
                DB::raw('avg(confidence) as avg_confidence'),
                DB::raw('avg(processing_time_ms) as avg_time'),
                DB::raw("sum(case when confidence_level = 'low' then 1 else 0 end) as low_count"),
                DB::raw("sum(case when recognition_status = 'failed' then 1 else 0 end) as failed_count"),
            ])
            ->whereNotNull('model_version')
            ->groupBy('model_name', 'model_version')
            ->orderByRaw('max(created_at) desc')
            ->limit(10)
            ->get();

        return view('admin.models.index', [
            'models' => ModelVersion::latest()->paginate(8),
            'performance' => $performance,
            'activeModel' => ModelVersion::where('is_active', true)->first(),
            'aiStatus' => $health->status(),
            'aiUrl' => config('services.ai.url'),
            'providerOrder' => config('services.ai.provider_order', []),
            'providerStatus' => $this->providerStatus(),
            'threshold' => config('services.ai.confidence_threshold'),
        ]);
    }

    private function providerStatus(): array
    {
        return collect(config('services.ai.provider_order', []))
            ->reject(fn (string $provider) => $provider === 'local')
            ->map(fn (string $provider) => [
                'name' => $provider,
                'model' => config("services.ai.providers.{$provider}.model"),
                'configured' => filled(config("services.ai.providers.{$provider}.key")),
            ])
            ->values()
            ->all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'confidence_threshold' => ['required', 'numeric', 'between:0,1'],
            'classes' => ['nullable', 'string', 'max:10000'],
            'evaluation_metrics' => ['nullable', 'string', 'max:10000'],
        ]);

        $model = ModelVersion::updateOrCreate(
            ['name' => $data['name'], 'version' => $data['version']],
            [
                'description' => $data['description'] ?? null,
                'confidence_threshold' => $data['confidence_threshold'],
                'classes' => $this->decodeStructuredText($data['classes'] ?? null),
                'evaluation_metrics' => $this->decodeStructuredText($data['evaluation_metrics'] ?? null),
                'deployed_at' => $request->boolean('is_active') ? now() : null,
                'is_active' => $request->boolean('is_active'),
            ]
        );

        if ($request->boolean('is_active')) {
            ModelVersion::where('id', '!=', $model->id)->update(['is_active' => false]);
        }

        $this->audit($request, 'model.saved', $model, $model->toArray());

        return redirect()->route('admin.models.index')->with('status', 'Model version saved.');
    }

    public function activate(Request $request, ModelVersion $modelVersion)
    {
        DB::transaction(function () use ($request, $modelVersion) {
            ModelVersion::where('id', '!=', $modelVersion->id)->update(['is_active' => false]);
            $modelVersion->update(['is_active' => true, 'deployed_at' => $modelVersion->deployed_at ?? now()]);
            $this->audit($request, 'model.activated', $modelVersion, $modelVersion->fresh()->toArray());
        });

        return redirect()->route('admin.models.index')->with('status', 'Active model updated.');
    }

    public function sync(Request $request)
    {
        $baseUrl = rtrim((string) config('services.ai.url'), '/');
        if ($baseUrl === '') {
            return redirect()->route('admin.models.index')->with('status', 'AI service URL is not configured.');
        }

        try {
            $response = Http::timeout(5)->get($baseUrl.'/api/v1/model');
            if (! $response->ok()) {
                return redirect()->route('admin.models.index')->with('status', 'AI service model endpoint returned HTTP '.$response->status().'.');
            }

            $payload = $response->json();
            $model = ModelVersion::updateOrCreate(
                ['name' => data_get($payload, 'name', 'AI model'), 'version' => data_get($payload, 'version', 'unknown')],
                [
                    'description' => data_get($payload, 'model_loaded') ? 'Synced from the active AI service.' : 'Synced from the AI service. Model file is not loaded yet.',
                    'classes' => data_get($payload, 'classes', []),
                    'confidence_threshold' => config('services.ai.confidence_threshold', 0.60),
                    'evaluation_metrics' => data_get($payload, 'evaluation_metrics'),
                    'deployed_at' => now(),
                    'is_active' => true,
                ]
            );
            ModelVersion::where('id', '!=', $model->id)->update(['is_active' => false]);
            $this->audit($request, 'model.synced', $model, $payload);

            return redirect()->route('admin.models.index')->with('status', 'AI service model metadata synced.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.models.index')->with('status', 'AI service model sync failed: '.$e->getMessage());
        }
    }

    private function decodeStructuredText(?string $value): ?array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $json = json_decode($value, true);
        if (is_array($json)) {
            return $json;
        }

        return collect(preg_split('/\R+/', $value) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function audit(Request $request, string $action, ModelVersion $model, array $new): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => ModelVersion::class,
            'entity_id' => $model->id,
            'new_values' => $new,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
