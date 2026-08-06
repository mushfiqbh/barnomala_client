@extends('layouts.admin')

@section('title', 'Edit Speech')

@push('header_actions')
<div class="flex items-center gap-5">
    <button>
        <a href="{{ route('admin.speeches.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </button>
    <button type="submit" form="speech-form" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-bold rounded-lg shadow-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
        <i class="fas fa-save mr-2"></i>
        Update Speech
    </button>
</div>
@endpush

@section('content')
<div class="bg-white rounded-lg shadow max-w-5xl p-6">
    <form id="speech-form" action="{{ route('admin.speeches.update', $speech) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column: Form Fields -->
            <div class="md:col-span-2 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $speech->title) }}" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-accent focus:border-accent" required>
                        @error('title') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Speaker Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $speech->name) }}" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-accent focus:border-accent">
                        @error('name') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>                

                <div>
                    <label for="speech" class="block text-sm font-medium text-gray-700 mb-1">Speech Content</label>
                    <textarea name="speech" id="speech" rows="16" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-accent focus:border-accent" required>{{ old('speech', $speech->speech) }}</textarea>
                    @error('speech') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center pt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $speech->is_active) ? 'checked' : '' }} class="h-4 w-4 text-accent border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700 font-medium">Active</label>
                </div>
            </div>

            <!-- Right Column: Image Upload -->
            <div class="md:col-span-1">
                <div id="drop-area" class="bg-slate-50 rounded-lg border-2 border-dashed border-slate-200 p-4 flex flex-col items-center justify-center h-full min-h-100">
                    <div class="w-full text-center">
                        <input type="file" id="image-input" name="image" accept="image/*" class="hidden" onchange="previewImage(event)">
                        
                        <div id="image-preview" class="mb-4">
                            @if($speech->image_json)
                            <div class="w-24 h-24 mx-auto rounded-full overflow-hidden border-2 border-indigo-300">
                                <img src="{{ $speech->image_url }}" alt="{{ $speech->name }}" class="w-full h-full object-cover">
                            </div>
                            @else
                            <div class="w-24 h-24 mx-auto rounded-full bg-white border-2 border-slate-300 flex items-center justify-center">
                                <i class="fas fa-image text-slate-300 text-3xl"></i>
                            </div>
                            @endif
                        </div>

                        <button type="button" onclick="document.getElementById('image-input').click()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium mb-3">
                            <i class="fas fa-upload mr-2"></i> {{ $speech->image_json ? 'Update' : 'Choose' }} Image
                        </button>

                        <p class="text-xs text-slate-500 mb-3">or drag and drop</p>
                        <p class="text-xs text-slate-400">JPG, PNG, GIF up to 2MB</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewDiv = document.getElementById('image-preview');
            previewDiv.innerHTML = `
                <div class="w-24 h-24 mx-auto rounded-full overflow-hidden border-2 border-indigo-300">
                    <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">
                </div>
                <p class="text-xs text-slate-600 mt-2 truncate">${file.name}</p>
            `;
        };
        reader.readAsDataURL(file);
    }
}

// Drag and drop
const dropArea = document.getElementById('drop-area');
if (dropArea) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => {
            dropArea.classList.add('border-indigo-400', 'bg-indigo-50');
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => {
            dropArea.classList.remove('border-indigo-400', 'bg-indigo-50');
        });
    });
    
    dropArea.addEventListener('drop', handleDrop);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            const input = document.getElementById('image-input');
            input.files = files;
            const event = { target: { files: files } };
            previewImage(event);
        }
    }
}
</script>
@endsection