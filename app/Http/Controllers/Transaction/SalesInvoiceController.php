<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Tree\ModuleNode;
use App\Models\Transaction\SalesOrder;
use App\Models\Transaction\SalesOrderDetailUm;
use App\Models\Transaction\SalesDelivery;
use App\Models\Transaction\SalesDeliveryDetail;
use App\Models\Transaction\SalesInvoice;

class SalesInvoiceController extends Controller
{

    public function siGetEfaktur(Request $request)
    {
        $no_bukti2 = str_replace(":", "/", $request->no_bukti2);
        $model_si = SalesInvoice::getSiEfaktur()
            ->leftjoin('jual_det', 'jual_head.NO_BUKTI', 'jual_det.NO_BUKTI')
            ->where('jual_head.no_bukti2', $no_bukti2);
        $modelSi = $model_si->get()->toArray();
        $no_so = $modelSi[0]['no_so'];
        if (empty($modelSi[0]['no_so'])) {
            $no_so = $modelSi[0]['no_so_um'];
        }

        $modelSo = SalesOrder::getById()->where('kontrak_head.NO_BUKTI', $no_so)->get()->toArray();
        if ($modelSo == null) {
            $modelSo[0]['PO_CUST'] = null;
        }
        $data = [
            'si' => $modelSi,
            'so' => $modelSo
        ];
        return response()->json($data);
    }

    public function getList(Request $request)
    {
        $model = new SalesInvoice();
        $fields = $model->getTableColumns();
        $void = $request->input('void');

        $kategori = $request->input('kategori');
        $fdate = $request->input('fdate');
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $si = SalesInvoice::getPopulateSalesInvoice();
        if ($void == "Y") {
            $si = SalesInvoice::getPopulateSalesInvoiceDetail();
        }

        if ($fdate == "Y") {
            if ($sdate == null) {
                $sdate = Carbon::parse($request->sdate)->format('Y-m-d');
            }

            if ($edate == null) {
                $edate = Carbon::parse($request->edate)->format('Y-m-d');
            }
            $si->whereBetween('jual_head.TGL_BUKTI', [$sdate, $edate]);
        }

        if ($kategori == "lunas") {
            $si->whereRaw('jual_head.total_rp = bayar.income');
        } else if ($kategori == "outstanding") {
            $si->whereRaw('jual_head.total_rp > bayar.income');
        }

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $si->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('no_bukti2', 'LIKE', "%$keyword%");
                });
            }
        }
        $filteredData = $si->get();
        $totalRows = $si->count();

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
                $si->orderBy($column, $direction);
            }
        } else {
            // $si->orderBy('jual_head.no_bukti2', 'asc');
        }
        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $si->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $si->skip($offset)->take($limit);
            }
        }
        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'si' => $si->get()
        ];
        return response()->json($data);
    }

    public function dataDo(Request $request)
    {
        $model = new SalesInvoice();
        $fields = $model->getTableColumns();
        $so_id = $request->input('so_id');
        $si = SalesInvoice::geDataDo();

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $si->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('NO_BUKTI', 'LIKE', "%$keyword%");
                });
            }
        }
        $si->where('kontrak_head.NO_BUKTI', $so_id);
        $si->groupby('kontrak_head.NO_BUKTI');
        $filteredData = $si->get();
        $totalRows = $si->count();

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
                $si->orderBy($column, $direction);
            }
        } else {
            $si->orderBy('kontrak_head.NO_BUKTI', 'asc');
        }
        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $si->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $si->skip($offset)->take($limit);
            }
        }
        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'si' => $si->get()
        ];
        return response()->json($data);
    }

    public function dataSoDp(Request $request)
    {
        $data = [
            'result' => false,
            'soDp' => "Data not found"
        ];
        $model = new SalesOrderDetailUm();
        $fields = $model->getTableColumns();
        $so_id = $request->input('so_id');
        $soDp = SalesOrderDetailUm::select('*');
        $soDp->where('NO_BUKTI', $so_id);
        $soDp->orderBy('urut');
        $count_soDp = $soDp->count();
        if ($count_soDp > 0) {
            $si = SalesInvoice::select('*');
            $si->where('isUM', "Y");
            $si->where('no_so_um', $so_id);
            $count_si = $si->count();
            if ($count_si > 0) {
                $getSi = $si->get();
                $cekFinal = $si->where('isSI_UM_FINAL', 'Y')->count();
                if ($cekFinal < 1) {
                    // data tidak ditemukan karena sudah final
                    $filteredData = $soDp->get();
                    $data = [
                        'result' => true,
                        'soDp' => $filteredData
                    ];
                }
            }
        }
        return response()->json($data);
    }

    public function getDo(Request $request)
    {
        $model = new SalesDelivery();
        $fields = $model->getTableColumns();
        $do_id = $request->input('do_id');
        $do = SalesDelivery::select('kontrak_det.*', 'sj_det.id_lokasi');
        $do->leftjoin('sj_det', 'sj_head.NO_BUKTI', 'sj_det.NO_BUKTI');
        $do->leftjoin('kontrak_det', 'sj_head.no_So', 'kontrak_det.NO_BUKTI');
        $do->whereRaw('`sj_det`.`NO_STOCK` = `kontrak_det`.`NO_STOCK`');
        $do->where('sj_det.NO_BUKTI', $do_id);
        $do->orderBy('sj_det.IDXURUT', 'ASC');
        $data = [
            'result' => true,
            'do' => $do->get()
        ];

        return response()->json($data);
    }
}
