<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Tree\ModuleNode;
use App\Models\Master\Inventory;
use App\Models\Transaction\SalesOrder;
use App\Models\Transaction\SalesOrderDetailUm;
use App\Models\Transaction\SalesDelivery;
use App\Models\Transaction\SalesDeliveryDetail;
use App\Models\Transaction\SalesInvoice;
use App\Models\Transaction\SalesInvoiceDetail;
use App\Models\Master\EfakturDetail;
use App\Models\Master\FilePath;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Master\Company;
use App\Models\Master\CompanyBankAccount;
use App\Models\Transaction\SalesInvoiceDetailUm;

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

        $si->whereRaw(DB::RAW("( sj_head.NO_BUKTI <> jual_det.no_sj AND `kontrak_head`.`NO_BUKTI` =  '" . $so_id . "' ) OR ( `kontrak_head`.`NO_BUKTI` = '" . $so_id . "' AND `jual_det`.`no_sj` IS NULL ) "));
        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $si->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('NO_BUKTI', 'LIKE', "%$keyword%");
                });
            }
        }
        // $si->where('kontrak_head.NO_BUKTI', $so_id);
        $si->groupby('sj_head.NO_BUKTI');
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
            // cek so_um
            $si = SalesInvoice::select(DB::RAW('sum(totdpp_rp) as total, sum(ppntotdetail) as ppn'));
            $si->where('isUM', "Y");
            $si->where('no_so_um', $so_id);
            $count_si = $si->count();

            // cek sudah pernah keluar invoice
            if ($count_si > 0) {
                // sudah pernah bikin
                $getSi = $si->get();
                $cekFinal = $si->where('isSI_UM_FINAL', 'Y')->count();
                if ($cekFinal > 0) {
                    // final ya ga keluar lagi
                } else {
                    $filteredData = $soDp->get();
                    $data = [
                        'result' => true,
                        'soDp' => $filteredData,
                        'totalSiDp' => $getSi[0]->total,
                        'totalPPnSiDp' => $getSi[0]->ppn
                    ];
                }
            } else {
                // belum pernah bikin
                $filteredData = $soDp->get();
                $data = [
                    'result' => true,
                    'soDp' => $filteredData,
                    'totalSiDp' => '0',
                    'totalPPnSiDp' => '0'
                ];
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

        $siDp = SalesInvoice::select(DB::RAW('sum(totdpp_rp) as total, sum(ppntotdetail)as ppn'));
        $siDp->where('isUM', "Y");
        $siDp->where('no_so_um', $request->input('so_id'));
        $getSiDp = $siDp->get();

        if ($getSiDp[0]->total == null) {
            $dp = 0;
            $ppn = 0;
        } else {
            $dp = $getSiDp[0]->total;
            $ppn = $getSiDp[0]->ppn;
        }

        $data = [
            'result' => true,
            'do' => $do->get(),
            'siDp' => $dp,
            'ppnSiDp' => $ppn
        ];

        return response()->json($data);
    }

    public function getSoDp(Request $request)
    {
        $model = new SalesDelivery();
        $fields = $model->getTableColumns();
        $dp = Inventory::select('NO_STOCK', 'kontrak.*');
        $dp->leftJoin(DB::RAW("( SELECT kontrak_det_um.*, kontrak_head.curr FROM kontrak_head LEFT JOIN kontrak_det_um ON kontrak_head.NO_BUKTI = kontrak_det_um.NO_BUKTI WHERE kontrak_head.NO_BUKTI = '" . $request->input('so_id') . "' ) AS kontrak"), 'stock.no_stock', 'LIKE', DB::RAW("concat('%',kontrak.curr)"));
        $dp->where('kontrak.NO_BUKTI', $request->input('so_id'));
        $dp->where('kontrak.idxurut', $request->input('um_id'));
        $dp->take(1);

        $data = [
            'result' => true,
            'dp' => $dp->get()
        ];
        return response()->json($data);
    }

    public function salesInvoiceAddSave(Request $request)
    {
        DB::beginTransaction();
        try {
            $dates =  "SI/" . date('ymd') . "%";
            $si = SalesInvoice::select('*')
                ->where('NO_BUKTI', 'LIKE', $dates)
                ->orderby('NO_BUKTI', 'DESC')
                ->take(1)
                ->get();
            $inc = "001";
            if (count($si) > 0) {
                $inc = sprintf("%03d", substr($si[0]->NO_BUKTI, 10) + 1);
            }
            $NO_BUKTI = "SI/" . date('ymd') . "-" . $inc;
            $no_pajak = $request->head['no_pajakF'] . "" . $request->head['no_pajakE'];
            $request['head'] += ['NO_BUKTI' => $NO_BUKTI, 'no_pajak' => $no_pajak];
            $model = SalesInvoice::addData($request->head);

            for ($i = 0; $i < count($request->detail); $i++) {
                $detail = [];
                $detail['NO_BUKTI'] = $NO_BUKTI;
                $detail['NO_STOCK'] = $request->detail[$i]['NO_STOCK'];
                $detail['NM_STOCK'] = $request->detail[$i]['NM_STOCK'];
                $detail['QTY'] = $request->detail[$i]['QTY'];
                $detail['SAT'] = $request->detail[$i]['SAT'];
                $detail['HARGA'] = $request->detail[$i]['HARGA'];
                $detail['DISC1'] = $request->detail[$i]['DISC1'];
                $detail['DISC2'] = $request->detail[$i]['DISC2'];
                $detail['DISC3'] = $request->detail[$i]['DISC3'];
                $detail['DISCRP'] = $request->detail[$i]['DISCRP'];
                $detail['discrp2'] = $request->detail[$i]['discrp2'];
                $detail['KET'] = $request->detail[$i]['KET'];
                $detail['id_lokasi'] = $request->detail[$i]['id_lokasi'];
                $detail['tax'] = $request->detail[$i]['tax'];
                $detail['kode_group'] = $request->detail[$i]['kode_group'];
                $detail['no_sj'] = $request->detail[$i]['no_sj'];
                $model = SalesInvoiceDetail::addData($detail);
            }
            $masterNoPajak = str_replace("-", ".", $request->head['no_pajakE']);
            $model = EfakturDetail::where('nomor', $masterNoPajak)
                ->update([
                    'no_faktur' =>  $NO_BUKTI,
                ]);
            if ($request['head']['isUM'] != "Y") {
                $getTax = salesInvoiceDetail::select('*')
                    ->where('NO_BUKTI', $NO_BUKTI)
                    ->orderby('URUT', 'DESC')
                    ->take(1)
                    ->get();
                $getDp = SalesOrderDetailUm::select('*')->where('NO_BUKTI', $request['head']['no_so'])->get();
                $totalUm = $request['head']['uangmuka'];
                $umSO = 0;
                foreach ($getDp as $dp) {
                    $umSO += $dp->nilai;
                }
                $i = 1;
                foreach ($getDp as $dp) {
                    $postDp = [];
                    $postDp['NO_BUKTI'] =  $NO_BUKTI;
                    $postDp['keterangan'] =  $dp->keterangan;
                    $postDp['nilai'] =   $dp->nilai / $umSO * $totalUm;
                    $postDp['nourut'] =  $i;
                    $postDp['TAX'] = $getTax[0]->tax;
                    $model = SalesInvoiceDetailUm::addData($postDp);
                    $i++;
                }
            }
            DB::commit();
            $message = 'Succesfully save data.';
            $data = [
                "result" => true,
                'message' => $message,
                "data" => $model,
                "id" => $NO_BUKTI
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
            Log::debug($e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
            return $data;
        }
    }

    public function salesInvoiceDetail(Request $request)
    {
        $company = Company::select('*')->Where('remark', 'finance')->get();
        $company_bank = CompanyBankAccount::select('*')->get();
        $head = SalesInvoice::select('*')->Where('NO_BUKTI', $request->NO_BUKTI)->get();
        $um = $head[0]->isUM;
        if ($um == "Y") {
            $detail = SalesOrder::leftJoin('kontrak_det', 'kontrak_head.NO_BUKTI', 'kontrak_det.NO_BUKTI')
                ->where('kontrak_head.NO_BUKTI', $head[0]->no_so_um)->get();
            $do = "-";
            // } else {
        }

        //     $head = salesOrder::leftJoin('mascustomer', 'kontrak_head.ID_CUST', 'mascustomer.ID_CUST')
        //         ->where('kontrak_head.NO_BUKTI', $request->NO_BUKTI)
        //         ->select('kontrak_head.*', 'mascustomer.ALAMAT1', 'mascustomer.ALAMAT2', 'mascustomer.KOTA', 'mascustomer.PROPINSI', 'mascustomer.al_npwp')
        //         ->get();
        //     $detail = salesInvoiceDetail::leftJoin('stock', 'stock.no_stock', 'kontrak_det.NO_STOCK')
        //         ->where('kontrak_det.NO_BUKTI', $request->NO_BUKTI)
        //         ->select('kontrak_det.*', 'stock.merk')
        //         ->get();
        // $um = salesInvoiceDetailUm::where('kontrak_det_um.NO_BUKTI', $request->NO_BUKTI)->select('*')->get();
        $attach = FilePath::where('name', $request->NO_BUKTI)->where('module', 'SI')->select('*')->get();

        $mergeData = [
            "company" => $company,
            "company_bank" => $company_bank,
            "head" => $head,
            "detail" => $detail,
            "do" => $do,
            "attach" => $attach
        ];
        // log::debug($head);
        // log::debug($detail);
        $data = [
            "result" => true,
            'si' => $mergeData,
        ];
        return $data;
    }
}
