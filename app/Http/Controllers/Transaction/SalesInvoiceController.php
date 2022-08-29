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
use App\Models\Transaction\SalesInvoiceDetailUm;
use App\Models\Master\EfakturDetail;
use App\Models\Master\FilePath;
use App\Models\Transaction\SalesOrderDetail;
use App\Models\Master\Company;
use App\Models\Master\CompanyBankAccount;
use App\Models\Master\DateCutOff;

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
            $dates =  "SI/" .  date_format(date_create($request['head']['TGL_BUKTI']), 'ymd') . "%";
            $si = SalesInvoice::select('*')
                ->where('NO_BUKTI', 'LIKE', $dates)
                ->orderby('NO_BUKTI', 'DESC')
                ->take(1)
                ->get();
            $inc = "001";
            if (count($si) > 0) {
                $inc = sprintf("%03d", substr($si[0]->NO_BUKTI, 10) + 1);
            }
            $NO_BUKTI = "SI/" .  date_format(date_create($request['head']['TGL_BUKTI']), 'ymd') . "-" . $inc;
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
                $getTax = salesInvoice::select('jual_det.tax')
                    ->leftJoin('jual_det', 'jual_det.No_BUKTI', 'jual_head.NO_BUKTI')
                    ->where('jual_head.no_so_um', $request->head['no_so'])
                    ->where('jual_head.isUM', 'Y')
                    ->orderby('jual_head.TGLCREATE', 'DESC')
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
            for ($i = 0; $i < count($request->attach); $i++) {
                $val =  "SI_" . date_format(date_create($request['head']['TGL_BUKTI']), 'ymd')  . "-" . $inc . "-" . ($i + 1) . "." . $request->attach[$i]['extension'];
                $attach = [];
                $attach = [
                    'module' => 'SI',
                    'name' => $NO_BUKTI,
                    'value' => $val,
                    'path' => 'document/SI/' . date_format(date_create($request->head['TGL_BUKTI']), 'Y') . '/' . $val
                ];
                $model = FilePath::addData($attach);
            }
            DB::commit();
            $edate = date('Y-m-d');
            DB::select("CALL TF_BB_SI('$NO_BUKTI', '2018-01-01', '$edate', '%','N')");
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
            $message = 'Server Error.';
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
            if ($request->param == 'd') {
                $detail = SalesInvoiceDetail::select('*')->Where('NO_BUKTI', $request->NO_BUKTI)->get();
            } else {
                $detail = SalesOrder::select('kontrak_head.*', 'kontrak_det.*', DB::RAW('"" as no_sj'))
                    ->leftJoin('kontrak_det', 'kontrak_head.NO_BUKTI', 'kontrak_det.NO_BUKTI')
                    ->where('kontrak_head.NO_BUKTI', $head[0]->no_so_um)->get();
            }
            $um = array(
                [
                    'total' => 0,
                    'ppn' => 0
                ],
            );
            $do = "-";
            $so = SalesOrder::where('kontrak_head.NO_BUKTI', $head[0]->no_so_um)->get();
        } else {
            $detail = SalesInvoice::leftJoin('jual_det', 'jual_head.NO_BUKTI', 'jual_det.NO_BUKTI')
                ->where('jual_head.NO_BUKTI', $request->NO_BUKTI)
                ->select('jual_head.*', 'jual_det.*')
                ->get();
            $um =  SalesInvoice::where('no_so_um', $head[0]->no_so)
                ->where('isUM', 'Y')
                ->select(DB::RAW('sum(totdetail) as total, sum(ppntotdetail) as ppn'))
                ->get();
            $do = $detail[0]->no_sj;
            $so = SalesOrder::where('kontrak_head.NO_BUKTI', $head[0]->no_so)->get();
        }
        $attach = FilePath::where('name', $request->NO_BUKTI)->where('module', 'SI')->select('*')->get();
        $mergeData = [
            "company" => $company,
            "company_bank" => $company_bank,
            "head" => $head,
            "detail" => $detail,
            "um" => $um,
            "do" => $do,
            "so" => $so,
            "attach" => $attach
        ];
        $data = [
            "result" => true,
            'si' => $mergeData,
        ];
        return $data;
    }

    public function salesInvoiceUpdate(Request $request)
    {
        $oldSiId = $request['head']['si_id'];
        $dates =  "SI/" . date_format(date_create($request['head']['TGL_BUKTI']), 'ymd') . "%";
        $edate = date('Y-m-d');
        $oldBs = DB::select("CALL TF_BB_SI('$oldSiId', '2018-01-01', '$edate', '%','Y')");
        $oldHead = SalesInvoice::select('*')->where('NO_BUKTI', $oldSiId)->get();
        $oldDet = SalesInvoiceDetail::select('*')->where('NO_BUKTI', $oldSiId)->get();
        $oldDp = SalesInvoiceDetailUm::select('*')->where('NO_BUKTI', $oldSiId)->get();

        if (substr($oldSiId, 0, 9) != substr($dates, 0, 9)) {
            $si = SalesInvoice::select('*')
                ->where('NO_BUKTI', 'LIKE', $dates)
                ->orderby('NO_BUKTI', 'DESC')
                ->take(1)
                ->get();
            $inc = "001";
            if (count($si) > 0) {
                $inc = sprintf("%03d", substr($si[0]->NO_BUKTI, 10) + 1);
            }
            $NO_BUKTI = "SI/" . date_format(date_create($request['head']['TGL_BUKTI']), 'ymd') . "-" . $inc;
        } else {
            $NO_BUKTI = $oldSiId;
        }

        DB::beginTransaction();
        try {
            $request['head'] += ['NO_BUKTI' => $NO_BUKTI];
            $no_pajak = $request->head['no_pajakF'] . "" . $request->head['no_pajakE'];
            $request['head'] += ['no_pajak' => $no_pajak];

            $model = SalesInvoice::where('NO_BUKTI', $oldSiId)
                ->update([
                    'NO_BUKTI' => $request->head['NO_BUKTI'],
                    'TGL_BUKTI' => $request->head['TGL_BUKTI'],
                    'ID_CUST' => $request->head['ID_CUST'],
                    'NM_CUST' => $request->head['NM_CUST'],
                    'TEMPO' => $request->head['TEMPO'],
                    'ID_SALES' => $request->head['ID_SALES'],
                    'NM_SALES' => $request->head['NM_SALES'],
                    'KETERANGAN' => $request->head['KETERANGAN'],
                    'EDITOR' => $request->head['EDITOR'],
                    'rate' => $request->head['rate'],
                    'curr' => $request->head['curr'],
                    'no_so' => $request->head['no_so'],
                    'alamatkirim' => $request->head['alamatkirim'],
                    'pay_term' => $request->head['pay_term'],
                    'isUM' => $request->head['isUM'],
                    'no_so_um' => $request->head['no_so_um'],
                    'uangmuka' => $request->head['uangmuka'],
                    'totdetail' => $request->head['totdetail'],
                    'uangmuka_ppn' => $request->head['uangmuka_ppn'],
                    'ppntotdetail' => $request->head['ppntotdetail'],
                    'no_rek' => $request->head['no_rek'],
                    'isWapu' => $request->head['isWapu'],
                    'no_tt' => $request->head['no_tt'],
                    'tgl_tt' => $request->head['tgl_tt'],
                    'penerima_tt' => $request->head['penerima_tt'],
                    'isSI_UM_FINAL' => $request->head['isSI_UM_FINAL'],
                    'PPN' => $request->head['PPN'],
                    'no_pajak' => $request->head['no_pajak']
                ]);

            $model = SalesInvoiceDetail::where('NO_BUKTI', $NO_BUKTI)->delete();
            $model = SalesInvoiceDetailUm::where('NO_BUKTI', $NO_BUKTI)->delete();

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
            $model = EfakturDetail::where('no_faktur', $NO_BUKTI)->update(['no_faktur' => '']);
            $masterNoPajak = str_replace("-", ".", $request->head['no_pajakE']);
            $model = EfakturDetail::where('nomor', $masterNoPajak)->update(['no_faktur' =>  $NO_BUKTI,]);
            if ($request['head']['isUM'] != "Y") {
                $getTax = salesInvoice::select('jual_det.tax')
                    ->leftJoin('jual_det', 'jual_det.No_BUKTI', 'jual_head.NO_BUKTI')
                    ->where('jual_head.no_so_um', $request->head['no_so'])
                    ->where('jual_head.isUM', 'Y')
                    ->orderby('jual_head.TGLCREATE', 'DESC')
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


            $getDateLocked = DateCutOff::select('*')->get();
            if ($getDateLocked[0]->tanggal2 > $request->head['TGL_BUKTI']) {
                $model = DB::select("CALL TF_BB_SI('$NO_BUKTI', '2018-01-01', '$edate', '%','N')");
                $newBs = DB::select("CALL TF_BB_SI('$NO_BUKTI', '2018-01-01', '$edate', '%','Y')");
                if ($oldBs[0]->debet_rp != $newBs[0]->debet_rp || $oldBs[0]->kredit_rp != $newBs[0]->kredit_rp) {
                    // rollback
                    // Log::debug($oldHead);
                    // Log::debug($oldDet);
                    // Log::debug($oldDp);
                    $model = SalesInvoice::where('NO_BUKTI', $NO_BUKTI)
                        ->update([
                            'NO_BUKTI' => $oldHead[0]->NO_BUKTI,
                            'TGL_BUKTI' => $oldHead[0]->TGL_BUKTI,
                            'ID_CUST' => $oldHead[0]->ID_CUST,
                            'NM_CUST' => $oldHead[0]->NM_CUST,
                            'TEMPO' => $oldHead[0]->TEMPO,
                            'ID_SALES' => $oldHead[0]->ID_SALES,
                            'NM_SALES' => $oldHead[0]->NM_SALES,
                            'KETERANGAN' => $oldHead[0]->KETERANGAN,
                            'EDITOR' => $oldHead[0]->EDITOR,
                            'rate' => $oldHead[0]->rate,
                            'curr' => $oldHead[0]->curr,
                            'no_so' => $oldHead[0]->no_so,
                            'alamatkirim' => $oldHead[0]->alamatkirim,
                            'pay_term' => $oldHead[0]->pay_term,
                            'isUM' => $oldHead[0]->isUM,
                            'no_so_um' => $oldHead[0]->no_so_um,
                            'uangmuka' => $oldHead[0]->uangmuka,
                            'totdetail' => $oldHead[0]->totdetail,
                            'uangmuka_ppn' => $oldHead[0]->uangmuka_ppn,
                            'ppntotdetail' => $oldHead[0]->ppntotdetail,
                            'no_rek' => $oldHead[0]->no_rek,
                            'isWapu' => $oldHead[0]->isWapu,
                            'no_tt' => $oldHead[0]->no_tt,
                            'tgl_tt' => $oldHead[0]->tgl_tt,
                            'penerima_tt' => $oldHead[0]->penerima_tt,
                            'isSI_UM_FINAL' => $oldHead[0]->isSI_UM_FINAL,
                            'PPN' => $oldHead[0]->PPN,
                            'no_pajak' => $oldHead[0]->no_pajak
                        ]);

                    $model = SalesInvoiceDetail::where('NO_BUKTI', $NO_BUKTI)->delete();
                    $model = SalesInvoiceDetailUm::where('NO_BUKTI', $NO_BUKTI)->delete();

                    for ($i = 0; $i < count($oldDet); $i++) {
                        $detail = [];
                        $detail['NO_BUKTI'] = $NO_BUKTI;
                        $detail['NO_STOCK'] = $oldDet[$i]->NO_STOCK;
                        $detail['NM_STOCK'] = $oldDet[$i]->NM_STOCK;
                        $detail['QTY'] = $oldDet[$i]->QTY;
                        $detail['SAT'] = $oldDet[$i]->SAT;
                        $detail['HARGA'] = $oldDet[$i]->HARGA;
                        $detail['DISC1'] = $oldDet[$i]->DISC1;
                        $detail['DISC2'] = $oldDet[$i]->DISC2;
                        $detail['DISC3'] = $oldDet[$i]->DISC3;
                        $detail['DISCRP'] = $oldDet[$i]->DISCRP;
                        $detail['discrp2'] = $oldDet[$i]->discrp2;
                        $detail['KET'] = $oldDet[$i]->KET;
                        $detail['id_lokasi'] = $oldDet[$i]->id_lokasi;
                        $detail['tax'] = $oldDet[$i]->tax;
                        $detail['kode_group'] = $oldDet[$i]->kode_group;
                        $detail['no_sj'] = $oldDet[$i]->no_sj;
                        $model = SalesInvoiceDetail::addData($detail);
                    }

                    $model = EfakturDetail::where('no_faktur', $NO_BUKTI)->update(['no_faktur' => '']);
                    $masterNoPajak = str_replace("-", ".", $oldHead[0]->no_pajak);
                    $model = EfakturDetail::where('nomor', $masterNoPajak)->update(['no_faktur' =>  $oldHead[0]->NO_BUKTI]);
                    if ($oldHead[0]->isUM != "Y") {
                        $getTax = salesInvoice::select('jual_det.tax')
                            ->leftJoin('jual_det', 'jual_det.No_BUKTI', 'jual_head.NO_BUKTI')
                            ->where('jual_head.no_so_um', $oldHead[0]->no_so)
                            ->where('jual_head.isUM', 'Y')
                            ->orderby('jual_head.TGLCREATE', 'DESC')
                            ->take(1)
                            ->get();
                        $getDp = SalesOrderDetailUm::select('*')->where('NO_BUKTI', $oldHead[0]->NO_BUKTI)->get();
                        $totalUm = $oldHead[0]->uangmuka;
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
                    $result = false;
                    $message = 'Unable to update data. Change can create different values in the general ledger';
                    $NO_BUKTI = $oldHead[0]->NO_BUKTI;
                } else {
                    $message = 'Succesfully update data.';
                    $result = true;
                }
            } else {
                $message = 'Succesfully save data.';
                $result = true;
            }

            $model = FilePath::where('name', $NO_BUKTI)->where('module', 'SI')->delete();
            for ($i = 0; $i < count($request->attach); $i++) {
                $val =  "SI_" . substr($NO_BUKTI, 3) . "-" . ($i + 1) . "." . $request->attach[$i]['extension'];
                $attach = [];
                $attach = [
                    'module' => 'SI',
                    'name' => $NO_BUKTI,
                    'value' => $val,
                    'path' => 'document/SI/' . date_format(date_create($request->head['TGL_BUKTI']), 'Y') . '/' . $val
                ];
                $model = FilePath::addData($attach);
            }

            $data = [
                "result" => $result,
                'message' => $message,
                "data" => $model,
                "id" => $NO_BUKTI
            ];

            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Server Error.';
            $data = [
                "result" => false,
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            Log::debug($e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
            return $data;
        }
    }

    public function salesInvoiceDelete(Request $request)
    {
        try {
            $fileLocal = FilePath::where('name', $request->NO_BUKTI)->where('module', 'SI')->get();
            FilePath::where('name', $request->NO_BUKTI)->where('module', 'SI')->delete();
            SalesInvoice::deleteData($request->NO_BUKTI);
            EfakturDetail::where('no_faktur', $request->NO_BUKTI)->update(['no_faktur' => 'CANCEL']);

            DB::commit();
            $message = 'Succesfully delete data.';
            $data = [
                "result" => true,
                'message' => $message,
                'fileLocal' => $fileLocal
            ];
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Something wrong! Cannot delete data.';
            $data = [
                "result" => false,
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            Log::debug($e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
            return $data;
        }
    }
}
