<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SIM Apotik' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center">
    <div class="w-full max-w-sm">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
