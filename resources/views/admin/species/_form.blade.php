@php($editing = $species->exists)
<form class="panel admin-form" method="post" action="{{ $editing ? route('admin.species.update', $species) : route('admin.species.store') }}">
    @csrf
    @if($editing) @method('put') @endif

    <div class="field-grid">
        <div class="field-group">
            <label for="common_name">Common name</label>
            <input id="common_name" name="common_name" value="{{ old('common_name', $species->common_name) }}" required>
        </div>
        <div class="field-group">
            <label for="scientific_name">Scientific name</label>
            <input id="scientific_name" name="scientific_name" value="{{ old('scientific_name', $species->scientific_name) }}" required>
        </div>
    </div>

    <div class="field-grid">
        <div class="field-group">
            <label for="local_name">Local name</label>
            <input id="local_name" name="local_name" value="{{ old('local_name', $species->local_name) }}">
        </div>
        <div class="field-group">
            <label for="family">Family</label>
            <input id="family" name="family" value="{{ old('family', $species->family) }}">
        </div>
    </div>

    <div class="field-group">
        <label for="classification">Classification</label>
        <input id="classification" name="classification" value="{{ old('classification', $species->classification) }}">
    </div>

    <div class="field-group">
        <label for="habitat">Habitat</label>
        <textarea id="habitat" name="habitat">{{ old('habitat', $species->habitat) }}</textarea>
    </div>

    <div class="field-group">
        <label for="description">Description</label>
        <textarea id="description" name="description">{{ old('description', $species->description) }}</textarea>
    </div>

    <div class="field-group">
        <label for="visual_characteristics">Visual characteristics</label>
        <textarea id="visual_characteristics" name="visual_characteristics">{{ old('visual_characteristics', $species->visual_characteristics) }}</textarea>
    </div>

    <div class="field-grid">
        <div class="field-group">
            <label for="edible_status">Edible status</label>
            <input id="edible_status" name="edible_status" value="{{ old('edible_status', $species->edible_status) }}">
        </div>
        <div class="field-group">
            <label for="model_class_id">Model class ID</label>
            <input id="model_class_id" name="model_class_id" type="number" min="0" value="{{ old('model_class_id', $species->model_class_id) }}">
        </div>
    </div>

    <div class="field-grid">
        <div class="field-group">
            <label for="model_class_name">Model class name</label>
            <input id="model_class_name" name="model_class_name" value="{{ old('model_class_name', $species->model_class_name) }}">
        </div>
        <div class="field-group">
            <label for="reference_image_path">Reference image</label>
            <input id="reference_image_path" name="reference_image_path" value="{{ old('reference_image_path', $species->reference_image_path) }}">
        </div>
    </div>

    <div class="field-grid">
        <div class="field-group">
            <label for="reference_name">Reference name</label>
            <input id="reference_name" name="reference_name" value="{{ old('reference_name', $species->reference_name) }}">
        </div>
        <div class="field-group">
            <label for="reference_url">Reference URL</label>
            <input id="reference_url" name="reference_url" value="{{ old('reference_url', $species->reference_url) }}">
        </div>
    </div>

    <div class="field-group">
        <label for="image_credit">Image credit</label>
        <input id="image_credit" name="image_credit" value="{{ old('image_credit', $species->image_credit) }}">
    </div>

    <div class="field-group">
        <label for="caution_notes">Caution notes</label>
        <textarea id="caution_notes" name="caution_notes">{{ old('caution_notes', $species->caution_notes) }}</textarea>
    </div>

    <div class="admin-check-row">
        <label><input type="checkbox" name="is_supported" value="1" @checked(old('is_supported', $species->is_supported ?? true))> Supported by model</label>
        <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $species->is_active ?? true))> Active in directory</label>
    </div>

    @foreach($errors->all() as $error)<p class="error">{{ $error }}</p>@endforeach

    <div class="actions">
        <button class="button" type="submit"><i data-lucide="save"></i>{{ $editing ? 'Save Species' : 'Add Species' }}</button>
        <a class="button muted" href="{{ route('admin.species.index') }}"><i data-lucide="arrow-right"></i>Back</a>
    </div>
</form>
