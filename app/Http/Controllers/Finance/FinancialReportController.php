<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\AccountGl;
use App\Models\Master\GlCard;
use App\Models\Finance\Tmp_Bbrl;

class FinancialReportController extends Controller
{
    // public function accountGetRawData()
    // {
    //     $model = accountGl::getAll();
    //     return response()->json($model);
    // }

    public function getListIncomeStatement(request $request)
    {
        $model = new AccountGl();
        $fields = $model->getTableColumns();
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $isTotal = $request->input('isTotal');
        $isParent = $request->input('isParent');
        $isChild = $request->input('isChild');
        $isZero = $request->input('isZero');
        $isTotalParent = $request->input('isTotalParent');
        $isRecord = "Y";
        $accountPercent = "";
        $isPercent = $request->input('isPercent');
        $isValas = $request->input('isValas');
        $isShowCoa = $request->input('isShowCoa');

        DB::select("CALL TF_RL('$sdate', '$edate', '$isTotal', '$isParent', '$isChild', '$isZero', '$isTotalParent','$isRecord','$accountPercent','$isPercent', '$isValas', '$isShowCoa')");
        $model = new Tmp_Bbrl();
        $bbrl = Tmp_Bbrl::getPopulateBbrl();
        $bbrl2 = Tmp_Bbrl::getPopulateBbrl();
        if ($isShowCoa == "Y") {
            $bbrl->addSelect(DB::RAW("no_rek AS no_rek2"));
        } else {
            $bbrl->addSelect(DB::RAW("'' AS no_rek2"));
        }


        if ($isPercent == "Y") {
            $nilaiJual = $bbrl2->where('nm_rek', 'Total OPERATING REVENUE')->get()->toArray();
            $bbrl->addSelect(DB::RAW("IF(IFNULL(" . $nilaiJual[0]['nilai'] . " ,0) <> 0, ROUND( nilai / " . $nilaiJual[0]['nilai'] . "  * 100, 2 ), 0 ) AS persen"));
        } else {
            $bbrl->addSelect(DB::RAW("'' AS persen"));
        }


        $filteredData = $bbrl->get();
        $totalRows = $bbrl->count();
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

        //     // foreach ($request->input('sort') as $key => $sort) {
        //     //     $column = $sort['column'];
        //     //     $direction = $sort['dir'];
        //     //     $bbrl->orderBy($column, $direction);
        //     // }
        // } else {
        //     $bbrl->orderBy('urut', 'asc');
        // }
        $bbrl->orderBy('urut', 'asc');
        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $bbrl->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $bbrl->skip($offset)->take($limit);
            }
        }
        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'bbrl' => $bbrl->get()
        ];

        return response()->json($data);
    }
}
