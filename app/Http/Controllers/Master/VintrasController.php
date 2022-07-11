<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Tree\ModuleNode;

class VintrasController extends Controller
{
    public function vintrasGetData(request $request)
    {
        $model = DB::table('vpa.inquiry2022')
            ->leftjoin('vpa.mpelanggan', 'vpa.mpelanggan.Id_pelanggan', 'vpa.inquiry2022.ID_Pelanggan')
            ->select('vpa.inquiry2022.*', 'vpa.mpelanggan.Nama_Pelanggan')->get();
        // Log::debug($model);
        return response()->json($model);
    }

    public function getList(request $request)
    {
        $periode = 'vpa.inquiry' . $request->input('period');
        $fields = Schema::getColumnListing($periode);

        $vintras = DB::table($periode);
        $vintras->leftjoin('vpa.mpelanggan', 'vpa.mpelanggan.Id_pelanggan', $periode . '.ID_Pelanggan');

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $vintras->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('vpa.inquiry2022.Kode_Ref', 'LIKE', "%$keyword%")
                        ->orWhere('vpa.inquiry2022.Spec_Barang', 'LIKE', "%$keyword%")
                        ->orWhere('vpa.inquiry2022.Tanggal', 'LIKE', "%$keyword%")
                        ->orWhere('vpa.mpelanggan.Nama_Pelanggan', 'LIKE', "%$keyword%")
                        ->orWhere('vpa.inquiry2022.Brand', 'LIKE', "%$keyword%")
                        ->orWhere('vpa.inquiry2022.Ket_Ref', 'LIKE', "%$keyword%")
                        ->orWhere('vpa.inquiry2022.Spec_Lain', 'LIKE', "%$keyword%")
                        ->orWhere('vpa.inquiry2022.Keterangan', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $vintras->get();
        $totalRows = $vintras->count();

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
                $vintras->orderBy($column, $direction);
            }
        } else {
            $vintras->orderBy('Kode_Ref', 'asc');
        }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $vintras->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $vintras->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'vintras' => $vintras->get()
        ];

        return response()->json($data);
    }
}
