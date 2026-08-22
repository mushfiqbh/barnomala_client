@extends('layouts.admin')

@section('title', 'Edit Notice')

@push('header_actions')
<div class="flex items-center gap-5">
    <a href="{{ route('admin.notices.index') }}" 
       class="inline-flex items-center px-4 py-2.5 border border-slate-200 dark:border-slate-600 text-sm font-bold rounded-xl text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 transition-all duration-200 shadow-sm">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
    <button type="submit" form="notice-form" 
            class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-lg text-white bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">
        <i class="fas fa-save mr-2"></i>
        Update Notice
    </button>
</div>
@endpush

@section('content')
<div class="space-y-6 animate-fadeInUp">
    <form id="notice-form" action="{{ route('admin.notices.update', $notice) }}" method="POST" enctype="multipart/form-data"
          x-data="editNoticeForm()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column: Form + Publishing -->
            <div class="space-y-6">
                <!-- Notice Information -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-pen-fancy text-indigo-500"></i>
                            Notice Information
                        </h3>
                    </div>
                    <div class="p-6 md:p-8 space-y-6">
                        <div>
                            <label for="title" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Notice Title *</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $notice->title) }}"
                                   class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400 text-slate-800 placeholder-slate-400 shadow-sm transition-all duration-200 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900 hover:border-slate-400 font-bold"
                                   placeholder="Enter a descriptive title..." required>
                            @error('title') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="content" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Detailed Content *</label>
                            <textarea name="content" id="content" rows="10"
                                      class="block w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400 text-slate-800 placeholder-slate-400 shadow-sm transition-all duration-200 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900 hover:border-slate-400"
                                      placeholder="Write the full notice content here..." required>{{ old('content', $notice->content) }}</textarea>
                            @error('content') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Publishing -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-clock text-indigo-500"></i>
                            Publishing
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label for="published_at" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Publication Date *</label>
                            <input type="date" name="published_at" id="published_at"
                                   value="{{ old('published_at', $notice->published_at ? $notice->published_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                   class="block w-full px-3 py-2.5 text-sm font-bold rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm transition-all duration-200 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900 hover:border-slate-400">
                            @error('published_at') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-700 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Published</span>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Visible to public</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $notice->is_active ?? true) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-10 h-5.5 bg-slate-200 dark:bg-slate-600 rounded-full peer peer-checked:bg-linear-to-r peer-checked:from-indigo-500 peer-checked:to-purple-500 peer-focus:ring-2 peer-focus:ring-indigo-200 dark:peer-focus:ring-indigo-800 transition-all duration-300 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4.5"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Urgent Notice</span>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Highlights in red</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_urgent" value="1" {{ old('is_urgent', $notice->is_urgent ?? false) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-10 h-5.5 bg-slate-200 dark:bg-slate-600 rounded-full peer peer-checked:bg-linear-to-r peer-checked:from-red-400 peer-checked:to-rose-500 peer-focus:ring-2 peer-focus:ring-red-200 dark:peer-focus:ring-red-800 transition-all duration-300 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4.5"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Attachments -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-paperclip text-indigo-500"></i>
                            Attachments
                        </h3>
                    </div>
                    <div class="p-6 md:p-8">
                        <!-- Existing Attachments -->
                        @if($notice->artifacts->count())
                        <div class="mb-5">
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Existing Files ({{ $notice->artifacts->count() }})</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 gap-3">
                                @foreach($notice->artifacts as $artifact)
                                    @php
                                        $ext = strtolower(pathinfo($artifact->file_name, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                    @endphp
                                    <div x-data="{ marked: false }"
                                         x-show="!marked"
                                         x-transition.opacity.duration.200ms
                                         class="group/attachment relative flex flex-col p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-red-300 dark:hover:border-red-600 transition-all duration-200">
                                        <div class="aspect-square w-full rounded-lg bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center overflow-hidden mb-2.5">
                                            @if($isImage)
                                                <img src="{{ asset('storage/' . $artifact->file_path) }}" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover/attachment:scale-105">
                                            @elseif($ext === 'pdf')
                                                <i class="fas fa-file-pdf text-3xl text-rose-500"></i>
                                            @elseif(in_array($ext, ['doc', 'docx'], true))
                                                <i class="fas fa-file-word text-3xl text-sky-500"></i>
                                            @elseif(in_array($ext, ['xls', 'xlsx'], true))
                                                <i class="fas fa-file-excel text-3xl text-emerald-600"></i>
                                            @elseif(in_array($ext, ['ppt', 'pptx'], true))
                                                <i class="fas fa-file-powerpoint text-3xl text-amber-500"></i>
                                            @elseif(in_array($ext, ['zip', 'rar'], true))
                                                <i class="fas fa-file-archive text-3xl text-amber-600"></i>
                                            @else
                                                <i class="fas fa-file-alt text-3xl text-slate-500"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate" title="{{ $artifact->file_name }}">{{ $artifact->file_name }}</p>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 font-medium">
                                                @php
                                                    $kb = $artifact->file_size / 1024;
                                                    echo $kb >= 1024 ? number_format($kb / 1024, 2) . ' MB' : number_format($kb, 1) . ' KB';
                                                @endphp
                                            </p>
                                        </div>
                                        <input type="checkbox" name="delete_artifacts[{{ $artifact->id }}]" value="1" class="hidden" :checked="marked">
                                        <button type="button"
                                                @click="if (confirm('Delete this attachment? It will be removed when you save the notice.')) { marked = true; }"
                                                title="Delete this attachment"
                                                class="absolute top-1.5 right-1.5 inline-flex items-center justify-center w-7 h-7 rounded-lg cursor-pointer text-red-500 bg-white/90 dark:bg-slate-800/90 hover:bg-red-500 hover:text-white border border-red-200 dark:border-red-800 hover:border-red-500 shadow-sm transition-all">
                                            <i class="fas fa-trash-alt text-[11px]"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Add New Files -->
                        <div :class="{ 'pt-5 border-t border-slate-100 dark:border-slate-700': {{ $notice->artifacts->count() }} > 0 }">
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Add New Files</p>
                            <div @click="$refs.attachmentsInput.click()"
                                 class="relative flex flex-col items-center justify-center w-full px-6 py-6 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-500 transition-all duration-200 bg-slate-50/50 dark:bg-slate-700/30 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 group">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-2 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/30 transition-colors">
                                    <i class="fas fa-cloud-upload-alt text-slate-400 dark:text-slate-500 group-hover:text-indigo-500 transition-colors"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    Click to add files
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Allowed: PDF, DOC, XLS, JPG, PNG, ZIP. Max 10MB per file.</p>
                                <input type="file" name="artifacts[]" id="artifacts" multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.zip"
                                       x-ref="attachmentsInput"
                                       @change="handleAttachments($event)"
                                       class="hidden">
                            </div>
                            @error('artifacts.*') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror

                            <!-- New files selected grid -->
                            <template x-if="selectedFiles.length > 0">
                                <div class="mt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            New Files (<span x-text="selectedFiles.length"></span>)
                                        </p>
                                        <button @click="clearAttachments()" type="button"
                                                class="text-xs font-semibold text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors">
                                            <i class="fas fa-trash-alt mr-1"></i> Clear all
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                                        <template x-for="(file, index) in selectedFiles" :key="index">
                                            <div class="group/attachment relative flex flex-col p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200">
                                                <div class="aspect-square w-full rounded-lg bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center overflow-hidden mb-2.5">
                                                    <template x-if="file.type && file.type.startsWith('image/')">
                                                        <img :src="file.preview" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover/attachment:scale-105">
                                                    </template>
                                                    <template x-if="!(file.type && file.type.startsWith('image/')) && file.type === 'application/pdf'">
                                                        <i class="fas fa-file-pdf text-3xl text-rose-500"></i>
                                                    </template>
                                                    <template x-if="!(file.type && file.type.startsWith('image/')) && file.type && (file.type.includes('word') || file.type.includes('document'))">
                                                        <i class="fas fa-file-word text-3xl text-sky-500"></i>
                                                    </template>
                                                    <template x-if="!(file.type && file.type.startsWith('image/')) && file.type && (file.type.includes('spreadsheet') || file.type.includes('excel') || file.type.includes('sheet'))">
                                                        <i class="fas fa-file-excel text-3xl text-emerald-600"></i>
                                                    </template>
                                                    <template x-if="!(file.type && file.type.startsWith('image/')) && (!file.type || (file.type !== 'application/pdf' && !file.type.includes('word') && !file.type.includes('document') && !file.type.includes('spreadsheet') && !file.type.includes('excel') && !file.type.includes('sheet')))">
                                                        <i class="fas fa-file-alt text-3xl text-slate-500"></i>
                                                    </template>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate" :title="file.name" x-text="file.name"></p>
                                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 font-medium" x-text="formatSize(file.size)"></p>
                                                </div>
                                                <button @click="removeFile(index)" type="button"
                                                        class="absolute top-1.5 right-1.5 inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 bg-white/90 dark:bg-slate-800/90 hover:bg-red-500 hover:text-white border border-slate-200 dark:border-slate-700 hover:border-red-500 shadow-sm transition-all opacity-0 group-hover/attachment:opacity-100"
                                                        title="Remove file">
                                                    <i class="fas fa-times text-[11px]"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('editNoticeForm', () => ({
            selectedFiles: [],

            handleAttachments(event) {
                const MAX_SIZE = 10 * 1024 * 1024; // 10MB matches server validation
                const incoming = Array.from(event.target.files);

                incoming.forEach(file => {
                    if (file.size > MAX_SIZE) {
                        alert(`'${file.name}' exceeds the 10MB limit and was skipped.`);
                        return;
                    }
                    if (this.selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                        return;
                    }
                    if (file.type && file.type.startsWith('image/')) {
                        file.preview = URL.createObjectURL(file);
                    } else {
                        file.preview = null;
                    }
                    this.selectedFiles.push(file);
                });

                this.syncInput();
                // Intentionally NOT clearing `event.target.value` here:
                // `syncInput()` writes the canonical FileList onto the input;
                // clearing `value` afterwards would wipe out the just-synced files
                // and the form would submit with no attachments.
            },

            removeFile(index) {
                if (!confirm('Remove this file from the upload list?')) return;
                const removed = this.selectedFiles[index];
                if (removed && removed.preview) {
                    URL.revokeObjectURL(removed.preview);
                }
                this.selectedFiles.splice(index, 1);
                this.syncInput();
            },

            clearAttachments() {
                if (!confirm('Remove all newly added files?')) return;
                this.selectedFiles.forEach(f => { if (f.preview) URL.revokeObjectURL(f.preview); });
                this.selectedFiles = [];
                this.syncInput();
            },

            // Rebuild the actual <input type="file"> FileList from selectedFiles
            // so the files are submitted with the form.
            syncInput() {
                // Use the Alpine ref (scoped to this component) instead of a global
                // getElementById lookup, so the binding is robust against id reuse.
                const input = this.$refs.attachmentsInput;
                if (!input) return;
                const dt = new DataTransfer();
                this.selectedFiles.forEach(f => dt.items.add(f));
                input.files = dt.files;
            },

            formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
                return (bytes / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
            }
        }));
    });
</script>
@endsection
