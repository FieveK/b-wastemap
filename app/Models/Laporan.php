<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';

    protected $fillable = ['user_id', 'titik_sampah_id', 'latitude', 'longitude', 'alamat', 'kecamatan', 'kelurahan', 'gps_accuracy_meters', 'submission_count', 'foto', 'deskripsi', 'jenis_sampah', 'perkiraan_kondisi', 'potensi_bahaya', 'alasan_keputusan', 'status', 'tanggal_laporan'];

    public function getNomorLaporanAttribute(): string
    {
        return 'BWM-'.substr((string) $this->tanggal_laporan, 0, 4).'-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function titikSampah()
    {
        return $this->belongsTo(TitikSampah::class);
    }

    public function penanganan()
    {
        return $this->hasOne(Penanganan::class);
    }
}
