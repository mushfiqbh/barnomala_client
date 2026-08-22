@extends('layouts.admin')

@section('title', 'Edit News')

@push('header_actions')
<div class="flex items-center gap-5">
    <a href="{{ route('admin.news.index') }}" 
       class="inline-flex items-center px-4 py-2.5 border border-slate-200 dark:border-slate-600 text-sm font-bold rounded-xl text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 transition-all duration-200 shadow-sm">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
    <button type="submit" form="news-form" 
            class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-lg text-white bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">
        <i class="fas fa-save mr-2"></i>
        Update News
    </button>
</div>
@endpush

@section('content')
<div class="space-y-6 animate-fadeInUp">
    <form id="news-form" action="{{ route('admin.news.update', $news) }}" method="POST" enctype="multipart/form-data"
          x-data="editNewsForm()">
        @csrf
        @method('PATCH')
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-pen-fancy text-indigo-500"></i>
                            Basic Information
                        </h3>
                    </div>
                    <div class="p-6 md:p-8 space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5" for="title">Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $news->title) }}" 
                                   class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400 text-slate-800 placeholder-slate-400 shadow-sm transition-all duration-200 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900 hover:border-slate-400 font-bold" 
                                   placeholder="Enter News Title" required>
                            @error('title') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5" for="content">Full Content</label>
                            <textarea name="content" id="content" rows="10" 
                                      class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400 text-slate-800 placeholder-slate-400 shadow-sm transition-all duration-200 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900 hover:border-slate-400" 
                                      placeholder="Write the full news article here..." required>{{ old('content', $news->content) }}</textarea>
                            @error('content') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Options -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-clock text-indigo-500"></i>
                            Publishing
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5" for="published_at">Publish Date</label>
                            <input type="date" name="published_at" id="published_at" value="{{ old('published_at', $news->published_at ? $news->published_at->format('Y-m-d') : '') }}" 
                                   class="block w-full px-3 py-2.5 text-sm font-bold rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm transition-all duration-200 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900 hover:border-slate-400">
                            @error('published_at') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Visible</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $news->is_active) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-10 h-5.5 bg-slate-200 dark:bg-slate-600 rounded-full peer peer-checked:bg-linear-to-r peer-checked:from-indigo-500 peer-checked:to-purple-500 peer-focus:ring-2 peer-focus:ring-indigo-200 dark:peer-focus:ring-indigo-800 transition-all duration-300 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4.5"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Featured</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $news->is_featured) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-10 h-5.5 bg-slate-200 dark:bg-slate-600 rounded-full peer peer-checked:bg-linear-to-r peer-checked:from-amber-400 peer-checked:to-orange-500 peer-focus:ring-2 peer-focus:ring-amber-200 dark:peer-focus:ring-amber-800 transition-all duration-300 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4.5"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Attachments + Cover Image -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- Cover Image -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-image text-indigo-500"></i>
                        Cover Image
                    </h3>
                </div>
                <div class="p-6 md:p-8">
                    <!-- Preview -->
                    <div class="mb-4 aspect-video rounded-xl overflow-hidden border-2 border-dashed border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center transition-all duration-200"
                         :class="coverPreview ? 'border-indigo-300 dark:border-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/10' : ''">
                        <template x-if="coverPreview">
                            <img :src="coverPreview" alt="Cover preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!coverPreview && existingCover">
                            <img :src="existingCover" alt="Current cover" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!coverPreview && !existingCover">
                            <div class="text-center p-6">
                                <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-camera text-slate-400 dark:text-slate-500 text-xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">No image selected</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Recommended: 1280x720px, Max 2MB</p>
                            </div>
                        </template>
                    </div>

                    <!-- Label showing current state -->
                    <div class="flex items-center gap-2 mb-3" x-show="existingCover && !coverPreview">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                            <i class="fas fa-check-circle"></i>
                            Current image
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mb-3" x-show="coverPreview">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-xs font-semibold">
                            <i class="fas fa-sync-alt"></i>
                            New image selected
                        </span>
                        <button @click="resetCover()" type="button"
                                class="text-xs font-semibold text-slate-400 hover:text-red-500 transition-colors">
                            <i class="fas fa-undo mr-1"></i> Revert
                        </button>
                    </div>

                    <!-- File input -->
                    <div @click="$refs.coverInput.click()"
                         class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-500 transition-all duration-200 bg-slate-50/50 dark:bg-slate-700/30 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/30 transition-colors">
                                <i class="fas fa-upload text-slate-400 dark:text-slate-500 group-hover:text-indigo-500 transition-colors"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    <span x-text="existingCover && !coverPreview ? 'Change cover image' : 'Choose cover image'"></span>
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">JPG, PNG or WebP</p>
                            </div>
                        </div>
                        <input type="file" name="image" id="image" accept="image/*"
                               x-ref="coverInput"
                               @change="handleCoverImage($event)"
                               class="hidden">
                    </div>
                    @error('image') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <!-- Attachments -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-paperclip text-indigo-500"></i>
                        Attachments
                    </h3>
                </div>
                <div class="p-6 md:p-8">
                    <!-- Existing Attachments -->
                    @if($news->artifacts->count())
                    <div class="mb-5">
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Existing Files ({{ $news->artifacts->count() }})</p>
                        <div class="space-y-2">
                            @foreach($news->artifacts as $artifact)
                            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-100 dark:border-slate-600 group/artifact hover:border-red-200 dark:hover:border-red-800 transition-all">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-9 h-9 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center text-indigo-500 shadow-sm shrink-0">
                                        @php
                                            $ext = pathinfo($artifact->file_name, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $isPdf = strtolower($ext) === 'pdf';
                                        @endphp
                                        @if($isImage)
                                            <i class="fas fa-file-image"></i>
                                        @elseif($isPdf)
                                            <i class="fas fa-file-pdf"></i>
                                        @else
                                            <i class="fas fa-file-alt"></i>
                                        @endif
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $artifact->file_name }}</p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ round($artifact->file_size / 1024, 1) }} KB</p>
                                    </div>
                                </div>
                                <label class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg cursor-pointer text-xs font-semibold text-red-500 hover:text-white hover:bg-red-500 border border-red-200 dark:border-red-800 hover:border-red-500 transition-all">
                                    <input type="checkbox" name="delete_artifacts[]" value="{{ $artifact->id }}" class="hidden">
                                    <i class="fas fa-trash-alt"></i>
                                    Delete
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Add New Files -->
                    <div :class="{ 'pt-5 border-t border-slate-100 dark:border-slate-700': {{ $news->artifacts->count() }} > 0 }">
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Add New Files</p>
                        <div @click="$refs.attachmentsInput.click()"
                             class="relative flex flex-col items-center justify-center w-full px-6 py-6 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-500 transition-all duration-200 bg-slate-50/50 dark:bg-slate-700/30 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 group">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-2 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/30 transition-colors">
                                <i class="fas fa-cloud-upload-alt text-slate-400 dark:text-slate-500 group-hover:text-indigo-500 transition-colors"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                Click to add files
                            </p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Max 10MB per file. Multi-select allowed.</p>
                            <input type="file" name="artifacts[]" multiple accept="*/*"
                                   x-ref="attachmentsInput"
                                   @change="handleAttachments($event)"
                                   class="hidden">
                        </div>
                        @error('artifacts.*') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror

                        <!-- New files selected list -->
                        <template x-if="selectedFiles.length > 0">
                            <div class="mt-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        New Files (<span x-text="selectedFiles.length"></span>)
                                    </p>
                                    <button @click="clearAttachments()" type="button"
                                            class="text-xs font-semibold text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors">
                                        <i class="fas fa-trash-alt mr-1"></i> Clear all
                                    </button>
                                </div>
                                <template x-for="(file, index) in selectedFiles" :key="index">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-100 dark:border-slate-600 group/file hover:border-indigo-200 dark:hover:border-indigo-700 transition-all">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div class="w-9 h-9 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center text-indigo-500 shadow-sm shrink-0">
                                                <template x-if="file.type.startsWith('image/')">
                                                    <i class="fas fa-file-image"></i>
                                                </template>
                                                <template x-if="file.type.startsWith('application/pdf')">
                                                    <i class="fas fa-file-pdf"></i>
                                                </template>
                                                <template x-if="!file.type.startsWith('image/') && !file.type.startsWith('application/pdf')">
                                                    <i class="fas fa-file-alt"></i>
                                                </template>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate" x-text="file.name"></p>
                                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium" x-text="formatSize(file.size)"></p>
                                            </div>
                                        </div>
                                        <button @click="removeFile(index)" type="button"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all opacity-0 group-hover/file:opacity-100">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('editNewsForm', () => ({
            selectedFiles: [],
            coverPreview: null,
            existingCover: '{{ $news->image_url ?? '' }}',

            handleAttachments(event) {
                const files = Array.from(event.target.files);
                files.forEach(file => {
                    if (file.size <= 10 * 1024 * 1024) {
                        this.selectedFiles.push(file);
                    }
                });
                // Sync the just-selected files back onto the actual <input type="file">
                // so they're included in the form submission. Without this, the
                // selectedFiles array is only used for display and nothing uploads.
                this.syncInput();
            },

            removeFile(index) {
                this.selectedFiles.splice(index, 1);
                this.syncInput();
            },

            clearAttachments() {
                this.selectedFiles = [];
                this.syncInput();
            },

            syncInput() {
                const input = this.$refs.attachmentsInput;
                if (!input) return;
                const dt = new DataTransfer();
                this.selectedFiles.forEach(f => dt.items.add(f));
                input.files = dt.files;
            },

            handleCoverImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.coverPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            resetCover() {
                this.coverPreview = null;
                this.$refs.coverInput.value = '';
            },

            formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            }
        }));
    });
</script>
@endsection
