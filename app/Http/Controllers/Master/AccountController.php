<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\AccountGl;

class AccountController extends Controller
{
    public function accountGetRawData()
    {
        $model = accountGl::getAll();
        return response()->json($model);
    }

    public function getListAccountHistory(request $request)
    {
        $model = new AccountGl();
        $fields = $model->getTableColumns();
        $gl_code = $request->input('gl_code');
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $so_id = $request->input('so_id');
        $id_employee = $request->input('id_employee');
        $dept_id = $request->input('dept_id');

        $accountGl = AccountGl::getPopulateAccount($gl_code, $sdate, $edate);

        if ($so_id != "null") {
            $accountGl->orWhere('tx.no_SO', $so_id);
        }
        if ($id_employee != "null") {
            $accountGl->orWhere('tx.id_kyw', $id_employee);
        }
        if ($dept_id != "null") {
            $accountGl->orWhere('tx.dept', $dept_id);
        }

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $accountGl->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('no_rek', 'LIKE', "%$keyword%")
                        ->orWhere('trx', 'LIKE', "%$keyword%")
                        ->orWhere('nm_rek', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $accountGl->get();
        $totalRows = $accountGl->count();

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

            // foreach ($request->input('sort') as $key => $sort) {
            //     $column = $sort['column'];
            //     $direction = $sort['dir'];
            //     $accountGl->orderBy($column, $direction);
            // }
        }
        // else {
        //     $accountGl->orderBy('no_rek', 'asc');
        // }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $accountGl->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $accountGl->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'accountGl' => $accountGl->get()
        ];

        return response()->json($data);
    }

    public function getListAccount(request $request)
    {
        $model = new AccountGl();
        $fields = $model->getTableColumns();
        $accountGl = AccountGl::populateRaw();
        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $accountGl->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('no_rek', 'LIKE', "%$keyword%")
                        ->orWhere('nm_rek', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $accountGl->get();
        $totalRows = $accountGl->count();

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
                $accountGl->orderBy($column, $direction);
            }
        } else {
            $accountGl->orderBy('no_rek', 'asc');
        }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $accountGl->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $accountGl->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'accountGl' => $accountGl->get()
        ];

        return response()->json($data);
    }
}
