<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ExpiryService;
use Illuminate\Console\Command;

class CekKadaluarsaObat extends Command
{
    protected $signature = 'stok:cek-kadaluarsa';

    protected $description = 'Write-off batch yang sudah lewat kadaluarsa dan laporkan batch yang mendekati H-90/H-30/H-7';

    public function handle(ExpiryService $expiryService): int
    {
        // TODO Phase-selanjutnya: ganti user id sistem ini dengan mekanisme
        // "system actor" yang proper (mis. user khusus 'system' atau
        // nullable user_id + kolom is_system_action di kartu_stok).
        // Untuk sekarang pakai user aktif pertama sebagai placeholder.
        $systemUserId = User::query()->value('id');

        if (!$systemUserId) {
            $this->error('Tidak ada user di database — batalkan write-off otomatis.');
            return self::FAILURE;
        }

        $writtenOff = $expiryService->writeOffExpiredBatches($systemUserId);
        $this->info("Batch di-write-off (expired): {$writtenOff}");

        $nearing = $expiryService->batchesNearingExpiry();

        foreach ($nearing as $days => $batches) {
            if ($batches->isEmpty()) {
                continue;
            }

            $this->warn("H-{$days}: {$batches->count()} batch mendekati kadaluarsa");
            foreach ($batches as $batch) {
                $this->line("  - {$batch->obat->nama_obat} (batch {$batch->no_batch}, stok {$batch->stok_saat_ini}, exp {$batch->tanggal_kadaluarsa->toDateString()})");
            }

            // NOTIFICATION CHANNEL: belum ada mekanisme kirim notifikasi
            // (email/in-app) di Phase 2 ini — baru mencatat ke console/log.
            // Sambungkan ke Notification/Mail di phase UI notifikasi nanti.
        }

        return self::SUCCESS;
    }
}
