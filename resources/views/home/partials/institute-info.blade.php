@php
    $instituteInfo = [
        ['label' => 'EIIN', 'value' => $options['institute.identity.eiin'] ?? 'N/A', 'colors' => 'from-blue-600 to-blue-800'],
        ['label' => 'Institution Code', 'value' => $options['institute.identity.code'] ?? 'N/A', 'colors' => 'from-emerald-600 to-emerald-800'],
        ['label' => 'Center Code', 'value' => $options['institute.identity.center_code'] ?? 'N/A', 'colors' => 'from-indigo-500 to-indigo-700'],
        ['label' => 'ESTD Year', 'value' => $options['institute.identity.established_year'] ?? 'N/A', 'colors' => 'from-rose-600 to-rose-800'],
    ];
@endphp

<div class="max-w-[90%] mx-auto px-0 md:px-6 lg:px-8 mb-6 reveal">
    <div class="grid grid-cols-2 md:grid-cols-4">
        @foreach($instituteInfo as $info)
            <div class="relative overflow-hidden group bg-linear-to-br p-2 md:p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 {{ $info['colors'] }}">
                <div class="relative z-10 gap-4">
                    <div class="text-xs font-bold uppercase tracking-wider opacity-80 mb-0.5">{{ $info['label'] }}</div>
                    <div class="font-black tabular-nums {{ strlen($info['value']) > 15 ? 'text-[10px] md:text-sm' : 'text-sm md:text-2xl' }}">{{ $info['value'] }}</div>
                </div>

                <!-- Hover indicator line -->
                <div class="absolute bottom-0 left-0 h-1 bg-white/40 w-0 group-hover:w-full transition-all duration-500"></div>
            </div>
        @endforeach
    </div>
</div>  
