<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @hasSection('title')
            @yield('title') - {{ $instituteName }}
        @else
            {{ $instituteName }}
        @endif
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --accent-color: {{ $options['institute.branding.accent_color'] ?? '#4F46E5' }};
        }
        .bg-accent { background-color: var(--accent-color); }
        .text-accent { color: var(--accent-color); }
        .border-accent { border-color: var(--accent-color); }

        /* Smooth sidebar scrollbar */
        .sidebar-scroll::-webkit-scrollbar { width: 10px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(228, 230, 232, 0.645); border-radius: 2px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.5); }

        /* Custom scrollbar for main content */
        .main-scroll::-webkit-scrollbar { width: 10px; }
        .main-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .main-scroll::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 3px; }
        .main-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }

        /* Fade-in animation */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translate3d(0, 20px, 0); }
            to { opacity: 1; transform: translate3d(0, 0, 0); }
        }
        .animate-fadeInUp { animation: fadeInUp 0.5s ease-out forwards; }

        /* Glass effect */
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 dark:bg-slate-900 font-sans antialiased text-slate-800 dark:text-slate-200" 
      x-data="adminLayout()"
      x-on:notify.window="addToast($event.detail)">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 transform bg-linear-to-b from-slate-900 to-slate-800 dark:from-slate-950 dark:to-slate-900 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shadow-2xl"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex h-full flex-col">
                <!-- Sidebar Header -->
                <div class="h-20 flex items-center justify-between px-6 py-4 bg-linear-to-r from-indigo-600 to-purple-600">
                    <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-white flex items-center">
                        <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-shield-halved text-white"></i>
                        </div>
                        <div>
                            <span>Admin Panel</span>
                            <p class="text-xs text-indigo-200 font-normal">Dashboard</p>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="text-white/70 hover:text-white lg:hidden focus:outline-none transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Sidebar Navigation -->
                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5 sidebar-scroll">                    
                    <div class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">Content Management</div>
                    
                          <a href="{{ route('admin.posts.index') }}" 
                              class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.posts.*') && request('tab') === 'notice' ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.posts.*') && request('tab') === 'notice' ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-bullhorn text-sm"></i>
                        </div>
                        <span>News / Notices</span>
                    </a>

                    <a href="{{ route('admin.gallery.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.gallery.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.gallery.*') ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-image text-sm"></i>
                        </div>
                        <span>Gallery</span>
                    </a>

                    <a href="{{ route('admin.speeches.index') }}"
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.speeches.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.speeches.*') ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-microphone-alt text-sm"></i>
                        </div>
                        <span>Speeches</span>
                    </a>

                    <a href="{{ route('admin.sliders.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.sliders.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.sliders.*') ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-images text-sm"></i>
                        </div>
                        <span>Home Slider</span>
                    </a>

                    <div class="pt-5 pb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">System Settings</div>

                    <a href="{{ route('admin.branding.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.branding.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.branding.*') ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-palette text-sm"></i>
                        </div>
                        <span>Branding</span>
                    </a>

                    <a href="{{ route('admin.settings.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-university text-sm"></i>
                        </div>
                        <span>Institution Settings</span>
                    </a>
                    
                    <a href="{{ route('admin.demographics.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.demographics.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.demographics.*') ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-users text-sm"></i>
                        </div>
                        <span>Demographics</span>
                    </a>

                    <a href="{{ route('admin.layout.index') }}" 
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.layout.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.layout.*') ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-th-large text-sm"></i>
                        </div>
                        <span>Homepage Layout</span>
                    </a>

                    <div class="pt-5 pb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">Other</div>
                    
                    <a href="{{ route('home') }}" target="_blank" class="flex items-center px-3 py-2.5 text-sm font-medium text-slate-400 hover:bg-white/5 hover:text-white rounded-lg transition-all duration-200">
                        <div class="w-7 h-7 bg-slate-800/50 flex items-center justify-center rounded-lg mr-3 shrink-0">
                            <i class="fas fa-globe text-sm"></i>
                        </div>
                        <span>Back to Website</span>
                    </a>

                    <a href="{{ route('admin.transfer.index') }}"
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.transfer.*') ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg {{ request()->routeIs('admin.transfer.*') ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-slate-800/50' }} mr-3 shrink-0 transition-colors">
                            <i class="fas fa-right-left text-sm"></i>
                        </div>
                        <span>Data Transfer</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-3 py-2.5 text-sm font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-all duration-200">
                            <div class="w-7 h-7 bg-red-500/10 flex items-center justify-center rounded-lg mr-3 shrink-0">
                                <i class="fas fa-sign-out-alt text-sm"></i>
                            </div>
                            Log out
                        </button>
                    </form>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="h-20 glass-header flex items-center px-6">
                <button @click="sidebarOpen = true" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none lg:hidden mr-4 transition-colors">
                    <i class="fas fa-bars fa-lg"></i>
                </button>

                <div class="flex-1 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-linear-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center shadow-md lg:hidden">
                            <i class="fas fa-shield-halved text-white text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">@yield('title')</h2>
                    </div>

                    <div class="flex items-center">
                        @stack('header_actions')
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto main-scroll bg-slate-50 dark:bg-slate-900">
                @include('layouts.partials.flash')
                <div class="p-6 md:p-8">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div class="fixed bottom-4 right-4 z-9999 flex flex-col gap-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform translate-y-2"
                 class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg border text-sm font-medium min-w-62.5"
                 :class="toast.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : (toast.type === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-white border-slate-200 text-slate-800')">
                 
                <i class="fas" :class="toast.type === 'success' ? 'fa-check-circle text-emerald-500' : (toast.type === 'error' ? 'fa-exclamation-circle text-red-500' : 'fa-info-circle text-slate-500')"></i>
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminLayout', () => ({
                sidebarOpen: false,
                toasts: [],
                addToast(detail) {
                    const id = Date.now();
                    this.toasts.push({ id, ...detail });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 3000);
                }
            }));
            
            // Global Auto-save Helpers
            window.autoSaveOption = async (url, key, value) => {
                try {
                    let data = {};
                    data[key] = value;
                    const response = await window.axios.post(url, data);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Saved successfully' }}));
                } catch (error) {
                    console.error("Save error", error);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Failed to save' }}));
                }
            };
            
            window.autoSaveFileOption = async (url, key, file) => {
                if(!file) return;
                try {
                    const formData = new FormData();
                    formData.append(key, file);
                    const response = await window.axios.post(url, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Uploaded successfully' }}));
                } catch(error) {
                    console.error("Upload error", error);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Failed to upload' }}));
                }
            };
        });
    </script>
    @stack('scripts')
</body>
</html>
