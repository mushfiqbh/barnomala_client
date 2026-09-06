<div
    class="sticky top-0 z-50 bg-accent border-b border-white/10 shadow-lg"
    x-data="{ mobileMenuOpen: false }"
>
    <nav class="max-w-[90%] mx-auto px-0 md:px-6 lg:px-8">
        <div class="relative transition-all duration-300">
            <div class="flex items-center justify-between">
                <!-- Mobile Menu Button -->
                <div class="flex md:hidden">
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen"
                        class="text-white hover:text-yellow-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white p-1 rounded-md transition-colors"
                    >
                        <span class="sr-only">Open main menu</span>
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex flex-wrap items-center gap-y-1 gap-x-0.5">
                    @foreach ($navigationItems ?? [] as $item)
                        <!-- Single Link -->
                        @if (empty($item['children']))
                            <a href="{{ $item['url'] }}"
                                    class="px-[clamp(0.5rem,1.2vw,0.75rem)] py-2.5 uppercase tracking-widest text-white font-semibold transition-all hover:bg-white/15 text-[clamp(0.7rem,0.9vw,0.875rem)]">
                                {{ $item['label'] }}
                            </a>

                        <!-- Dropdown -->
                        @else
                            <div class="relative group px-[clamp(0.5rem,1.2vw,0.75rem)] py-2.5 cursor-pointer font-bold text-white transition-all hover:bg-white/15 group text-[clamp(0.7rem,0.9vw,0.875rem)]">
                                <div class="flex items-center gap-1 uppercase tracking-widest font-semibold">
                                    <span>{{ $item['label'] }}</span>
                                    <svg class="w-3 h-3 transform group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                                <div class="absolute hidden group-hover:block top-full left-0 w-48 pt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <div class="bg-white text-gray-800 shadow-2xl rounded-lg overflow-hidden border border-gray-100 transform origin-top scale-95 group-hover:scale-100 transition-all duration-300">
                                        @foreach ($item['children'] as $child)
                                            <a href="{{ $child['url'] }}"
                                                    class="block px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 transition-colors border-b border-gray-50 last:border-0 text-sm">
                                                {{ $child['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if(!$showTopHeader)
                        <a href="{{ $applyUrl }}"
                            class="px-[clamp(0.5rem,1.2vw,0.75rem)] py-2.5 uppercase tracking-widest text-white font-semibold transition-all hover:bg-white/15 text-[clamp(0.7rem,0.9vw,0.875rem)] inline-flex items-center gap-1.5">
                            Online Apply
                        </a>
                    @endif
                </div>
                
                {{-- PORTAL LOGIN with Admin Panel reveal --}}
                <div class="relative ml-4 flex group/login">                        
                    <a href="{{ route('admin.dashboard') }}"
                        class="opacity-0 group-hover/login:opacity-100 px-3 py-2.5 text-white font-bold transition-all hover:bg-white/15 text-sm">
                        Web Panel
                    </a>
                    <a href="{{ $portalLoginUrl }}" target="_blank" class="block px-3 py-2.5 text-white font-bold transition-all hover:bg-white/15 text-sm">
                        LOGIN
                    </a>
                </div>
            </div>
        </div>

        <div x-show="mobileMenuOpen" 
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                class="md:hidden pb-4 space-y-1">
            @foreach ($navigationItems ?? [] as $item)
                @if (empty($item['children']))
                    <a href="{{ $item['url'] }}" 
                        class="block px-4 py-2 text-sm font-bold text-white hover:bg-white/10 rounded-md transition-all">
                        {{ $item['label'] }}
                    </a>
                @else
                    <div x-data="{ open: false }" class="space-y-1">
                        <button @click="open = !open" 
                                class="w-full flex items-center justify-between px-4 py-2 text-sm font-bold text-white hover:bg-white/10 rounded-md transition-all">
                            <span>{{ $item['label'] }}</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-4 space-y-1">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] }}" 
                                    class="block px-4 py-2 text-xs font-medium text-indigo-100 hover:bg-white/10 rounded-md transition-all">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
            
            @if(!$showTopHeader)
                <a href="{{ $applyUrl }}" 
                    class="uppercase block px-4 py-2 text-sm font-bold text-white hover:bg-white/10 rounded-md transition-all">
                    Online Apply
                </a>
            @endif
            <div class="pt-4 mt-4 border-t border-white/10 flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="flex-1 block px-4 py-2 text-sm font-bold text-white hover:bg-white/10 rounded-md transition-all text-center">
                    Web Panel
                </a>
                <a href="{{ $portalLoginUrl }}" target="_blank" class="flex-1 block px-4 py-2 text-sm font-bold text-white hover:bg-white/10 rounded-md transition-all text-center">
                    Login
                </a>
            </div>
        </div>
    </nav>
</div>
