@extends('layouts.app')
@section('content')
<section class="page-head feature-head reports-head">
    <div>
        <p class="eyebrow">Export center</p>
        <h1>Reports</h1>
    </div>
    <div class="feature-actions reports-actions">
        <a class="button muted" href="{{ route('recognition.export.csv', request()->query()) }}"><i data-lucide="download"></i>CSV</a>
        <a class="button" href="{{ route('recognition.export.pdf', request()->query()) }}"><i data-lucide="file-text"></i>PDF</a>
    </div>
</section>

<form class="filters feature-filters reports-filters">
    <select name="species">
        <option value="">All species</option>
        @foreach($species as $item)
            <option value="{{ $item->id }}" @selected(request('species') == $item->id)>{{ $item->common_name }}</option>
        @endforeach
    </select>
    <select name="confidence">
        <option value="">Any confidence</option>
        <option value="high" @selected(request('confidence') === 'high')>High</option>
        <option value="moderate" @selected(request('confidence') === 'moderate')>Moderate</option>
        <option value="low" @selected(request('confidence') === 'low')>Low</option>
    </select>
    <input name="date_from" type="date" value="{{ request('date_from') }}">
    <input name="date_to" type="date" value="{{ request('date_to') }}">
    <button class="button small"><i data-lucide="filter"></i>Filter</button>
</form>

<section class="stats feature-stats reports-stats">
    <div class="reports-stat"><i data-lucide="file-search"></i><span>Records</span><strong>{{ $total }}</strong></div>
    <div class="reports-stat success"><i data-lucide="check-circle-2"></i><span>Recognized</span><strong>{{ $recognized }}</strong></div>
    <div class="reports-stat warning"><i data-lucide="alert-triangle"></i><span>Low confidence</span><strong>{{ $low }}</strong></div>
    <div class="reports-stat info"><i data-lucide="gauge"></i><span>Avg confidence</span><strong>{{ $avgConfidence ? number_format($avgConfidence * 100, 1).'%' : 'N/A' }}</strong></div>
</section>

<section class="dashboard-split feature-split reports-split">
    <article class="panel reports-panel">
        <h2>Status Summary</h2>
        <div class="compact-list">
            @forelse($byStatus as $status => $count)
                <div class="compact-row reports-row"><span>{{ str_replace('_', ' ', ucfirst($status)) }}</span><strong>{{ $count }}</strong></div>
            @empty
                <div class="empty">No status data.</div>
            @endforelse
        </div>
    </article>
    <article class="panel reports-panel">
        <h2>Recent Report Rows</h2>
        <div class="compact-list">
            @forelse($records as $record)
                <a class="compact-row reports-row" href="{{ route('recognition.show', $record) }}">
                    <span>{{ $record->scan_reference }}</span>
                    <strong>{{ $record->species?->common_name ?? $record->predicted_class ?? $record->recognition_status }}</strong>
                </a>
            @empty
                <div class="empty">No records found.</div>
            @endforelse
        </div>
    </article>
</section>
{{ $records->links('components.pagination') }}
@endsection
