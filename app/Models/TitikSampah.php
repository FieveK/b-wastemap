<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TitikSampah extends Model
{
    protected $table = 'titik_sampah';

    protected $fillable = ['latitude', 'longitude', 'alamat', 'kecamatan', 'kelurahan', 'status', 'total_laporan', 'total_penanganan'];

    public function laporan()
    {
        return $this->hasMany(Laporan::class);
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatTitik::class)->latest();
    }

    public function getStatusPetaAttribute(): string
    {
        if (($this->penanganan_aktif ?? 0) > 0) {
            return 'dalam_penanganan';
        }

        if ($this->status === 'berulang') {
            return 'berulang';
        }

        if (($this->laporan_belum_ditangani ?? 0) > 0) {
            return 'perlu_perhatian';
        }

        return $this->total_penanganan > 0 ? 'selesai' : $this->status;
    }
}
