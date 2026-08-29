<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SIM Apotik' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50">
    <nav class="border-b border-slate-200 bg-white px-4 py-3">
        <div class="mx-auto flex max-w-6xl items-center justify-between">
            <a href="{{ url('/') }}" class="font-semibold text-slate-800">SIM Apotik</a>

            @auth
                <div class="flex items-center gap-4 text-sm text-slate-600">
                    <span>{{ auth()->user()->name }} <span class="text-slate-400">({{ auth()->user()->getRoleNames()->first() }})</span></span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">Keluar</button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
