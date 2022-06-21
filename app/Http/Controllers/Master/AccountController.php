<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\AccountGl;
use App\Models\Finance\GlCard;

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
        $accountGl->where(DB::RAW("(tx.idxurut!=0 OR masbesar.TIPE IN ('A','B','C','D','E','F','G','H','I','J','M'))"), true);


        if ($so_id != null && $so_id != "null") {
            $accountGl->Where('tx.no_SO', $so_id);
        }
        if ($id_employee != 'all') {
            $accountGl->Where('tx.id_kyw', $id_employee);
        }
        if ($dept_id != "all") {
            $accountGl->Where('tx.dept', 'LIKE', "%$dept_id%");
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
        $accountGl->orderBy('tgl_bukti', 'asc');
        $accountGl->orderBy('idxurut', 'asc');
        $filteredData = $accountGl->get();
        // Log::debug($accountGl->toSql());
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

    public function getListCoaTransaction(request $request)
    {
        $model = new GlCard();
        $fields = $model->getTableColumns();
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $trx_type = $request->input('trx_type');
        $trx_id = $request->input('trx_id');
        $coaTrx = GlCard::whereBetween('tgl_bukti', [$sdate, $edate]);
        if ($trx_id != 'all') {
            $trx_id = str_replace(":", "/",  $request->input('trx_id'));
            $coaTrx->where('no_bukti', $trx_id);
        }
        if ($trx_type != 'all') {
            $coaTrx->where('trx', $trx_type);
        }


        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $coaTrx->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('no_rek', 'LIKE', "%$keyword%")
                        ->orWhere('trx', 'LIKE', "%$keyword%")
                        ->orWhere('nm_rek', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $coaTrx->get();
        $totalRows = $coaTrx->count();

        // if ($request->has('sort')) {
        //     if (!is_array($request->input('sort'))) {
        //         $message = "Invalid array for parameter sort";
        //         $data = [
        //             'result' => FALSE,
        //             'message' => $message
        //         ];
        //         Log::debug($request->path() . " | " . $message . " | " . print_r($_POST, TRUE));
        //         return response()->json($data);
        //     }

        //     foreach ($request->input('sort') as $key => $sort) {
        //         $column = $sort['column'];
        //         $direction = $sort['dir'];
        //         $coaTrx->orderBy($column, $direction);
        //     }
        // } else {
        // }
        $coaTrx->orderBy('no_bukti', 'asc');

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $coaTrx->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $coaTrx->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'coaTrx' => $coaTrx->get()
        ];

        return response()->json($data);
    }

    public function getListGlGroupTransaction(request $request)
    {
        $model = new GlCard();
        $fields = $model->getTableColumns();
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $trx_type = $request->input('trx_type');
        $trx_id = $request->input('trx_id');

        $no_bukti = GlCard::select('*');
        $no_bukti->whereBetween('tgl_bukti', [$sdate, $edate]);
        if ($trx_id != 'all') {
            $trx_id = str_replace(":", "/",  $request->input('trx_id'));
            $no_bukti->where('no_bukti', $trx_id);
        }
        if ($trx_type != 'all') {
            $no_bukti->where('trx', $trx_type);
        }

        $no_bukti->groupby('no_bukti');
        $no_bukti->orderBy('no_bukti', 'asc');
        $bukti = $no_bukti->get();
        $coaTrx = [];

        foreach ($bukti as $no_bukti) {
            $coaTrx[] = [
                'head' => $no_bukti,
                'child' => GLCard::where('no_bukti', $no_bukti->no_bukti)->orderBy('no_rek', 'asc')->get()
            ];
        }
        $data = [
            'result' => true,
            'coaTrx' => $coaTrx
        ];

        return response()->json($data);
    }

    public function getListCashBankDetail(request $request)
    {
        $model = new AccountGl();
        $fields = $model->getTableColumns();
        $gl_code = $request->input('gl_code');
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $so_id = $request->input('so_id');
        $id_employee = $request->input('id_employee');
        $dept_id = $request->input('dept_id');

        $cashBank = AccountGl::getPopulateCashBankDetail($gl_code, $sdate, $edate);

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $cashBank->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('no_rek', 'LIKE', "%$keyword%")
                        ->orWhere('trx', 'LIKE', "%$keyword%")
                        ->orWhere('nm_rek', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $cashBank->get();
        $totalRows = $cashBank->count();

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
            //     $cashBank->orderBy($column, $direction);
            // }
        }
        // else {
        //     $cashBank->orderBy('no_rek', 'asc');
        // }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $cashBank->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $cashBank->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'cashBank' => $cashBank->get()
        ];

        return response()->json($data);
    }

    public function trxTypeFromGlCard()
    {
        $model = GlCard::distinct()->get(['trx']);
        return response()->json($model);
    }
}
