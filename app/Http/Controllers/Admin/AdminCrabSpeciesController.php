<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CrabSpecies;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCrabSpeciesController extends Controller
{
    public function index(Request $request)
    {
        $query = CrabSpecies::query()->latest();
        if ($request->filled('q')) {
            $query->where(function ($inner) use ($request) {
                $term = '%'.$request->q.'%';
                $inner->where('common_name', 'like', $term)
                    ->orWhere('scientific_name', 'like', $term)
                    ->orWhere('local_name', 'like', $term)
                    ->orWhere('family', 'like', $term);
            });
        }
        if ($request->filled('support')) {
            $query->where('is_supported', $request->support === 'supported');
        }
        if ($request->filled('active')) {
            $query->where('is_active', $request->active === 'active');
        }

        return view('admin.species.index', [
            'species' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('admin.species.create', ['species' => new CrabSpecies()]);
    }

    public function store(Request $request)
    {
        $species = CrabSpecies::create($this->validated($request));
        $this->audit($request, 'created', $species, null, $species->toArray());

        return redirect()->route('admin.species.index')->with('status', 'Species added.');
    }

    public function edit(CrabSpecies $species)
    {
        return view('admin.species.edit', compact('species'));
    }

    public function update(Request $request, CrabSpecies $species)
    {
        $old = $species->toArray();
        $species->update($this->validated($request, $species));
        $this->audit($request, 'updated', $species, $old, $species->fresh()->toArray());

        return redirect()->route('admin.species.index')->with('status', 'Species updated.');
    }

    public function destroy(Request $request, CrabSpecies $species)
    {
        $old = $species->toArray();
        $species->delete();
        $this->audit($request, 'deleted', $species, $old, null);

        return redirect()->route('admin.species.index')->with('status', 'Species deleted.');
    }

    private function validated(Request $request, ?CrabSpecies $species = null): array
    {
        $data = $request->validate([
            'common_name' => ['required', 'string', 'max:255'],
            'scientific_name' => ['required', 'string', 'max:255', Rule::unique('crab_species', 'scientific_name')->ignore($species)],
            'local_name' => ['nullable', 'string', 'max:255'],
            'family' => ['nullable', 'string', 'max:255'],
            'classification' => ['nullable', 'string', 'max:255'],
            'habitat' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:4000'],
            'visual_characteristics' => ['nullable', 'string', 'max:4000'],
            'edible_status' => ['nullable', 'string', 'max:255'],
            'caution_notes' => ['nullable', 'string', 'max:2000'],
            'reference_image_path' => ['nullable', 'string', 'max:255'],
            'reference_name' => ['nullable', 'string', 'max:255'],
            'reference_url' => ['nullable', 'string', 'max:255'],
            'image_credit' => ['nullable', 'string', 'max:255'],
            'model_class_name' => ['nullable', 'string', 'max:255'],
            'model_class_id' => ['nullable', 'integer', 'min:0'],
        ]);

        return $data + [
            'is_supported' => $request->boolean('is_supported'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function audit(Request $request, string $action, CrabSpecies $species, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'species.'.$action,
            'entity_type' => CrabSpecies::class,
            'entity_id' => $species->id,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
