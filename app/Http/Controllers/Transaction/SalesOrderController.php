<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use App\Tree\ModuleNode;
use App\Models\Transaction\SalesOrder;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Transaction\SalesOrderDetailUm;

class SalesOrderController extends Controller
{
    public function getList(Request $request)
    {
        $model = new SalesOrder();
        $fields = $model->getTableColumns();
        $void = $request->input('void');

        $kategori = $request->input('kategori');
        $fdate = $request->input('fdate');
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $so = SalesOrder::getPopulateSalesOrder();
        if ($void == "Y") {
            $so = SalesOrder::getPopulateSalesOrderDetail();
        }

        if ($fdate == "Y") {
            if ($sdate == null) {
                $sdate = Carbon::parse($request->sdate)->format('Y-m-d');
            }

            if ($edate == null) {
                $edate = Carbon::parse($request->edate)->format('Y-m-d');
            }
            $so->whereBetween('TGL_BUKTI', [$sdate, $edate]);
        }

        if ($kategori == "lunas") {
            $so->where('QTY', '<=', DB::RAW('SJ_QTY-RJ_QTY'));
        } else if ($kategori == "outstanding") {
            $so->where('QTY', '>', DB::RAW('SJ_QTY-RJ_QTY'));
        }

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $so->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('NO_BUKTI', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $so->get();
        $totalRows = $so->count();

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
                $so->orderBy($column, $direction);
            }
        } else {
            $so->orderBy('NO_BUKTI', 'asc');
        }
        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $so->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $so->skip($offset)->take($limit);
            }
        }
        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'so' => $so->get()
        ];
        return response()->json($data);
    }

    public function SalesOrderAddSave(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = SalesOrder::addData($request->head);
            for ($i = 0; $i < count($request->detail); $i++) {
                $model = SalesOrderDetail::addData($request->detail[$i]);
            }
            for ($i = 0; $i < count($request->um); $i++) {
                $model = SalesOrderDetailUm::addData($request->um[$i]);
            }

            DB::commit();
            $message = 'Succesfully save data.';
            $data = [
                "result" => true,
                'message' => $message,
                "data" => $model
            ];

            // DB::rollback();
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

    public function salesOrderDetail(Request $request)
    {
        // $model = SalesOrderDetail::getSoDetail()->latest('nourut')->first();
        $head = salesOrder::where('kontrak_head.NO_BUKTI', $request->NO_BUKTI)->select('*')->get();
        $detail = salesOrderDetail::where('kontrak_det.NO_BUKTI', $request->NO_BUKTI)->select('*')->get();
        $um = SalesOrderDetailUm::where('kontrak_det_um.NO_BUKTI', $request->NO_BUKTI)->select('*')->get();

        $mergeData = [
            "head" => $head,
            "detail" => $detail,
            "um" => $um
        ];
        $data = [
            "result" => true,
            'so' => $mergeData,
        ];
        return $data;
    }

    public function soGetLastDetail()
    {
        $model = SalesOrderDetail::getSoDetail()
            ->latest('nourut')
            ->first();
        return response()->json($model);
    }
}
