<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Tree\ModuleNode;
use App\Models\Finance\Tmp_IncomeStatement;
use App\Models\Finance\Tmp_BalanceSheet;

class FinancialReportController extends Controller
{
    public function getListIncomeStatement(request $request)
    {
        $model = new Tmp_IncomeStatement();
        $fields = $model->getTableColumns();
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');

        $isTotal = ($request->input('isTotal') == "Y") ? "Y" : "";
        $isParent = ($request->input('isParent') == "Y") ? "Y" : "";
        $isChild = ($request->input('isChild') == "Y") ? "Y" : "";
        $isZero = ($request->input('isZero') == "Y") ? "Y" : "";
        $isTotalParent = ($request->input('isTotalParent') == "Y") ? "Y" : "";
        $isRecord = "Y";
        $accountPercent = "";
        $isPercent = ($request->input('isPercent') == "Y") ? "Y" : "";
        $isValas = ($request->input('isValas') == "Y") ? "Y" : "";
        $isShowCoa = ($request->input('isShowCoa') == "Y") ? "Y" : "";

        DB::select("CALL TF_RL('$sdate', '$edate', '$isTotal', '$isParent', '$isChild', '$isZero', '$isTotalParent','$isRecord','$accountPercent','$isPercent', '$isValas', '$isShowCoa')");
        $model = new Tmp_IncomeStatement();
        $bbrl = Tmp_IncomeStatement::getPopulate();
        $bbrl2 = Tmp_IncomeStatement::getPopulate();
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
<<<<<<< .mine

    public function getListBalanceSheet(request $request)
    {
        $model = new Tmp_BalanceSheet();
        $fields = $model->getTableColumns();
        $edate = $request->input('edate');
        $isTotal = ($request->input('isTotal') == "Y") ? "Y" : "";
        $isParent = ($request->input('isParent') == "Y") ? "Y" : "";
        $isChild = ($request->input('isChild') == "Y") ? "Y" : "";
        $isZero = ($request->input('isZero') == "Y") ? "Y" : "";
        $isTotalParent = ($request->input('isTotalParent') == "Y") ? "Y" : "";
        $isValas = ($request->input('isValas') == "Y") ? "Y" : "";
        $isShowCoa = ($request->input('isShowCoa') == "Y") ? "Y" : "";

        DB::select("CALL TF_NRC2('$edate', '$isTotal', '$isParent', '$isChild', '$isZero', '$isTotalParent', '$isValas', '$isShowCoa')");
        $model = new Tmp_BalanceSheet();
        $balance = Tmp_BalanceSheet::getPopulate();
        if ($isShowCoa == "Y") {
            $balance->addSelect(DB::RAW("no_rek AS no_rek2"));
        } else {
            $balance->addSelect(DB::RAW("'' AS no_rek2"));
        }

        if ($isValas == "Y") {
            $balance->addSelect(DB::RAW("IF(curr <> 'IDR' AND curr <> '',CONCAT(CONVERT(FORMAT(nilai_valas, 2) using utf8),' ',curr),'') AS valas"));
        } else {
            $balance->addSelect(DB::RAW("'' AS valas"));
        }

        $filteredData = $balance->get();
        $totalRows = $balance->count();
        $balance->orderBy('urut', 'asc');

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'balance' => $balance->get()
        ];

        return response()->json($data);
    }

    public function getTrlProject(Request $request)
    {
        Log::debug($request);
    }
=======

    public function getListBalanceSheet(request $request)
    {
        $model = new Tmp_BalanceSheet();
        $fields = $model->getTableColumns();
        $edate = $request->input('edate');
        $isTotal = ($request->input('isTotal') == "Y") ? "Y" : "";
        $isParent = ($request->input('isParent') == "Y") ? "Y" : "";
        $isChild = ($request->input('isChild') == "Y") ? "Y" : "";
        $isZero = ($request->input('isZero') == "Y") ? "Y" : "";
        $isTotalParent = ($request->input('isTotalParent') == "Y") ? "Y" : "";
        $isValas = ($request->input('isValas') == "Y") ? "Y" : "";
        $isShowCoa = ($request->input('isShowCoa') == "Y") ? "Y" : "";

        DB::select("CALL TF_NRC2('$edate', '$isTotal', '$isParent', '$isChild', '$isZero', '$isTotalParent', '$isValas', '$isShowCoa')");
        $model = new Tmp_BalanceSheet();
        $balance = Tmp_BalanceSheet::getPopulate();
        if ($isShowCoa == "Y") {
            $balance->addSelect(DB::RAW("no_rek AS no_rek2"));
        } else {
            $balance->addSelect(DB::RAW("'' AS no_rek2"));
        }

        if ($isValas == "Y") {
            $balance->addSelect(DB::RAW("IF(curr <> 'IDR' AND curr <> '',CONCAT(CONVERT(FORMAT(nilai_valas, 2) using utf8),' ',curr),'') AS valas"));
        } else {
            $balance->addSelect(DB::RAW("'' AS valas"));
        }

        $filteredData = $balance->get();
        $totalRows = $balance->count();
        $balance->orderBy('urut', 'asc');

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'balance' => $balance->get()
        ];

        return response()->json($data);
    }





>>>>>>> .theirs
}
