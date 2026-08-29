<?php

namespace App\Livewire\Resep;

use App\Exceptions\ApprovalRequiredException;
use App\Models\Resep;
use App\Services\ResepService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public bool $showVerifikasiModal = false;
    public ?Resep $reviewing = null;
    public string $catatanVerifikasi = '';
    public string $alasanTolak = '';
    public string $aksi = 'verify'; // 'verify' | 'reject'

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openVerifikasi(int $resepId, string $aksi = 'verify'): void
    {
        $this->authorize('verify', Resep::class);

        $this->reviewing = Resep::with(['detail.obat', 'pasien'])->findOrFail($resepId);
        $this->aksi = $aksi;
        $this->catatanVerifikasi = '';
        $this->alasanTolak = '';
        $this->showVerifikasiModal = true;
    }

    public function confirmVerifikasi(ResepService $service): void
    {
        $this->authorize('verify', Resep::class);

        if (!$this->reviewing) {
            return;
        }

        try {
            if ($this->aksi === 'reject') {
                $this->validate(['alasanTolak' => ['required', 'string', 'max:500']]);
                $service->reject($this->reviewing, auth()->id(), $this->alasanTolak);
                session()->flash('success', "Resep {$this->reviewing->no_resep} ditolak.");
            } else {
                $service->verify($this->reviewing, auth()->id(), $this->catatanVerifikasi ?: null);
                session()->flash('success', "Resep {$this->reviewing->no_resep} berhasil diverifikasi.");
            }
        } catch (ApprovalRequiredException|\InvalidArgumentException $e) {
            $this->addError('verifikasi', $e->getMessage());
            return;
        }

        $this->showVerifikasiModal = false;
        $this->reviewing = null;
    }

    public function render()
    {
        return view('livewire.resep.index', [
            'resepList' => Resep::query()
                ->with(['pasien', 'apotekerVerifikasi'])
                ->when($this->search, fn ($q) => $q->where('no_resep', 'like', "%{$this->search}%")
                    ->orWhereHas('pasien', fn ($q2) => $q2->where('nama_pasien', 'like', "%{$this->search}%")))
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->orderByDesc('created_at')
                ->paginate(20),
            'statusOptions' => [
                Resep::STATUS_MENUNGGU_VERIFIKASI,
                Resep::STATUS_TERVERIFIKASI,
                Resep::STATUS_DITOLAK,
                Resep::STATUS_SELESAI,
            ],
        ]);
    }
}
