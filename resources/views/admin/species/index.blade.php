@extends('layouts.app')
@section('content')
<section class="page-head">
    <div><p class="eyebrow">Admin library</p><h1>Species</h1></div>
    <a class="button" href="{{ route('admin.species.create') }}"><i data-lucide="plus"></i>Add Species</a>
</section>

<form class="filters admin-filters">
    <label class="input-icon"><i data-lucide="search"></i><input name="q" value="{{ request('q') }}" placeholder="Search species"></label>
    <select name="support">
        <option value="">Any support</option>
        <option value="supported" @selected(request('support') === 'supported')>Supported</option>
        <option value="unsupported" @selected(request('support') === 'unsupported')>Unsupported</option>
    </select>
    <select name="active">
        <option value="">Any status</option>
        <option value="active" @selected(request('active') === 'active')>Active</option>
        <option value="inactive" @selected(request('active') === 'inactive')>Inactive</option>
    </select>
    <button class="button small"><i data-lucide="filter"></i>Filter</button>
</form>

<div class="admin-table">
    <div class="admin-table-head"><span>Name</span><span>Model</span><span>Status</span><span></span></div>
    @forelse($species as $item)
        <article class="admin-table-row">
            <div>
                <strong>{{ $item->common_name }}</strong>
                <span>{{ $item->scientific_name }}</span>
            </div>
            <div>
                <strong>{{ $item->model_class_name ?? 'Unmapped' }}</strong>
                <span>{{ $item->model_class_id !== null ? '#'.$item->model_class_id : 'No class ID' }}</span>
            </div>
            <div class="admin-status-list">
                <span class="badge">{{ $item->is_supported ? 'supported' : 'unsupported' }}</span>
                <span class="badge muted-badge">{{ $item->is_active ? 'active' : 'inactive' }}</span>
            </div>
            <div class="admin-row-actions">
                <a class="icon-button" href="{{ route('admin.species.edit', $item) }}" aria-label="Edit {{ $item->common_name }}"><i data-lucide="pencil"></i></a>
                <form method="post" action="{{ route('admin.species.destroy', $item) }}" onsubmit="return confirm('Delete this species?')">
                    @csrf
                    @method('delete')
                    <button class="icon-button danger" type="submit" aria-label="Delete {{ $item->common_name }}"><i data-lucide="trash-2"></i></button>
                </form>
            </div>
        </article>
    @empty
        <div class="empty">No species found.</div>
    @endforelse
</div>

{{ $species->links('components.pagination') }}
@endsection
