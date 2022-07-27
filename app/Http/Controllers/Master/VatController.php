<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Vat;

class VatController extends Controller
{
    public function getList(Request $request)
    {
        $model = new Vat();
        $fields = $model->getTableColumns();

        $sdate = $request->input('sdate');
        $vat = Vat::getPopulate();
        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $vat->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('kode', 'LIKE', "%$keyword%")
                        ->orWhere('keterangan', 'LIKE', "%$keyword%")
                        ->orWhere('prosen', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $vat->get();
        $totalRows = $vat->count();

        if ($request->has('sort')) {
            if (!is_array($request->input('sort'))) {
                $message = "Invalid array for parameter sort";
                $data = [
                    'result' => FALSE,
                    'message' => $message
                ];
                Log::debug($request->path() . " | " . $message . " | " . print_r($_POST, TRUE));
                return response()->json($data);
            }

            foreach ($request->input('sort') as $key => $sort) {
                $column = $sort['column'];
                $direction = $sort['dir'];
                $vat->orderBy($column, $direction);
            }
        } else {
            $vat->orderBy('kode', 'asc');
        }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $vat->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($offset < 0) {
                $offset = 0;
            }

            $vat->skip($offset)->take($limit);
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'vat' => $vat->get()
        ];

        return response()->json($data);
    }

    public function vatGetData(Request $request)
    {
        $model = Vat::select('*')
            ->join(DB::RAW('((SELECT max( effective_date ) AS latest FROM maskodepajak  WHERE effective_date < "' . $request['sdate'] . '") AS r)'), 'maskodepajak.effective_date', 'r.latest')
            ->get();
        return response()->json($model);
    }
}
