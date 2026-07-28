@extends('layouts.app')
@section('content')
<section class="page-head feature-head training-head">
    <div>
        <p class="eyebrow">Model improvement</p>
        <h1>Training Dataset</h1>
    </div>
    <a class="button training-export" href="{{ route('training.export', request()->query()) }}"><i data-lucide="download"></i>Export CSV</a>
</section>

<section class="stats feature-stats training-stats">
    <div class="training-stat"><i data-lucide="database"></i><span>Candidates</span><strong>{{ $candidateCount }}</strong></div>
    <div class="training-stat success"><i data-lucide="clipboard-check"></i><span>Corrected</span><strong>{{ $correctedCount }}</strong></div>
    <div class="training-stat warning"><i data-lucide="alert-triangle"></i><span>Low confidence</span><strong>{{ $lowCount }}</strong></div>
    <div class="training-stat info"><i data-lucide="shield-check"></i><span>Scope</span><strong>{{ auth()->user()->isAdmin() ? 'All' : 'Mine' }}</strong></div>
</section>

<form class="filters feature-filters compact-feature-filters training-filters">
    <select name="species">
        <option value="">Any expert species</option>
        @foreach($species as $item)
            <option value="{{ $item->id }}" @selected(request('species') == $item->id)>{{ $item->common_name }}</option>
        @endforeach
    </select>
    <select name="status">
        <option value="">Any scan status</option>
        <option value="recognized" @selected(request('status') === 'recognized')>Recognized</option>
        <option value="low_confidence" @selected(request('status') === 'low_confidence')>Low confidence</option>
        <option value="no_detection" @selected(request('status') === 'no_detection')>No detection</option>
        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
    </select>
    <button class="button small"><i data-lucide="filter"></i>Filter</button>
</form>

<div class="feature-list training-list">
    @forelse($records as $record)
        <article class="feature-row training-row">
            <div>
                <span>{{ $record->scan_reference }} &middot; {{ $record->created_at->format('M d, Y') }}</span>
                <strong>{{ $record->expertSpecies?->common_name ?? $record->species?->common_name ?? $record->predicted_class ?? 'Unlabeled candidate' }}</strong>
                <small>{{ $record->needs_retraining ? 'Marked for retraining' : 'Quality candidate' }} &middot; {{ $record->confidence ? number_format($record->confidence * 100, 1).'%' : 'N/A' }}</small>
            </div>
            <a class="button muted small" href="{{ route('recognition.show', $record) }}"><i data-lucide="external-link"></i>Open</a>
        </article>
    @empty
        <div class="empty training-empty">No training candidates yet.</div>
    @endforelse
</div>
{{ $records->links('components.pagination') }}
@endsection
