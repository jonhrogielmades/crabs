@extends('layouts.app')
@section('content')
<section class="page-head">
    <div><p class="eyebrow">Admin review</p><h1>Recognition Feedback</h1></div>
    <a class="button muted" href="{{ route('admin.dashboard') }}"><i data-lucide="bar-chart-3"></i>Dashboard</a>
</section>

<form class="filters admin-filters">
    <select name="status">
        <option value="">Any status</option>
        @foreach($statuses as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
        @endforeach
    </select>
    <select name="category">
        <option value="">Any category</option>
        @foreach($categories as $category)
            <option value="{{ $category }}" @selected(request('category') === $category)>{{ str_replace('_', ' ', ucfirst($category)) }}</option>
        @endforeach
    </select>
    <label class="admin-inline-check"><input type="checkbox" name="training_candidates" value="1" @checked(request()->boolean('training_candidates'))> Training candidates</label>
    <button class="button small"><i data-lucide="filter"></i>Filter</button>
</form>

<div class="feedback-review-list">
    @forelse($feedback as $item)
        @php($record = $item->recognitionRecord)
        <article class="panel feedback-review-card">
            <header class="feedback-review-head">
                <div>
                    <p class="eyebrow">{{ str_replace('_', ' ', $item->category) }}</p>
                    <h2>{{ $record?->scan_reference ?? 'Missing recognition record' }}</h2>
                    <span>{{ $item->user?->name }} &middot; {{ $item->created_at->format('M d, Y H:i') }}</span>
                </div>
                <span class="badge">{{ str_replace('_', ' ', $item->status) }}</span>
            </header>

            <div class="feedback-review-grid">
                <div class="feedback-review-copy">
                    <strong>User report</strong>
                    <p>{{ $item->description }}</p>
                    @if($record)
                        <dl class="mini-dl">
                            <div><dt>AI result</dt><dd>{{ $record->species?->common_name ?? $record->predicted_class ?? 'Unknown' }}</dd></div>
                            <div><dt>Confidence</dt><dd>{{ $record->confidence ? number_format($record->confidence * 100, 1).'%' : 'N/A' }}</dd></div>
                            <div><dt>Status</dt><dd>{{ str_replace('_', ' ', $record->recognition_status) }}</dd></div>
                            <div><dt>Location</dt><dd>{{ $record->location_label ?: 'N/A' }}</dd></div>
                        </dl>
                        <a class="button muted small" href="{{ route('recognition.show', $record) }}"><i data-lucide="external-link"></i>Open Result</a>
                    @endif
                </div>

                <form class="feedback-review-form" method="post" action="{{ route('admin.feedback.update', $item) }}">
                    @csrf
                    @method('patch')
                    <div class="field-grid">
                        <div class="field-group">
                            <label>Status</label>
                            <select name="status">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected(old('status', $item->status) === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label>Expert species</label>
                            <select name="expert_species_id">
                                <option value="">No correction</option>
                                @foreach($species as $speciesItem)
                                    <option value="{{ $speciesItem->id }}" @selected(old('expert_species_id', $record?->expert_species_id) == $speciesItem->id)>{{ $speciesItem->common_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <label class="admin-inline-check"><input type="checkbox" name="needs_retraining" value="1" @checked(old('needs_retraining', $record?->needs_retraining))> Mark for retraining</label>
                    <div class="field-group">
                        <label>Admin response</label>
                        <textarea name="admin_response">{{ old('admin_response', $item->admin_response) }}</textarea>
                    </div>
                    <div class="field-group">
                        <label>Internal notes</label>
                        <textarea name="admin_notes">{{ old('admin_notes', $record?->admin_notes) }}</textarea>
                    </div>
                    <button class="button" type="submit"><i data-lucide="save"></i>Save Review</button>
                </form>
            </div>
        </article>
    @empty
        <div class="empty">No feedback found.</div>
    @endforelse
</div>

{{ $feedback->links('components.pagination') }}
@endsection
