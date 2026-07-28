@extends('layouts.app')
@section('content')
<section class="page-head">
    <div><p class="eyebrow">AI operations</p><h1>Models</h1></div>
    <form method="post" action="{{ route('admin.models.sync') }}">
        @csrf
        <button class="button" type="submit"><i data-lucide="refresh-cw"></i>Sync AI Service</button>
    </form>
</section>

<section class="stats">
    <div><i data-lucide="activity"></i><strong>{{ $aiStatus }}</strong><span>AI service</span></div>
    <div><i data-lucide="gauge"></i><strong>{{ number_format((float) $threshold * 100, 0) }}%</strong><span>Threshold</span></div>
    <div><i data-lucide="database"></i><strong>{{ $models->total() }}</strong><span>Registered versions</span></div>
    <div><i data-lucide="check-circle-2"></i><strong>{{ $activeModel?->version ?? 'None' }}</strong><span>Active version</span></div>
</section>

<section class="panel model-config-panel">
    <h2>Service Configuration</h2>
    <div class="mini-dl">
        <div><dt>Endpoint</dt><dd>{{ $aiUrl ?: 'Not configured' }}</dd></div>
        <div><dt>Provider order</dt><dd>{{ implode(', ', $providerOrder) ?: 'local' }}</dd></div>
        <div><dt>Active registry model</dt><dd>{{ $activeModel ? $activeModel->name.' '.$activeModel->version : 'None' }}</dd></div>
    </div>
    <div class="mini-dl">
        @foreach($providerStatus as $provider)
            <div>
                <dt>{{ ucfirst($provider['name']) }}</dt>
                <dd>{{ $provider['configured'] ? 'Configured' : 'Missing API key' }} · {{ $provider['model'] ?: 'No model' }}</dd>
            </div>
        @endforeach
    </div>
</section>

<form class="panel admin-form" method="post" action="{{ route('admin.models.store') }}">
    @csrf
    <h2>Add Model Version</h2>
    <div class="field-grid">
        <div class="field-group">
            <label>Name</label>
            <input name="name" value="{{ old('name', 'YOLO Crab Recognition Adapter') }}" required>
        </div>
        <div class="field-group">
            <label>Version</label>
            <input name="version" value="{{ old('version') }}" required>
        </div>
    </div>
    <div class="field-group">
        <label>Description</label>
        <textarea name="description">{{ old('description') }}</textarea>
    </div>
    <div class="field-grid">
        <div class="field-group">
            <label>Confidence threshold</label>
            <input name="confidence_threshold" type="number" min="0" max="1" step="0.001" value="{{ old('confidence_threshold', $threshold) }}" required>
        </div>
        <label class="admin-inline-check model-active-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active'))> Make active</label>
    </div>
    <div class="field-grid">
        <div class="field-group">
            <label>Classes</label>
            <textarea name="classes" placeholder='["Scylla serrata", "Portunus pelagicus"]'>{{ old('classes') }}</textarea>
        </div>
        <div class="field-group">
            <label>Evaluation metrics</label>
            <textarea name="evaluation_metrics" placeholder='{"accuracy":0.91,"mAP50":0.88}'>{{ old('evaluation_metrics') }}</textarea>
        </div>
    </div>
    @foreach($errors->all() as $error)<p class="error">{{ $error }}</p>@endforeach
    <button class="button" type="submit"><i data-lucide="save"></i>Save Model</button>
</form>

<section class="section-title">
    <div><p class="eyebrow">Registry</p><h2>Model Versions</h2></div>
</section>
<div class="admin-table">
    <div class="admin-table-head"><span>Model</span><span>Threshold</span><span>Classes</span><span></span></div>
    @forelse($models as $model)
        <article class="admin-table-row">
            <div>
                <strong>{{ $model->name }}</strong>
                <span>{{ $model->version }}</span>
            </div>
            <div>
                <strong>{{ number_format($model->confidence_threshold * 100, 1) }}%</strong>
                <span>{{ $model->deployed_at?->format('M d, Y') ?? 'Not deployed' }}</span>
            </div>
            <div>
                <strong>{{ count($model->classes ?? []) }}</strong>
                <span>{{ $model->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="admin-row-actions">
                @unless($model->is_active)
                    <form method="post" action="{{ route('admin.models.activate', $model) }}">
                        @csrf
                        @method('patch')
                        <button class="button small" type="submit"><i data-lucide="check-circle-2"></i>Activate</button>
                    </form>
                @else
                    <span class="badge">active</span>
                @endunless
            </div>
        </article>
    @empty
        <div class="empty">No model versions registered.</div>
    @endforelse
</div>
{{ $models->links('components.pagination') }}

<section class="section-title">
    <div><p class="eyebrow">Performance</p><h2>Model Metrics</h2></div>
</section>
<div class="admin-table">
    <div class="admin-table-head"><span>Model</span><span>Scans</span><span>Confidence</span><span>Issues</span></div>
    @forelse($performance as $row)
        <article class="admin-table-row">
            <div>
                <strong>{{ $row->model_name ?? 'Unknown' }}</strong>
                <span>{{ $row->model_version ?? 'N/A' }}</span>
            </div>
            <div><strong>{{ $row->scans }}</strong><span>{{ $row->avg_time ? number_format($row->avg_time).' ms avg' : 'No time data' }}</span></div>
            <div><strong>{{ $row->avg_confidence ? number_format($row->avg_confidence * 100, 1).'%' : 'N/A' }}</strong><span>Average</span></div>
            <div><strong>{{ $row->low_count + $row->failed_count }}</strong><span>{{ $row->low_count }} low &middot; {{ $row->failed_count }} failed</span></div>
        </article>
    @empty
        <div class="empty">No model performance data yet.</div>
    @endforelse
</div>
@endsection
