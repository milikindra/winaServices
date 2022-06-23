<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Tree\ModuleNode;
use App\Models\Finance\Tmp_IncomeStatement;
use App\Models\Finance\Tmp_BalanceSheet;
use App\Models\Master\PnlProjectDef;
use App\Models\Finance\PnlProject;
use App\Models\Finance\Tmp_PnlProject;
use App\Models\Finance\Tmp_PnlProjectList;
use App\Models\Transaction\SalesOrder;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

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
        $bbrl->orderBy('urut', 'asc');
        $data = [
            'result' => true,
            'bbrl' => $bbrl->get()
        ];

        return response()->json($data);
    }

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
        $balance->orderBy('urut', 'asc');
        $data = [
            'result' => true,
            'balance' => $balance->get()
        ];

        return response()->json($data);
    }

    public function getListPnlProject(request $request)
    {
        $model = new Tmp_PnlProject();
        $fields = $model->getTableColumns();
        $sdate = '2000-01-01';
        $edate = $request->input('edate');
        $so_id = $request->input('so_id');
        $isAssumptionCost = ($request->input('isAssumptionCost') == "Y") ? "Y" : "";
        $isOverhead = ($request->input('isOverhead') == "Y") ? "Y" : "";
        $isShowRecord = "Y";

        DB::select("CALL TF_RL_SO('$so_id','$sdate','$edate', '$isOverhead', '$isAssumptionCost','$isShowRecord')");
        $model = new Tmp_PnlProject();
        $balance = Tmp_PnlProject::getPopulate();
        $balance->orderBy('urut', 'asc');
        $data = [
            'result' => true,
            'balance' => $balance->get()
        ];

        return response()->json($data);
    }

    public function getListPnlProjectList(request $request)
    {
        $model = new Tmp_PnlProject();
        $fields = $model->getTableColumns();
        $so_id = '';
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $isAssumptionCost = ($request->input('isAssumptionCost') == "Y") ? "Y" : "";
        $isOverhead = ($request->input('isOverhead') == "Y") ? "Y" : "";
        $showProjectBy = $request->input('showProjectBy');
        if ($showProjectBy == 'clear') {
            $showProjectBy == 'C';
        } else if ($showProjectBy == 'cr') {
            $showProjectBy == 'R';
        } else {
            $showProjectBy = '';
        }
        $showProject = $request->input('showProject');
        $isShowRecord = 'N';

        DB::select("CALL TF_RL_SO_LIST('$so_id','$sdate','$edate', '$isOverhead', '$isAssumptionCost','$isShowRecord','$showProjectBy')");
        $model = new Tmp_PnlProjectList();
        $balance = Tmp_PnlProjectList::getPopulate();
        $balance->orderBy('tgl_so', 'asc');
        $balance->orderBy('no_so', 'asc');
        $data = [
            'result' => true,
            'balance' => $balance->get()
        ];

        return response()->json($data);
    }


    public function getPnlProject(Request $request)
    {
        $model = new PnlProject();
        $fields = $model->getTableColumns();
        $so_id = $request->input('so_id');
        $commision = PnlProject::getPopulate();
        $commision->where('no_so', $so_id);
        $commision->orderBy('idx', 'asc');
        $result = $commision->get();
        $c = count($result);
        if ($c == 0) {
            $commision = PnlProjectDef::getPopulate();
            $commision->orderBy('idx', 'asc');
            $result = $commision->get();
        }
        $data = [
            'result' => true,
            'balance' => $result
        ];

        return response()->json($data);
    }

    public function pnlProjectSave(Request $request)
    {
        DB::beginTransaction();
        try {
            $so_id = $request->so_id;
            PnlProject::where('no_so', $so_id)->delete();
            $i = 0;
            foreach ($request->data as $data) {
                $idx = $i + 1;
                if ($data['type'] == 'prosen') {
                    $post = [
                        'ket' => $data['ket'],
                        'no_so' => $so_id,
                        'rate' => floatval($data['value']),
                        'nilai' => 0,
                        'idx' => $idx
                    ];
                } else {
                    $post = [
                        'ket' => $data['ket'],
                        'no_so' => $so_id,
                        'rate' => 0,
                        'nilai' =>  floatval($data['value']),
                        'idx' => $idx
                    ];
                }
                $model = PnlProject::addData($post);
                $i++;
            }
            $ph = SalesOrder::find($so_id);
            $ph->note_ph = $request->note_ph;
            $ph->save();

            DB::commit();
            $message = 'Succesfully save data.';
            $data = [
                "result" => true,
                'message' => $message,
            ];
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Terjadi Error Server.';
            $data = [
                "result" => false,
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            return $data;
        }
    }
}
