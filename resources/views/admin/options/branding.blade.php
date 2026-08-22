@extends('layouts.admin')

@section('title', 'Branding Settings')

@push('header_actions')
    <button type="submit" form="branding-form"
        class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-lg text-white bg-linear-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">
        <i class="fas fa-save mr-2"></i>
        Save Branding
    </button>
@endpush

@section('content')
<div class="space-y-8 animate-fadeInUp">
    <form id="branding-form" action="{{ route('admin.branding.update') }}" method="POST"
        enctype="multipart/form-data">
        @csrf

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden"
             x-data="{ activeTab: 'branding' }">

            {{-- Tab Navigation --}}
            <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <nav class="flex gap-0 px-4 md:px-6" aria-label="Tabs">
                    <button type="button" @click="activeTab = 'branding'"
                        :class="activeTab === 'branding' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
                        class="px-4 md:px-5 py-3.5 md:py-4 text-xs md:text-sm font-bold border-b-2 transition-all duration-200 whitespace-nowrap">
                        <i class="fas fa-paint-brush mr-1.5 md:mr-2"></i>
                        Branding &amp; Visuals
                    </button>
                    <button type="button" @click="activeTab = 'layout'"
                        :class="activeTab === 'layout' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
                        class="px-4 md:px-5 py-3.5 md:py-4 text-xs md:text-sm font-bold border-b-2 transition-all duration-200 whitespace-nowrap">
                        <i class="fas fa-layer-group mr-1.5 md:mr-2"></i>
                        Layout &amp; Theme
                    </button>
                </nav>
            </div>

            {{-- Tab 1: Branding & Visuals --}}
            <div x-show="activeTab === 'branding'" class="p-6 md:p-8">
                @php
                    $decodeJson = function ($raw) {
                        if (is_array($raw)) return $raw;
                        if (is_string($raw) && $raw !== '') {
                            $d = json_decode($raw, true);
                            if (is_array($d)) return $d;
                        }
                        return [];
                    };

                    $logo = $decodeJson($options['institute.branding.logo_json'] ?? null) ?: [];

                    // Resolve the banner library with legacy fallback.
                    $bannersLibrary = $decodeJson($options['institute.branding.banners_json'] ?? null);
                    if (empty($bannersLibrary)) {
                        $legacy = $decodeJson($options['institute.branding.banner_json'] ?? null);
                        if (!empty($legacy['path'])) {
                            $bannersLibrary = [[
                                'id'    => 'legacy',
                                'url'   => $legacy['url'] ?? '',
                                'path'  => $legacy['path'],
                            ]];
                        } else {
                            $bannersLibrary = [];
                        }
                    }
                    $activeBannerId = $options['institute.branding.active_banner_id'] ?? null;
                    if (!$activeBannerId && !empty($bannersLibrary)) {
                        $activeBannerId = $bannersLibrary[0]['id'];
                    }
                @endphp

                {{-- Logo & Banners --}}
                <div x-data='bannerManager({
                    banners: @json($bannersLibrary),
                    activeId: @json((string) $activeBannerId)
                })'>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        {{-- Logo (3 cols) --}}
                        <div class="md:col-span-3 space-y-3">
                            <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Logo</label>
                            <div class="relative group aspect-4/3 bg-linear-to-br from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-600 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-2xl overflow-hidden flex items-center justify-center transition-all duration-300 hover:border-indigo-300 dark:hover:border-indigo-500 hover:shadow-lg"
                                x-data="{
                                    logoPreview: '{{ isset($logo['url']) ? $logo['url'] : '' }}',
                                    handleLogoChange(e) {
                                        const file = e.target.files[0];
                                        if (file) this.logoPreview = URL.createObjectURL(file);
                                    }
                                }">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" class="max-w-full max-h-full object-contain p-2">
                                </template>
                                <template x-if="!logoPreview">
                                    <div class="text-center p-2">
                                        <i class="fas fa-image text-slate-300 dark:text-slate-500 text-xl mb-1"></i>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase">No Logo</p>
                                    </div>
                                </template>
                                <label class="absolute inset-0 bg-linear-to-t from-indigo-900/80 to-purple-900/60 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center cursor-pointer backdrop-blur-sm">
                                    <div class="text-center transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                        <i class="fas fa-cloud-upload-alt text-xl text-white mb-1"></i>
                                        <span class="block text-white text-[10px] font-black uppercase tracking-widest">Change Logo</span>
                                    </div>
                                    <input type="file" name="logo" class="hidden" accept="image/*"
                                        @change="handleLogoChange">
                                </label>
                            </div>
                        </div>

                        {{-- Banners (9 cols) --}}
                        <div class="md:col-span-9 space-y-4">
                            <div class="flex items-center justify-between gap-3">
                                <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Site Banners</label>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                    Pick which one shows on the live site
                                </span>
                            </div>

                            <input type="hidden" name="active_banner_id" :value="activeId">

                            {{-- Existing banners grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <template x-for="(banner, index) in banners" :key="banner.id">
                                    <div class="group relative bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700 overflow-hidden transition-all duration-300 hover:shadow-lg"
                                        :class="banner.removed ? 'opacity-50' : ''"
                                        x-show="!banner.removed">
                                        <div class="relative aspect-video bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                            <img :src="banner.preview || banner.url"
                                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">

                                            {{-- Active ribbon --}}
                                            <div class="absolute top-2 left-2 flex gap-2">
                                                <span x-show="activeId === banner.id"
                                                    class="bg-emerald-500 text-white px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm flex items-center gap-1">
                                                    <i class="fas fa-check-circle"></i> Active
                                                </span>
                                                <span x-show="banner.isNew"
                                                    class="bg-indigo-600 text-white px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                                    New
                                                </span>
                                            </div>

                                            {{-- Hover actions --}}
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                <label class="cursor-pointer bg-white/90 hover:bg-white text-slate-900 w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-transform hover:scale-110"
                                                    title="Replace image">
                                                    <i class="fas fa-camera text-sm"></i>
                                                    <input type="file" :name="`existing_banners[${banner.id}][image]`"
                                                        @change="handleReplace($event, index)" class="hidden">
                                                </label>
                                                <button type="button" @click="markRemoved(index)"
                                                    class="bg-red-500/90 hover:bg-red-600 text-white w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-transform hover:scale-110"
                                                    title="Delete banner">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="p-3 space-y-2.5 bg-gray-200">
                                            <input type="hidden" :name="`existing_banners[${banner.id}][delete]`" :value="banner.removed ? '1' : '0'">
                                            <input type="hidden" :name="`existing_banners[${banner.id}][url]`" :value="banner.url">
                                            <input type="hidden" :name="`existing_banners[${banner.id}][path]`" :value="banner.path">

                                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                                <input type="radio" name="active_banner_radio" :value="banner.id"
                                                    @change="setActive(banner.id)"
                                                    :checked="activeId === banner.id"
                                                    class="w-4 h-4 text-emerald-500 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Set as active banner</span>
                                            </label>
                                        </div>
                                    </div>
                                </template>

                                {{-- Add new banner card --}}
                                <label class="group relative flex flex-col items-center justify-center min-h-[180px] bg-slate-50 dark:bg-slate-700/30 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-2xl cursor-pointer transition-all duration-300 hover:bg-indigo-50 dark:hover:bg-slate-700 hover:border-indigo-300 dark:hover:border-indigo-500 hover:shadow-md active:scale-[0.98]">
                                    <input type="file" name="banners[]" accept="image/*" multiple
                                        @change="handleNewUpload($event)" class="hidden">
                                    <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-sm border border-slate-100 dark:border-slate-600 group-hover:border-indigo-100 group-hover:scale-110 transition-transform mb-3">
                                        <i class="fas fa-plus text-slate-400 group-hover:text-indigo-600 text-xl"></i>
                                    </div>
                                    <span class="text-xs font-black text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 uppercase tracking-widest">Add Banner</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">You can upload several at once</span>
                                </label>

                                {{-- Hidden stash for new banners so the actual File objects ride along on submit --}}
                                <template x-for="banner in banners.filter(b => b.isNew && !b.removed)" :key="'stash-' + banner.id">
                                    <input type="file" :name="`new_banners[${banner.id}]`" class="hidden"
                                        x-effect="banner.file ? (() => { const dt = new DataTransfer(); dt.items.add(banner.file); $el.files = dt.files; })() : null">
                                </template>
                            </div>

                            {{-- Empty state --}}
                            <div x-show="banners.filter(b => !b.removed).length === 0"
                                class="py-10 bg-slate-50/50 dark:bg-slate-700/30 rounded-2xl border-4 border-dashed border-slate-100 dark:border-slate-600 text-center">
                                <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center shadow-sm mx-auto mb-3">
                                    <i class="fas fa-image text-slate-300 dark:text-slate-500 text-xl"></i>
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-300 font-bold">No banners yet</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Upload one or more to populate your header.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inline Alpine component for the multi-banner manager --}}
                <script>
                    function bannerManager(initial) {
                        return {
                            banners: (initial.banners || []).map(b => Object.assign({}, b, {
                                preview: null,
                                removed: false,
                                isNew: false,
                            })),
                            activeId: initial.activeId || null,
                            setActive(id) {
                                this.activeId = id;
                            },
                            markRemoved(index) {
                                if (this.banners[index].isNew) {
                                    // Brand-new (unsaved) banners can just be dropped locally.
                                    this.banners.splice(index, 1);
                                    if (!this.banners.find(b => b.id === this.activeId)) {
                                        this.activeId = this.banners[0]?.id || null;
                                    }
                                    return;
                                }
                                this.banners[index].removed = true;
                                if (this.activeId === this.banners[index].id) {
                                    const remaining = this.banners.find(b => !b.removed);
                                    this.activeId = remaining ? remaining.id : null;
                                }
                            },
                            handleReplace(event, index) {
                                const file = event.target.files[0];
                                if (!file) return;
                                const reader = new FileReader();
                                reader.onload = (e) => { this.banners[index].preview = e.target.result; };
                                reader.readAsDataURL(file);
                            },
                            handleNewUpload(event) {
                                const files = Array.from(event.target.files || []);
                                if (!files.length) return;
                                const readerFor = (file) => new Promise(resolve => {
                                    const r = new FileReader();
                                    r.onload = e => resolve(e.target.result);
                                    r.readAsDataURL(file);
                                });
                                Promise.all(files.map(readerFor)).then(previews => {
                                    files.forEach((file, i) => {
                                        const id = 'new_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8) + '_' + i;
                                        this.banners.push({
                                            id: id,
                                            url: '',
                                            path: '',
                                            preview: previews[i],
                                            removed: false,
                                            isNew: true,
                                            file: file,
                                        });
                                    });
                                    // Reset the picker so the same files can be re-selected later if removed.
                                    event.target.value = '';
                                    if (!this.activeId) {
                                        this.activeId = this.banners[this.banners.length - 1].id;
                                    }
                                });
                            },
                        };
                    }
                </script>

                {{-- Colors & About Image --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-16">
                    {{-- Header Background Color --}}
                    <div class="bg-slate-50/50 dark:bg-slate-700/30 rounded-xl p-5 border border-slate-100 dark:border-slate-600">
                        <label class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fas fa-fill-drip text-indigo-500"></i>
                            Header Background
                        </label>
                        <div class="flex items-center gap-4 p-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl">
                            <input type="color" name="settings[institute.branding.header_bg]" value="{{ $options['institute.branding.header_bg'] ?? '#ffffff' }}"
                                    class="h-10 w-16 rounded-lg cursor-pointer border-none bg-transparent">
                            <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300 uppercase">{{ $options['institute.branding.header_bg'] ?? '#ffffff' }}</span>
                            <span class="ml-auto text-[10px] text-slate-400 font-medium">Click to change</span>
                        </div>
                    </div>

                    {{-- Accent Color --}}
                    <div class="bg-slate-50/50 dark:bg-slate-700/30 rounded-xl p-5 border border-slate-100 dark:border-slate-600">
                        <label class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fas fa-paint-brush text-indigo-500"></i>
                            Accent Color
                        </label>
                        <div x-data="{
                            selectedColor: '{{ $options['institute.branding.accent_color'] ?? '#4F46E5' }}',
                            colors: ['#4F46E5', '#2563EB', '#059669', '#DC2626', '#7C3AED', '#D97706', '#0891B2', '#4B5563']
                        }">
                            <div class="flex flex-wrap gap-3 mb-4">
                                <template x-for="color in colors" :key="color">
                                    <button type="button" @click="selectedColor = color"
                                        :style="{ backgroundColor: color }"
                                        class="w-9 h-9 rounded-full border-2 transition-all duration-200 hover:scale-110 hover:shadow-lg"
                                        :class="selectedColor === color ? 'border-slate-900 dark:border-white scale-110 ring-2 ring-offset-2 ring-indigo-500' : 'border-transparent'">
                                    </button>
                                </template>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <input type="color" x-model="selectedColor"
                                        class="w-10 h-10 rounded-lg border border-slate-300 dark:border-slate-600 p-0.5 cursor-pointer">
                                </div>
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-xs text-slate-400">#</span>
                                    </div>
                                    <input type="text" name="settings[institute.branding.accent_color]"
                                        x-model="selectedColor"
                                        class="w-full pl-6 pr-3 py-2.5 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- About Image --}}
                    <div class="bg-slate-50/50 dark:bg-slate-700/30 rounded-xl p-5 border border-slate-100 dark:border-slate-600">
                        @php
                            $aboutImageRaw = $options['institute.about.image_json'] ?? null;
                            $aboutImage = is_array($aboutImageRaw) ? $aboutImageRaw : (is_string($aboutImageRaw) && $aboutImageRaw !== '' ? (json_decode($aboutImageRaw, true) ?: []) : []);
                        @endphp>
                        <label class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fas fa-image text-indigo-500"></i>
                            About Image
                        </label>
                        <div x-data="{
                            aboutPreview: '{{ $aboutImage['url'] ?? '' }}',
                            handleAboutChange(e) {
                                const file = e.target.files[0];
                                if (file) this.aboutPreview = URL.createObjectURL(file);
                            }
                        }">
                            <div class="relative group w-full bg-linear-to-br from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-600 border-2 border-dashed border-slate-200 dark:border-slate-600 rounded-2xl overflow-hidden flex items-center justify-center transition-all duration-300 hover:border-indigo-300 dark:hover:border-indigo-500 hover:shadow-lg"
                                style="min-height: 160px;">
                                <template x-if="aboutPreview">
                                    <img :src="aboutPreview" class="max-w-full max-h-full object-contain p-4">
                                </template>
                                <template x-if="!aboutPreview">
                                    <div class="text-center p-6">
                                        <i class="fas fa-image text-slate-300 dark:text-slate-500 text-3xl mb-2"></i>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase">No Image</p>
                                    </div>
                                </template>
                                <label class="absolute inset-0 bg-linear-to-t from-indigo-900/80 to-purple-900/60 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center cursor-pointer backdrop-blur-sm">
                                    <div class="text-center transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                        <i class="fas fa-cloud-upload-alt text-xl text-white mb-1"></i>
                                        <span class="block text-white text-[10px] font-black uppercase tracking-widest">Change Image</span>
                                    </div>
                                    <input type="file" name="about_image" class="hidden" accept="image/*"
                                        @change="handleAboutChange">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Layout & Theme --}}
            <div x-show="activeTab === 'layout'" x-cloak class="p-6 md:p-8">
                <div class="space-y-6">
                    {{-- Top Header Toggle --}}
                    <div class="bg-slate-50/50 dark:bg-slate-700/30 rounded-xl p-5 border border-slate-100 dark:border-slate-600">
                        @php
                            $showTopHeader = ($options['institute.branding.show_top_header'] ?? '1') === '1';
                        @endphp
                        <label for="institute.branding.show_top_header"
                            class="flex items-center justify-between gap-4 cursor-pointer">
                            <div class="flex items-start gap-4 min-w-0">
                                <span class="shrink-0 w-12 h-12 rounded-xl bg-linear-to-br from-indigo-100 to-purple-100 dark:from-indigo-500/20 dark:to-purple-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                    <i class="fas fa-window-maximize text-lg"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Top Header Bar</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Show the dark contact strip (phone, email, social links, online apply)</p>
                                </div>
                            </div>
                            <span class="relative inline-flex items-center shrink-0">
                                <input type="hidden" name="settings[institute.branding.show_top_header]" value="0">
                                <input type="checkbox" id="institute.branding.show_top_header"
                                    name="settings[institute.branding.show_top_header]" value="1"
                                    class="peer sr-only"
                                    {{ $showTopHeader ? 'checked' : '' }}>
                                <span class="w-12 h-6 bg-slate-300 dark:bg-slate-600 rounded-full peer-checked:bg-linear-to-r peer-checked:from-indigo-500 peer-checked:to-purple-500 transition-all duration-300"></span>
                                <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transform peer-checked:translate-x-6 peer-checked:shadow-lg transition-all duration-300"></span>
                            </span>
                        </label>
                    </div>

                    {{-- Theme Sections --}}
                    <div class="bg-slate-50/50 dark:bg-slate-700/30 rounded-xl p-5 border border-slate-100 dark:border-slate-600">
                        <label class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fas fa-palette text-indigo-500"></i>
                            Section Designs
                        </label>

                        @if(empty($themeSections))
                            <div class="text-center py-8 bg-slate-50/50 dark:bg-slate-700/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-600">
                                <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-info-circle text-slate-400 text-xl"></i>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic">No theme sections registered.</p>
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach($themeSections as $sectionKey => $section)
                                    @php
                                        $optionKey    = $theme->optionKey($sectionKey);
                                        $available    = $theme->available($sectionKey);
                                        $currentValue = $theme->currentValue($sectionKey);
                                        if (!array_key_exists((string) $currentValue, $available)) {
                                            $currentValue = $theme->defaultFor($sectionKey);
                                        }
                                        $isDesign     = $theme->typeOf($sectionKey) === 'design';
                                        $labelSuffix  = $isDesign ? ' Design' : '';
                                    @endphp
                                    <div x-data="{ selected: '{{ $currentValue }}' }">
                                        <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2.5">
                                            {{ $section['label'] ?? ucfirst($sectionKey) }}{{ $labelSuffix }}
                                        </label>

                                        <input type="hidden" name="settings[{{ $optionKey }}]" x-model="selected">

                                        <div class="flex flex-wrap gap-3">
                                            @foreach($available as $valueKey => $valueLabel)
                                                <div @click="selected = '{{ $valueKey }}'"
                                                        :class="selected === '{{ $valueKey }}' ? 'ring-1 ring-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 border-indigo-300 dark:border-indigo-600 shadow-sm' : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                                        class="w-fit flex items-center gap-3 px-3.5 py-2.5 rounded-lg border cursor-pointer transition-all duration-150 select-none">
                                                    <i :class="selected === '{{ $valueKey }}' ? 'fa-solid fa-square-check text-indigo-600 dark:text-indigo-400' : 'fa-regular fa-square text-slate-300 dark:text-slate-500'" class="text-lg shrink-0"></i>
                                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200 leading-tight">{{ $valueLabel }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
