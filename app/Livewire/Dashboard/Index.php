<?php

namespace App\Livewire\Dashboard;

use App\Policies\LaporanPolicy;
use App\Services\DashboardService;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        abort_unless(app(LaporanPolicy::class)->view(auth()->user()), 403);
    }

    public function render(DashboardService $service)
    {
        return view('livewire.dashboard.index', [
            'ringkasan' => $service->ringkasan(),
        ]);
    }
}
