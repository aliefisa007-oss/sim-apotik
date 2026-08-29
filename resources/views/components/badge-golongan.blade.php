@props(['golongan'])

@php
    $styles = [
        'bebas' => 'bg-emerald-100 text-emerald-700',
        'bebas_terbatas' => 'bg-amber-100 text-amber-700',
        'keras' => 'bg-red-100 text-red-700',
        'narkotika' => 'bg-red-200 text-red-800',
        'psikotropika' => 'bg-red-200 text-red-800',
    ];
    $labels = [
        'bebas' => 'Bebas',
        'bebas_terbatas' => 'Bebas Terbatas',
        'keras' => 'Keras',
        'narkotika' => 'Narkotika',
        'psikotropika' => 'Psikotropika',
    ];
@endphp

<span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $styles[$golongan] ?? 'bg-slate-100 text-slate-600' }}">
    {{ $labels[$golongan] ?? $golongan }}
</span>
