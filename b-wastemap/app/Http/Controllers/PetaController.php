<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TitikSampah;

class PetaController extends Controller
{
    public function index()
    {
        return view('peta.index');
    }

    public function data()
    {
        return response()->json(
            TitikSampah::select('id','latitude','longitude','status','alamat','kecamatan','total_laporan')->get()
        );
    }
}