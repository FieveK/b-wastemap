<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanMasukNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $nomorLaporan,
        private readonly string $kecamatan,
        private readonly bool $pembaruan
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, string> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->pembaruan ? 'Bukti laporan diperbarui' : 'Laporan baru masuk',
            'body' => $this->nomorLaporan.' · '.$this->kecamatan,
            'url' => route('admin.laporan.index'),
        ];
    }
}
