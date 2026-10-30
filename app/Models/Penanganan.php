<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penanganan extends Model
{
    protected $table = 'penanganan';

    protected $fillable = ['laporan_id', 'petugas_id', 'status', 'foto_sebelum', 'foto_sesudah', 'volume_m3', 'catatan', 'tanggal_penanganan'];

    protected $casts = ['tanggal_penanganan' => 'date'];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
