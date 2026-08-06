@extends('layouts.admin')

@section('title', 'Speech Management')

@section('content')
<div class="space-y-12">
    @foreach($rows as $rowIndex)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50/50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-bold text-slate-800">Row {{ $rowIndex }}</h2>
                <form action="{{ route('admin.speeches.row-config') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="row_index" value="{{ $rowIndex }}">
                    <select name="config" onchange="this.form.submit()" class="text-xs font-bold uppercase tracking-wider bg-white border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-1">
                        @php $currentConfig = \App\Models\Option::get("speech.row.{$rowIndex}.config", '1 item'); @endphp
                        <option value="1 item" {{ $currentConfig == '1 item' ? 'selected' : '' }}>1 Item</option>
                        <option value="2 items" {{ $currentConfig == '2 items' ? 'selected' : '' }}>2 Items</option>
                        <option value="3 items" {{ $currentConfig == '3 items' ? 'selected' : '' }}>3 Items</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $maxCols = 3;
                    if($currentConfig == '1 item') $maxCols = 1;
                    elseif($currentConfig == '2 items') $maxCols = 2;
                @endphp

                @for($colIndex = 1; $colIndex <= $maxCols; $colIndex++)
                    @php
                        $item = $speeches->get($rowIndex)?->where('column_index', $colIndex)->first();
                    @endphp

                    @if($item)
                    <div onclick="window.location='{{ route('admin.speeches.edit', $item) }}'" 
                        class="relative group cursor-pointer bg-slate-50 rounded-xl border-2 border-dashed border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/30 transition-all p-4 flex flex-col items-center justify-center text-center min-h-50">
                        <img src="{{ $item->image_url }}" alt="" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-sm mb-3">
                        <h3 class="font-bold text-slate-800 line-clamp-1">{{ $item->title }}</h3>
                        @if($item->name)
                            <p class="text-xs text-slate-500 mt-1">{{ $item->name }}</p>
                        @endif
                        <div class="mt-3">
                            @if($item->is_active)
                                <span class="inline-flex text-[10px] font-bold uppercase px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full">Active</span>
                            @else
                                <span class="inline-flex text-[10px] font-bold uppercase px-2 py-0.5 bg-slate-200 text-slate-600 rounded-full">Inactive</span>
                            @endif
                        </div>
                        
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <form action="{{ route('admin.speeches.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this speech?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="event.stopPropagation()" class="p-1.5 bg-white text-red-500 rounded-lg shadow-sm hover:text-red-700">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <form action="{{ route('admin.speeches.quick') }}" method="POST" class="h-full">
                        @csrf
                        <input type="hidden" name="row_index" value="{{ $rowIndex }}">
                        <input type="hidden" name="column_index" value="{{ $colIndex }}">
                        <button type="submit" class="w-full h-full min-h-50 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/50 transition-all group">
                            <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3 group-hover:bg-indigo-100 transition-colors">
                                <i class="fas fa-plus text-lg"></i>
                            </div>
                            <span class="text-sm font-bold">Add Speech</span>
                            <span class="text-[10px] uppercase mt-1 opacity-60">Column {{ $colIndex }}</span>
                        </button>
                    </form>
                    @endif
                @endfor
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
