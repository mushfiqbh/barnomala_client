{{--
    Shared attachment tile used by index / create / edit views.

    Required:
      - $tile (array) with keys:
            name      string
            size      int (bytes)
            mime      string|null
            url       string|null  (server file)  OR
            preview   string|null  (Alpine blob URL)
            isImage   bool

    Optional:
      - $deleteName  string  form input name to toggle deletion (renders a trash button top-right)
      - $deleteValue string  value attribute for the delete checkbox
--}}
@php
    $icon = 'fa-file-alt';
    $iconColor = 'text-slate-500';

    if (!empty($tile['isImage'])) {
        $iconColor = 'text-emerald-500';
    } elseif (!empty($tile['mime'])) {
        $mime = $tile['mime'];
        if ($mime === 'application/pdf') {
            $icon = 'fa-file-pdf';          $iconColor = 'text-rose-500';
        } elseif (str_contains($mime, 'word') || str_contains($mime, 'document')) {
            $icon = 'fa-file-word';         $iconColor = 'text-sky-500';
        } elseif (str_contains($mime, 'spreadsheet') || str_contains($mime, 'excel') || str_contains($mime, 'sheet')) {
            $icon = 'fa-file-excel';        $iconColor = 'text-emerald-600';
        } elseif (str_contains($mime, 'presentation') || str_contains($mime, 'powerpoint')) {
            $icon = 'fa-file-powerpoint';   $iconColor = 'text-amber-500';
        } elseif (str_contains($mime, 'zip') || str_contains($mime, 'compressed')) {
            $icon = 'fa-file-archive';      $iconColor = 'text-amber-600';
        }
    }

    $sizeBytes = (int) ($tile['size'] ?? 0);
    $sizeLabel = $sizeBytes >= 1024 * 1024
        ? number_format($sizeBytes / (1024 * 1024), 2) . ' MB'
        : number_format($sizeBytes / 1024, 1) . ' KB';

    $imageSrc = $tile['preview'] ?? $tile['url'] ?? null;
@endphp

<div class="group/attachment relative flex flex-col p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200">
    <div class="aspect-square w-full rounded-lg bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center overflow-hidden mb-2.5">
        @if(!empty($tile['isImage']) && $imageSrc)
            <img src="{{ $imageSrc }}" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover/attachment:scale-105">
        @else
            <i class="fas {{ $icon }} text-3xl {{ $iconColor }}"></i>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate" title="{{ $tile['name'] ?? '' }}">
            {{ $tile['name'] ?? '' }}
        </p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 font-medium">{{ $sizeLabel }}</p>
    </div>

    @if(!empty($deleteName))
        <label class="absolute top-1.5 right-1.5 inline-flex items-center justify-center w-7 h-7 rounded-lg cursor-pointer text-red-500 bg-white/90 dark:bg-slate-800/90 hover:bg-red-500 hover:text-white border border-red-200 dark:border-red-800 hover:border-red-500 shadow-sm transition-all"
               title="Mark for deletion">
            <input type="checkbox" name="{{ $deleteName }}" value="{{ $deleteValue ?? '1' }}" class="hidden">
            <i class="fas fa-trash-alt text-[11px]"></i>
        </label>
    @endif
</div>
