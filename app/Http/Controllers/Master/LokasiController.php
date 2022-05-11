<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Lokasi;

class LokasiController extends Controller
{
    public function lokasiGetRawData()
    {
        $model = Lokasi::getAll();
        return response()->json($model);
    }
}
