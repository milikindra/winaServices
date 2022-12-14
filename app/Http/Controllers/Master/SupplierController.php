<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Supplier;

class SupplierController extends Controller
{
    public function getList(Request $request)
    {
        $model = new Supplier();
        $fields = $model->getTableColumns();
        $void = $request->input('void');

        $sales = Supplier::getPopulate();

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $sales->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('id_supplier', 'LIKE', "%$keyword%")
                        ->orWhere('nm_supplier', 'LIKE', "%$keyword%")
                        ->orWhere('ALAMAT1', 'LIKE', "%$keyword%")
                        ->orWhere('TELP', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $sales->get();
        $totalRows = $sales->count();

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
                $sales->orderBy($column, $direction);
            }
        } else {
            $sales->orderBy('id_supplier', 'asc');
        }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $sales->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $sales->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'supplier' => $sales->get()
        ];

        return response()->json($data);
    }
}
