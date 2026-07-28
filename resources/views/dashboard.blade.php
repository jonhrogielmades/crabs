@extends('layouts.app')
@section('content')
<section class="page-head app-page-head dashboard-head">
    <div><p class="eyebrow">Recognition workspace</p><h1>Welcome, {{ auth()->user()->name }}</h1></div>
    <a class="button dashboard-primary-action" href="{{ route('recognition.create') }}"><i data-lucide="camera"></i>New Scan</a>
</section>
<section class="stats dashboard-stats">
    <div class="dashboard-stat"><span>Total scans</span><strong>{{ $total }}</strong><i class="dashboard-stat-icon" data-lucide="scan-line" aria-hidden="true"></i></div>
    <div class="dashboard-stat success"><span>Successful</span><strong>{{ $successful }}</strong><i class="dashboard-stat-icon" data-lucide="badge-check" aria-hidden="true"></i></div>
    <div class="dashboard-stat warning"><span>Low confidence</span><strong>{{ $low }}</strong><i class="dashboard-stat-icon" data-lucide="shield-alert" aria-hidden="true"></i></div>
    <div class="dashboard-stat info"><span>Crab data</span><strong>{{ $speciesCount }}</strong><i class="dashboard-stat-icon" data-lucide="database" aria-hidden="true"></i></div>
</section>
<section class="status-strip dashboard-status"><span class="status-dot {{ $aiStatus === 'online' ? 'online' : '' }}"></span><div><strong>AI recognition {{ $aiStatus }}</strong><span>{{ $aiStatusDetail }}</span></div></section>
<section class="section-title dashboard-section-title"><div><p class="eyebrow">Activity</p><h2>Recent Results</h2></div><a href="{{ route('recognition.history') }}">View all <i data-lucide="arrow-right"></i></a></section>
<div class="list app-list dashboard-list">
@forelse($recent as $record)
    <a class="row app-row dashboard-row" href="{{ route('recognition.show', $record) }}"><span>{{ $record->scan_reference }}</span><strong>{{ $record->predicted_class ?? ucfirst($record->recognition_status) }}</strong><i data-lucide="arrow-right"></i></a>
@empty
    <div class="empty dashboard-empty">No recognition records yet.</div>
@endforelse
</div>
@endsection
