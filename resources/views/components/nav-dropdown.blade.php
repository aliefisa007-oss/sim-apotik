@props(['label', 'active' => false])

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <button @click="open = ! open"
            class="{{ $active ? 'border-slate-800 text-slate-900' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} inline-flex items-center gap-1 border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none">
        {{ $label }}
        <svg class="h-3.5 w-3.5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 w-52 origin-top-left rounded-md shadow-lg"
         style="display: none;"
         @click="open = false">
        <div class="rounded-md bg-white py-1 ring-1 ring-black ring-opacity-5">
            {{ $slot }}
        </div>
    </div>
</div>
