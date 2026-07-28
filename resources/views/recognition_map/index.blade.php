@extends('layouts.app')
@section('content')
<section class="page-head feature-head map-head">
    <div>
        <p class="eyebrow">Scan geography</p>
        <h1>Recognition Map</h1>
    </div>
    <span class="badge map-count-badge"><i data-lucide="map-pin"></i>{{ $records->count() }} plotted</span>
</section>

<form class="filters feature-filters map-filters">
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

<section class="map-board recognition-map-board" data-map-points='@json($points)'>
    <div class="map-board-head">
        <span><i data-lucide="map-pin"></i>Located scans</span>
        <strong>{{ $records->count() }}</strong>
    </div>
    <div class="map-grid" id="recognitionMapGrid">
        <div class="map-empty-state">No scan locations match this filter.</div>
    </div>
</section>

<div class="mobile-record-list map-record-list">
    @forelse($records as $record)
        <a class="feature-row map-record-row" href="{{ route('recognition.show', $record) }}">
            <span><i data-lucide="map-pin"></i>{{ $record->location_label ?: number_format($record->latitude, 4).', '.number_format($record->longitude, 4) }}</span>
            <strong>{{ $record->species?->common_name ?? $record->predicted_class ?? 'Unknown crab' }}</strong>
            <small>{{ $record->confidence ? number_format($record->confidence * 100, 1).'%' : 'N/A' }} &middot; {{ $record->created_at->format('M d, Y') }}</small>
        </a>
    @empty
        <div class="empty map-empty-list">No located scans yet.</div>
    @endforelse
</div>
@endsection
