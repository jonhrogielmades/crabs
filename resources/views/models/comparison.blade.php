@extends('layouts.app')
@section('content')
<section class="page-head feature-head model-head">
    <div>
        <p class="eyebrow">AI benchmarking</p>
        <h1>Model Comparison</h1>
    </div>
    <span class="badge model-count-badge"><i data-lucide="bar-chart-3"></i>{{ $rows->count() }} model(s)</span>
</section>

<section class="stats feature-stats model-stats">
    <div class="model-stat"><i data-lucide="gauge"></i><span>Best confidence</span><strong>{{ $bestConfidence?->model_version ?? 'N/A' }}</strong></div>
    <div class="model-stat success"><i data-lucide="clock-3"></i><span>Fastest model</span><strong>{{ $fastest?->model_version ?? 'N/A' }}</strong></div>
    <div class="model-stat info"><i data-lucide="database"></i><span>Registry versions</span><strong>{{ $registry->count() }}</strong></div>
    <div class="model-stat muted-stat"><i data-lucide="shield-check"></i><span>Scope</span><strong>{{ auth()->user()->isAdmin() ? 'All' : 'Mine' }}</strong></div>
</section>

<div class="model-compare-list">
    @forelse($rows as $row)
        @php($reviewAccuracy = $row->expert_reviews > 0 ? $row->expert_matches / $row->expert_reviews : null)
        <article class="panel model-compare-card">
            <header>
                <div>
                    <p class="eyebrow">{{ $row->model_name ?? 'Unknown model' }}</p>
                    <h2>{{ $row->model_version ?? 'N/A' }}</h2>
                </div>
                <span class="badge model-scan-badge">{{ $row->scans }} scans</span>
            </header>
            <div class="metric-bars model-metrics">
                <div><span>Avg confidence</span><strong>{{ $row->avg_confidence ? number_format($row->avg_confidence * 100, 1).'%' : 'N/A' }}</strong></div>
                <div><span>Avg time</span><strong>{{ $row->avg_time ? number_format($row->avg_time).' ms' : 'N/A' }}</strong></div>
                <div><span>High confidence</span><strong>{{ $row->high_count }}</strong></div>
                <div><span>Low / failed</span><strong>{{ $row->low_count + $row->failed_count }}</strong></div>
                <div><span>Review accuracy</span><strong>{{ $reviewAccuracy !== null ? number_format($reviewAccuracy * 100, 1).'%' : 'N/A' }}</strong></div>
            </div>
        </article>
    @empty
        <div class="empty model-empty">No model comparison data yet.</div>
    @endforelse
</div>
@endsection
