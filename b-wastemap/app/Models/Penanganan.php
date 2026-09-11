<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penanganan extends Model
{
    protected $table = 'penanganan';

    protected $fillable = ['laporan_id','petugas_id','status','foto_sebelum','foto_sesudah','catatan','tanggal_penanganan'];

    public function laporan() { return $this->belongsTo(Laporan::class); }
    public function petugas() { return $this->belongsTo(User::class, 'petugas_id'); }
}