<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';

    protected $fillable = ['user_id','titik_sampah_id','latitude','longitude','foto','deskripsi','jenis_sampah','perkiraan_kondisi','status','tanggal_laporan'];

    public function pelapor() { return $this->belongsTo(User::class, 'user_id'); }
    public function titikSampah() { return $this->belongsTo(TitikSampah::class); }
    public function penanganan() { return $this->hasOne(Penanganan::class); }
}