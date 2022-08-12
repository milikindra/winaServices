<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Efaktur;

class EfakturController extends Controller
{
    public function getByDate(Request $request)
    {
        $model = Efaktur::select('masnopajak_det.*');
        $model->join(DB::RAW('((SELECT max( tanggal ) AS latest FROM masnopajak  WHERE tanggal < "' . $request['dates'] . '") AS r)'), 'masnopajak.tanggal', 'r.latest');
        $model->leftjoin('masnopajak_det', 'masnopajak.kode', 'masnopajak_det.kode');
        $model->whereRaw("masnopajak_det.no_faktur = '' OR masnopajak_det.no_faktur is null");
        $model->orderby('masnopajak_det.IDXURUT', 'ASC');
        $data = $model->get();
        return response()->json($data);
    }
}
