<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Sales;

class SalesController extends Controller
{
    public function salesGetRawData(Request $request)
    {
        $model = Sales::getAll($request->field, $request->sort);
        return response()->json($model);
    }

    public function getList(Request $request)
    {
        $model = new Sales();
        $fields = $model->getTableColumns();
        $void = $request->input('void');

        $sales = Sales::getPopulate();

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $sales->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('ID_SALES', 'LIKE', "%$keyword%")
                        ->orWhere('NM_SALES', 'LIKE', "%$keyword%");
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
            $sales->orderBy('ID_SALES', 'asc');
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
            'sales' => $sales->get()
        ];

        return response()->json($data);
    }
}
