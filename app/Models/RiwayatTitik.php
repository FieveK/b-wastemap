<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatTitik extends Model
{
    protected $table = 'riwayat_titik';

    protected $fillable = ['titik_sampah_id', 'laporan_id', 'status_kejadian', 'keterangan'];

    public function titikSampah()
    {
        return $this->belongsTo(TitikSampah::class);
    }

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }
}
