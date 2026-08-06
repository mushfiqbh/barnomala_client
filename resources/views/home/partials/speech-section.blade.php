@php
    function getRowItemClass(string $config): string {
        return match ($config) {
            '1 item' => 'md:col-span-6',
            '2 items' => 'md:col-span-3',
            default => 'md:col-span-2',
        };
    }

    function getImageUrl($speech) {
        if (is_array($speech)) {
            return $speech['image_json']['url'] ?? '/images/teacher.png';
        }
        return $speech->image_json['url'] ?? '/images/teacher.png';
    }
@endphp

<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="max-w-[90%] mx-auto px-0 md:px-6 lg:px-8 mt-16 font-bn reveal">
    
    <!-- Dynamic Speech Section (row/column placement driven by row config) -->
    @php
        $rows = [1, 2, 3];
    @endphp
    <div class="space-y-4 mb-10">
        @foreach($rows as $rowIndex)
            @php
                $rowConfig = $options["speech.row.{$rowIndex}.config"] ?? '1 item';
                $maxItems = $rowConfig === '1 item' ? 1 : ($rowConfig === '2 items' ? 2 : 3);
                $rowItems = collect($speeches)
                    ->where('row_index', $rowIndex)
                    ->sortBy('column_index')
                    ->take($maxItems);
            @endphp

            @if($rowItems->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-6 gap-8">
                    @foreach($rowItems as $speech)
                        <div class="bg-white rounded-4xl hover:shadow-[0_40px_80px_rgba(79,70,229,0.2)] transition-all duration-700 group flex flex-col border border-indigo-50/50 {{ getRowItemClass($rowConfig) }}">
                            <div class="p-2 md:p-7 flex-1 flex flex-col relative overflow-hidden">
                                <!-- Background Decoration -->
                                <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-1000"></div>
                                
                                <div class="flex flex-col items-center mb-8 relative z-10">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-indigo-600 rounded-full blur-lg opacity-0 group-hover:opacity-20 transition-opacity duration-500"></div>
                                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-2xl mb-4 relative">
                                            <img src="{{ getImageUrl($speech) }}"
                                                 class="w-full h-full object-cover transition duration-700"
                                                 alt="{{ $speech['name'] ?? $speech->name }}">
                                        </div>
                                    </div>
                                    <h3 class="text-xl font-black text-indigo-950 text-center tracking-tight">{{ $speech['title'] ?? $speech->title }}</h3>
                                    <div class="w-12 h-1 bg-indigo-100 rounded-full mt-3 group-hover:w-20 group-hover:bg-indigo-600 transition-all duration-500"></div>
                                </div>

                                <div class="relative flex-1 z-10 group/speech overflow-hidden">
                                    <span class="text-7xl font-serif text-indigo-100 absolute -top-8 -left-2 select-none group-hover:text-indigo-200 transition-colors">“</span>
                                    @php
                                        $speechText = $speech['speech'] ?? $speech->speech;
                                    @endphp
                                    <div lang="bn" class="font-bn max-h-52 overflow-y-auto scrollbar-hide text-slate-600 text-justify px-4 leading-relaxed bg-white">
                                        {{ $speechText }}
                                    </div>
                                    <div class="absolute bottom-0 left-0 right-0 h-8 bg-linear-to-t from-white to-transparent pointer-events-none opacity-0 group-hover/speech:opacity-100 transition-opacity"></div>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="{{ route('speeches.index', ['id' => $speech['id'] ?? $speech->id]) }}" 
                                        class="inline-flex items-center text-sm font-black text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-widest">
                                        {{ $options['institute.about.button_text'] ?? 'Read Full Message' }} <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                                    </a>
                                </div>

                                <div class="text-right pt-4 border-t border-indigo-50 relative z-10">
                                    @if(!empty($speech['name'] ?? $speech->name))
                                        <div class="font-black text-indigo-950 text-lg">{{ $speech['name'] ?? $speech->name }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>
