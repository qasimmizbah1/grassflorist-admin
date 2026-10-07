@php
    $globalSettings = \App\Models\GlobalSetting::current();
    $logoUrl = $globalSettings?->site_logo_url;
    $siteName = $globalSettings?->site_name ?: config('app.name', 'Grass Florist');
@endphp

@if($logoUrl)
    <div class="flex items-center gap-2">
        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-8 max-h-8 w-auto object-contain" style="max-height: 32px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        <span class="text-lg font-bold tracking-tight text-white hidden">{{ $siteName }} Admin</span>
    </div>
@else
    <h2 class="text-lg font-bold tracking-tight text-white">{{ $siteName }} Admin</h2>
@endif