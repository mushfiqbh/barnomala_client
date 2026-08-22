@extends('layouts.admin')

@section('title', 'Notice Management')

@push('header_actions')
    <a href="{{ route('admin.notices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-bold shadow-sm">
        <i class="fas fa-plus mr-2"></i> New Notice
    </a>
@endpush

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold">
                <tr>
                    <th class="px-6 py-4 uppercase tracking-wider text-xs">Title & Status</th>
                    <th class="px-6 py-4 uppercase tracking-wider text-xs">Date</th>
                    <th class="px-6 py-4 uppercase tracking-wider text-xs">Attachments</th>
                    <th class="px-6 py-4 uppercase tracking-wider text-xs text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($notices as $notice)
                <tr onclick="window.location='{{ route('admin.notices.edit', $notice) }}'" class="hover:bg-slate-100 transition-colors group cursor-pointer">
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800 line-clamp-1">{{ $notice->title }}</span>
                            <div class="flex items-center gap-2 mt-1">
                                @if($notice->is_active)
                                    <span class="inline-flex text-[10px] uppercase font-bold px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full">Live</span>
                                @else
                                    <span class="inline-flex text-[10px] uppercase font-bold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">Draft</span>
                                @endif
                                
                                @if($notice->is_urgent)
                                    <span class="inline-flex text-[10px] uppercase font-bold px-2 py-0.5 bg-red-100 text-red-700 rounded-full">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Urgent
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-500">
                        {{ $notice->published_at ? $notice->published_at->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($notice->artifacts->count())
                            <div class="grid grid-cols-3 gap-1.5 max-w-[140px]">
                                @foreach($notice->artifacts->take(6) as $artifact)
                                    @php
                                        $ext = strtolower(pathinfo($artifact->file_name, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                        $icon = 'fa-file-alt';
                                        $iconColor = 'text-slate-500';
                                        if ($ext === 'pdf') { $icon = 'fa-file-pdf'; $iconColor = 'text-rose-500'; }
                                        elseif (in_array($ext, ['doc', 'docx'], true)) { $icon = 'fa-file-word'; $iconColor = 'text-sky-500'; }
                                        elseif (in_array($ext, ['xls', 'xlsx'], true)) { $icon = 'fa-file-excel'; $iconColor = 'text-emerald-600'; }
                                        elseif (in_array($ext, ['ppt', 'pptx'], true)) { $icon = 'fa-file-powerpoint'; $iconColor = 'text-amber-500'; }
                                        elseif (in_array($ext, ['zip', 'rar'], true)) { $icon = 'fa-file-archive'; $iconColor = 'text-amber-600'; }
                                    @endphp
                                    <div class="aspect-square w-10 rounded-md bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden" title="{{ $artifact->file_name }}">
                                        @if($isImage)
                                            <img src="{{ asset('storage/' . $artifact->file_path) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas {{ $icon }} {{ $iconColor }} text-base"></i>
                                        @endif
                                    </div>
                                @endforeach
                                @if($notice->artifacts->count() > 6)
                                    <div class="aspect-square w-10 rounded-md bg-slate-100 border border-slate-200 flex items-center justify-center text-[11px] font-bold text-slate-600">
                                        +{{ $notice->artifacts->count() - 6 }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <form action="{{ route('admin.notices.destroy', $notice) }}" method="POST" class="inline" onsubmit="return confirm('Delete this notice?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="event.stopPropagation()" class="text-slate-400 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="fas fa-trash-alt fa-lg"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                <i class="fas fa-inbox text-2xl text-slate-300"></i>
                            </div>
                            <p class="font-medium text-slate-600">No notices found</p>
                            <p class="text-sm mt-1">Get started by creating a new notice.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($notices->hasPages())
<div class="mt-6 flex justify-end">
    {{ $notices->links() }}
</div>
@endif
@endsection
