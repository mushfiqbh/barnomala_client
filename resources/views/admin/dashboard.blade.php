@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8 animate-fadeInUp">
    <!-- Welcome Header -->
    <div class="bg-linear-to-r from-indigo-600 via-purple-600 to-indigo-700 rounded-2xl shadow-xl p-6 md:p-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full -ml-24 -mb-24"></div>
        <div class="absolute top-1/2 left-1/3 w-32 h-32 bg-white/5 rounded-full"></div>
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-1">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                    <p class="text-indigo-200/80 mt-1 text-sm">Here's what's happening at your institution today.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-center border border-white/10">
                        <div class="text-xl font-bold">{{ now('Asia/Dhaka')->format('h:i A') }}</div>
                        <div class="text-xs text-indigo-200">BD Time (UTC+6)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Notices Stat -->
        <div class="group bg-white dark:bg-slate-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-slate-100 dark:border-slate-700 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-linear-to-br from-indigo-400 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                    <i class="fas fa-bullhorn text-white text-lg"></i>
                </div>
                <div class="text-indigo-500 group-hover:translate-x-1 transition-transform duration-300">
                    <i class="fas fa-arrow-right text-lg"></i>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Active Notices</div>
            <div class="text-3xl font-bold text-slate-800 dark:text-white">{{ \App\Models\Post::notices()->where('is_active', true)->count() }}</div>
            <div class="mt-3">
                <div class="bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full bg-linear-to-r from-indigo-500 to-purple-500" style="width: 75%"></div>
                </div>
            </div>
        </div>

        <!-- Speeches Stat -->
        <div class="group bg-white dark:bg-slate-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-slate-100 dark:border-slate-700 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-linear-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                    <i class="fas fa-microphone-alt text-white text-lg"></i>
                </div>
                <div class="text-emerald-500 group-hover:translate-x-1 transition-transform duration-300">
                    <i class="fas fa-arrow-right text-lg"></i>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Speeches</div>
            <div class="text-3xl font-bold text-slate-800 dark:text-white">{{ \App\Models\Speech::count() }}</div>
            <div class="mt-3">
                <div class="bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full bg-linear-to-r from-emerald-500 to-green-500" style="width: 60%"></div>
                </div>
            </div>
        </div>
        
        <!-- News Stat -->
        <div class="group bg-white dark:bg-slate-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-slate-100 dark:border-slate-700 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-linear-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                    <i class="fas fa-newspaper text-white text-lg"></i>
                </div>
                <div class="text-blue-500 group-hover:translate-x-1 transition-transform duration-300">
                    <i class="fas fa-arrow-right text-lg"></i>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total News</div>
            <div class="text-3xl font-bold text-slate-800 dark:text-white">{{ \App\Models\Post::news()->where('is_active', true)->count() }}</div>
            <div class="mt-3">
                <div class="bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full bg-linear-to-r from-blue-500 to-cyan-500" style="width: 45%"></div>
                </div>
            </div>
        </div>

        <!-- Sliders Stat -->
        <div class="group bg-white dark:bg-slate-800 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-slate-100 dark:border-slate-700 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-linear-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                    <i class="fas fa-images text-white text-lg"></i>
                </div>
                <div class="text-amber-500 group-hover:translate-x-1 transition-transform duration-300">
                    <i class="fas fa-arrow-right text-lg"></i>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Sliders Active</div>
            <div class="text-3xl font-bold text-slate-800 dark:text-white">3</div>
            <div class="mt-3">
                <div class="bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full bg-linear-to-r from-amber-500 to-orange-500" style="width: 80%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Links -->
        <div class="lg:col-span-1 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 transition-all duration-300">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-widest mb-5 flex items-center gap-2">
                <i class="fas fa-link text-indigo-500"></i>
                Quick Links
            </h3>
            <div class="space-y-2">
                <a href="{{ route('admin.settings.index') }}" class="flex items-center p-3.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all duration-200 border border-transparent hover:border-slate-100 dark:hover:border-slate-600 group">
                    <div class="w-10 h-10 rounded-xl bg-linear-to-br from-indigo-50 to-indigo-100 dark:from-indigo-500/20 dark:to-indigo-500/10 flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform duration-200 mr-3">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Institution Settings</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Manage basic info</div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-indigo-400 group-hover:translate-x-1 transition-all duration-200"></i>
                </a>
                
                <a href="{{ route('admin.posts.create', ['type' => 'notice']) }}" class="flex items-center p-3.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all duration-200 border border-transparent hover:border-slate-100 dark:hover:border-slate-600 group">
                    <div class="w-10 h-10 rounded-xl bg-linear-to-br from-emerald-50 to-emerald-100 dark:from-emerald-500/20 dark:to-emerald-500/10 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform duration-200 mr-3">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Publish Notice</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Announce updates</div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all duration-200"></i>
                </a>

                <a href="{{ route('admin.posts.create', ['type' => 'news']) }}" class="flex items-center p-3.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all duration-200 border border-transparent hover:border-slate-100 dark:hover:border-slate-600 group">
                    <div class="w-10 h-10 rounded-xl bg-linear-to-br from-blue-50 to-blue-100 dark:from-blue-500/20 dark:to-blue-500/10 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform duration-200 mr-3">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Create News</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Share updates</div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-blue-400 group-hover:translate-x-1 transition-all duration-200"></i>
                </a>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 transition-all duration-300">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-clock text-purple-500"></i>
                    Recent Activities
                </h3>
            </div>
            <div class="flex flex-col items-center justify-center h-52 text-center text-slate-400 dark:text-slate-500 space-y-4">
                <div class="w-20 h-20 rounded-2xl bg-linear-to-br from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center shadow-inner">
                    <i class="fas fa-chart-line text-3xl text-slate-300 dark:text-slate-500"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">No recent activities available yet.</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Activities will appear here as you manage your content.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Stagger animation for stat cards
        const cards = document.querySelectorAll('.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4 > .group, .grid-cols-1.lg\\:grid-cols-3 > div');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.opacity = '1';
                card.classList.add('animate-fadeInUp');
            }, 100 + index * 100);
        });

        // Live clock update
        const timeEl = document.querySelector('.text-xl.font-bold');
        if (timeEl) {
            setInterval(() => {
                const now = new Date();
                const bdTime = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    timeZone: 'Asia/Dhaka'
                });
                timeEl.textContent = bdTime;
            }, 60000);
        }
    });
</script>
@endsection
