<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RiwayatTitik;

class TitikSampah extends Model
{
    protected $table = 'titik_sampah';
    protected $fillable = ['latitude','longitude','alamat','kecamatan','status','total_laporan','total_penanganan'];

    public function laporan() { return $this->hasMany(Laporan::class); }
    public function riwayat() { return $this->hasMany(RiwayatTitik::class)->latest(); }
}