@extends('layouts.app')
@section('content')
<section class="page-head app-page-head dashboard-head">
    <div><p class="eyebrow">Recognition workspace</p><h1>Welcome, {{ auth()->user()->name }}</h1></div>
    <a class="button dashboard-primary-action" href="{{ route('recognition.create') }}"><i data-lucide="camera"></i>New Scan</a>
</section>
<section class="stats dashboard-stats">
    <div class="dashboard-stat"><i data-lucide="file-search"></i><span>Total scans</span><strong>{{ $total }}</strong></div>
    <div class="dashboard-stat success"><i data-lucide="check-circle-2"></i><span>Successful</span><strong>{{ $successful }}</strong></div>
    <div class="dashboard-stat warning"><i data-lucide="alert-triangle"></i><span>Low confidence</span><strong>{{ $low }}</strong></div>
    <div class="dashboard-stat info"><i data-lucide="database"></i><span>Crab data</span><strong>{{ $speciesCount }}</strong></div>
</section>
<section class="status-strip dashboard-status"><span class="status-dot {{ $aiStatus === 'online' ? 'online' : '' }}"></span><div><strong>AI service {{ $aiStatus }}</strong><span>Recognition needs internet or a reachable AI service.</span></div></section>
<section class="section-title dashboard-section-title"><div><p class="eyebrow">Activity</p><h2>Recent Results</h2></div><a href="{{ route('recognition.history') }}">View all <i data-lucide="arrow-right"></i></a></section>
<div class="list app-list dashboard-list">
@forelse($recent as $record)
    <a class="row app-row dashboard-row" href="{{ route('recognition.show', $record) }}"><span>{{ $record->scan_reference }}</span><strong>{{ $record->predicted_class ?? ucfirst($record->recognition_status) }}</strong><i data-lucide="arrow-right"></i></a>
@empty
    <div class="empty dashboard-empty">No recognition records yet.</div>
@endforelse
</div>
@endsection
