@extends('layouts.admin')

@section('title', $isEditing ? 'Edit Post' : 'Create Post')

@push('header_actions')
<div class="flex items-center gap-5">
    <a href="{{ route('admin.posts.index') }}" class="inline-flex items-center px-4 py-2.5 border border-slate-200 dark:border-slate-600 text-sm font-bold rounded-xl text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 transition-all duration-200 shadow-sm">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
    <button type="submit" form="post-form" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-lg text-white bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">
        <i class="fas fa-save mr-2"></i> {{ $isEditing ? 'Update Post' : 'Save Post' }}
    </button>
</div>
@endpush

@section('content')
<div class="space-y-6 animate-fadeInUp">
    <form id="post-form" action="{{ $isEditing ? route('admin.posts.update', $post) : route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" x-data="postForm('{{ old('type', $post->type) }}', {{ $isEditing ? 'true' : 'false' }}, '{{ $post->image_json['url'] ?? '' }}')">
        @csrf
        @if($isEditing) @method('PUT') @endif

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="w-full lg:w-1/2 bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="p-4 space-y-6">
                    <div>
                        <label for="type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Post Type *</label>
                        <select name="type" id="type" x-model="type" class="block w-full px-4 py-2.5 text-sm font-bold rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" required>
                            @foreach($types as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                        </select>
                        @error('type') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-slate-800 shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 font-bold" placeholder="Enter a descriptive title..." required>
                        @error('title') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="isNotice || isNews" x-cloak>
                        <label for="content" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Detailed Content *</label>
                        <textarea name="content" id="content" rows="10" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-slate-800 shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100" placeholder="Write the full content here...">{{ old('content', $post->content) }}</textarea>
                        @error('content') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="isDownload" x-cloak class="space-y-6">
                        <div><label for="description" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Description</label><textarea name="description" id="description" rows="5" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-slate-800 shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">{{ old('description', $post->description) }}</textarea></div>
                        <div class="grid gap-5 md:grid-cols-2"><div><label for="class_label" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Class / Group</label><input name="class_label" id="class_label" value="{{ old('class_label', $post->class_label) }}" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm"></div><div><label for="sort_order" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Sort Order</label><input type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $post->sort_order ?? 0) }}" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm"></div></div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/2 space-y-6">
                <div x-show="isNews" x-cloak class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50"><h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2"><i class="fas fa-image text-indigo-500"></i> Cover Image</h3></div>
                    <div class="p-6"><div class="mb-4 aspect-video rounded-xl overflow-hidden border-2 border-dashed border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center"><template x-if="coverPreview"><img :src="coverPreview" alt="Cover preview" class="w-full h-full object-cover"></template><template x-if="!coverPreview && existingCover"><img :src="existingCover" alt="Current cover" class="w-full h-full object-cover"></template><template x-if="!coverPreview && !existingCover"><i class="fas fa-camera text-3xl text-slate-400"></i></template></div><div @click="$refs.coverInput.click()" class="flex items-center justify-center px-4 py-4 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-indigo-400 transition-all bg-slate-50/50 group"><i class="fas fa-upload mr-3 text-slate-400 group-hover:text-indigo-500"></i><span class="text-sm font-semibold text-slate-600 dark:text-slate-400" x-text="existingCover ? 'Change cover image' : 'Choose cover image'"></span><input type="file" name="image" accept="image/*" x-ref="coverInput" @change="handleCoverImage($event)" class="hidden"></div>@error('image') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror</div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50"><h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2"><i class="fas fa-paperclip text-indigo-500"></i> Attachments</h3></div>
                    <div class="p-6">
                        @if($isEditing && $post->artifacts->count())<div class="mb-5"><p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Existing Files ({{ $post->artifacts->count() }})</p><div class="space-y-2">@foreach($post->artifacts as $artifact)<div x-data="{ marked: false }" x-show="!marked" class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-100 dark:border-slate-600"><div class="flex items-center gap-3 min-w-0"><i class="fas fa-file-alt text-indigo-500"></i><span class="text-sm font-semibold truncate">{{ $artifact->file_name }}</span></div><input type="checkbox" name="delete_artifacts[{{ $artifact->id }}]" value="1" x-model="marked" class="h-4 w-4 rounded border-red-300 text-red-500"></div>@endforeach</div></div>@endif
                        <div @click="$refs.attachmentsInput.click()" class="flex flex-col items-center justify-center px-6 py-8 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-indigo-400 transition-all bg-slate-50/50 group"><i class="fas fa-cloud-upload-alt text-2xl text-slate-400 group-hover:text-indigo-500 mb-2"></i><p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Click to add files</p><p class="text-xs text-slate-400 mt-1">Maximum 20 MB per file.</p><input type="file" name="artifacts[]" multiple x-ref="attachmentsInput" @change="handleAttachments($event)" class="hidden"></div>@error('artifacts.*') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        <template x-if="selectedFiles.length"><div class="mt-4 space-y-2"><p class="text-xs font-bold text-slate-500 uppercase tracking-wider">New Files (<span x-text="selectedFiles.length"></span>)</p><template x-for="(file, index) in selectedFiles" :key="index"><div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl"><div class="flex items-center gap-3 min-w-0"><i class="fas fa-file-alt text-indigo-500"></i><span class="text-sm font-semibold truncate" x-text="file.name"></span></div><button @click="removeFile(index)" type="button" class="text-slate-400 hover:text-red-500"><i class="fas fa-times"></i></button></div></template></div></template>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden mt-6">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center gap-6 flex-wrap">
                    <div class="md:flex-1 md:max-w-[200px]"><label for="published_at" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Publication Date *</label><input type="date" name="published_at" id="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" class="block w-full px-3 py-2.5 text-sm font-bold rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm" required>@error('published_at') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror</div>
                    <div class="flex items-center gap-3"><span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Published</span><label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $post->is_active) ? 'checked' : '' }} class="sr-only peer"><div class="w-10 h-5.5 bg-slate-200 dark:bg-slate-600 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:after:translate-x-4.5"></div></label></div>
                    <div x-show="isNotice" x-cloak class="flex items-center gap-3"><span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Urgent Notice</span><input type="checkbox" name="is_urgent" value="1" {{ old('is_urgent', $post->is_urgent) ? 'checked' : '' }} class="h-5 w-5 rounded border-slate-300 text-red-500 focus:ring-red-500"></div>
                    <div x-show="isNews" x-cloak class="flex items-center gap-3"><span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Featured</span><input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $post->is_featured) ? 'checked' : '' }} class="h-5 w-5 rounded border-slate-300 text-amber-500 focus:ring-amber-500"></div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('postForm', (initialType, isEditing, existingCover) => ({
        type: initialType,
        existingCover,
        coverPreview: null,
        selectedFiles: [],
        get isNotice() { return this.type === 'notice'; },
        get isNews() { return this.type === 'news'; },
        get isDownload() { return !this.isNotice && !this.isNews; },
        handleAttachments(event) {
            Array.from(event.target.files).forEach(file => {
                if (file.size <= 20 * 1024 * 1024 && !this.selectedFiles.some(item => item.name === file.name && item.size === file.size)) this.selectedFiles.push(file);
            });
            this.syncInput();
        },
        removeFile(index) { this.selectedFiles.splice(index, 1); this.syncInput(); },
        syncInput() {
            const input = this.$refs.attachmentsInput;
            if (!input) return;
            const dataTransfer = new DataTransfer();
            this.selectedFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        },
        handleCoverImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = event => { this.coverPreview = event.target.result; };
            reader.readAsDataURL(file);
        }
    }));
});
</script>
@endsection
