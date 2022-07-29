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
use App\Models\Transaction\SalesDeliveryDetail;
use App\Models\Transaction\SalesReturnDetail;
use App\Models\Master\Customer;
use App\Models\Master\CustomerShippingAddress;

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
            // update address customer
            if ($request->head['use_branch'] == '1') {
                $model = CustomerShippingAddress::where('customer_id', $request->head['ID_CUST'])
                    ->where('address_alias', $request->customer['address_alias'])
                    ->update([
                        'other_address' => $request->customer['other_address'],
                        'user_modified' => $request->head['EDITOR']
                    ]);
            }

            // insert head update
            $model = SalesOrder::addData($request->head);
            //insert child order
            for ($i = 0; $i < count($request->detail); $i++) {
                $vintrasId = $request->detail[$i]['VINTRASID']; //no_nota vintras
                $tahunVintras = $request->detail[$i]['tahun']; //year of inquiry
                $tipeInquiry = 'Tipe_Inquiry'; //field name on vintras
                $paramVintras = '2'; //for first update on vintras
                $userVintras = $request->head['CREATOR'];
                $itemPath = ""; //path file reference
                $uniParam = "SO||" . $request->head['jenis'] . "||" . $request->head['TGL_BUKTI'] . "||" . $request->head['tgl_due'] . "||" . $request->head['PO_CUST'] . "||" . $request->detail[$i]['QTY'] . " " . $request->detail[$i]['SAT'] . "||" . $request->detail[$i]['KET'] . "||" . $request->detail[$i]['merk'] . "||" . $request->head['no_ref'] . "||" . $request->head['NO_BUKTI'] . "||" . $request->head['NM_SALES'] . "||" . $itemPath . "||" . $request->head['TEMPO'] . " days " . $request->head['pay_term']; //value update vintras
                if ($vintrasId != '') {
                    DB::select("CALL SP_UPDATE_VINTRAS('$vintrasId','$tahunVintras','$tipeInquiry','$paramVintras','$userVintras','$uniParam')");
                }
                $model = SalesOrderDetail::addData($request->detail[$i]);
            }
            // insert down payment
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
        $head = salesOrder::leftJoin('mascustomer', 'kontrak_head.ID_CUST', 'mascustomer.ID_CUST')
            ->where('kontrak_head.NO_BUKTI', $request->NO_BUKTI)
            ->select('kontrak_head.*', 'mascustomer.ALAMAT1', 'mascustomer.ALAMAT2', 'mascustomer.KOTA', 'mascustomer.PROPINSI', 'mascustomer.al_npwp')
            ->get();
        $detail = salesOrderDetail::leftJoin('stock', 'stock.no_stock', 'kontrak_det.NO_STOCK')
            ->where('kontrak_det.NO_BUKTI', $request->NO_BUKTI)
            ->select('kontrak_det.*', 'stock.merk')
            ->get();
        $um = SalesOrderDetailUm::where('kontrak_det_um.NO_BUKTI', $request->NO_BUKTI)->select('*')->get();

        $mergeData = [
            "head" => $head,
            "detail" => $detail,
            "um" => $um
        ];
        // log::debug($mergeData);
        $data = [
            "result" => true,
            'so' => $mergeData,
        ];
        return $data;
    }
    public function SalesOrderUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            // update address customer
            if ($request->customer['address_alias'] == 'Main Address') {
                $model = Customer::where('ID_CUST', $request->head['ID_CUST'])
                    ->update([
                        'al_npwp' => $request->customer['other_address'],
                        'EDITOR' => $request->head['EDITOR']
                    ]);
            } else {
                $model = CustomerShippingAddress::where('customer_id', $request->head['ID_CUST'])
                    ->where('address_alias', $request->customer['address_alias'])
                    ->update([
                        'other_address' => $request->customer['other_address'],
                        'user_modified' => $request->head['EDITOR']
                    ]);
            }
            $old = salesOrderDetail::leftJoin('stock', 'stock.no_stock', 'kontrak_det.NO_STOCK')
                ->where('kontrak_det.NO_BUKTI', $request->where['id'])
                ->select('kontrak_det.*', 'stock.merk')
                ->get();
            $new = [];
            for ($i = 0; $i < count($request->detail); $i++) {
                // input
                $arrNew = [];
                $arrNew['NO_BUKTI'] = $request->detail[$i]['NO_BUKTI'];
                $arrNew['NO_STOCK'] = $request->detail[$i]['NO_STOCK'];
                $arrNew['NM_STOCK'] = $request->detail[$i]['NM_STOCK'];
                $arrNew['QTY'] = $request->detail[$i]['QTY'];
                $arrNew['SAT'] = $request->detail[$i]['SAT'];
                $arrNew['HARGA'] = $request->detail[$i]['HARGA'];
                $arrNew['DISC1'] = $request->detail[$i]['DISC1'];
                $arrNew['DISC2'] = $request->detail[$i]['DISC2'];
                $arrNew['DISC3'] = $request->detail[$i]['DISC3'];
                $arrNew['DISCRP'] = $request->detail[$i]['DISCRP'];
                $arrNew['discrp2'] = $request->detail[$i]['discrp2'];
                $arrNew['KET'] = $request->detail[$i]['KET'];
                $arrNew['state'] = $request->detail[$i]['state'];
                $arrNew['alasan'] = $request->detail[$i]['alasan'];
                $arrNew['tax'] = $request->detail[$i]['tax'];
                $arrNew['kode_group'] = $request->detail[$i]['kode_group'];
                $arrNew['qty_grup'] = $request->detail[$i]['qty_grup'];
                $arrNew['VINTRASID'] = $request->detail[$i]['VINTRASID'];
                $arrNew['tahun'] = $request->detail[$i]['tahun'];
                $arrNew['merk'] = $request->detail[$i]['merk'];

                // $update = [];
                // $update1 = [];
                // $j = 0;
                // foreach ($old as $val) {
                //     // lama
                //     $cek = '';
                //     if ($val->NO_STOCK == $request->detail[$i]['NO_STOCK']) {
                //         // make array for check diff
                //         $arrOld['NO_BUKTI'] = $val->NO_BUKTI;
                //         $arrOld['NO_STOCK'] = $val->NO_STOCK;
                //         $arrOld['NM_STOCK'] = $val->NM_STOCK;
                //         $arrOld['QTY'] = $val->QTY;
                //         $arrOld['SAT'] = $val->SAT;
                //         $arrOld['HARGA'] = $val->HARGA;
                //         $arrOld['DISC1'] = $val->DISC1;
                //         $arrOld['DISC2'] = $val->DISC2;
                //         $arrOld['DISC3'] = $val->DISC3;
                //         $arrOld['DISCRP'] = $val->DISCRP;
                //         $arrOld['discrp2'] = $val->discrp2;
                //         $arrOld['KET'] = $val->KET;
                //         $arrOld['state'] = $val->state;
                //         $arrOld['alasan'] = $val->alasan;
                //         $arrOld['tax'] = $val->tax;
                //         $arrOld['kode_group'] = $val->kode_group;
                //         $arrOld['qty_grup'] = $val->qty_grup;
                //         $arrOld['VINTRASID'] = $val->VINTRASID;
                //         $arrOld['tahun'] = $val->tahun;
                //         $arrOld['merk'] = $val->merk;
                //         // found change field on array diff
                //         $cek = array_diff_assoc($arrNew, $arrOld);
                //         //Update vintras by vintras id | ps : inquiry '2'
                //         if (!empty($cek)) {
                //             if ($request->detail[$i]['VINTRASID'] != '') {
                //                 $vintrasId = $request->detail[$i]['VINTRASID']; //no_nota vintras
                //                 $tahunVintras = $request->detail[$i]['tahun']; //year of inquiry
                //                 $tipeInquiry = 'Tipe_Inquiry'; //field name on vintras
                //                 $paramVintras = '2'; //for first update on vintras
                //                 $userVintras = $request->head['CREATOR'];
                //                 $itemPath = ""; //path file reference
                //                 $uniParam = "SO||" . $request->head['jenis'] . "||" . $request->head['TGL_BUKTI'] . "||" . $request->head['tgl_due'] . "||" . $request->head['PO_CUST'] . "||" . $request->detail[$i]['QTY'] . " " . $request->detail[$i]['SAT'] . "||" . $request->detail[$i]['KET'] . "||" . $request->detail[$i]['merk'] . "||" . $request->head['no_ref'] . "||" . $request->head['NO_BUKTI'] . "||" . $request->head['NM_SALES'] . "||" . $itemPath . "||" . $request->head['TEMPO'] . " days " . $request->head['pay_term']; //value update vintras
                //                 DB::select("CALL SP_UPDATE_VINTRAS('$vintrasId','$tahunVintras','$tipeInquiry','$paramVintras','$userVintras','$uniParam')");
                //             }
                //         }
                //     }
                // }
                $new[] = $arrNew;
            }

            $oldSort = [];
            foreach ($old as $val) {
                // old array
                $arrOld = [];
                $arrOld['NO_BUKTI'] = $val->NO_BUKTI;
                $arrOld['NO_STOCK'] = $val->NO_STOCK;
                $arrOld['NM_STOCK'] = $val->NM_STOCK;
                $arrOld['QTY'] = $val->QTY;
                $arrOld['SAT'] = $val->SAT;
                $arrOld['HARGA'] = $val->HARGA;
                $arrOld['DISC1'] = $val->DISC1;
                $arrOld['DISC2'] = $val->DISC2;
                $arrOld['DISC3'] = $val->DISC3;
                $arrOld['DISCRP'] = $val->DISCRP;
                $arrOld['discrp2'] = $val->discrp2;
                $arrOld['KET'] = $val->KET;
                $arrOld['state'] = $val->state;
                $arrOld['alasan'] = $val->alasan;
                $arrOld['tax'] = $val->tax;
                $arrOld['kode_group'] = $val->kode_group;
                $arrOld['qty_grup'] = $val->qty_grup;
                $arrOld['VINTRASID'] = $val->VINTRASID;
                $arrOld['tahun'] = $val->tahun;
                $arrOld['merk'] = $val->merk;
                $oldSort[] = $arrOld;
            }

            $vintransDelete = array_udiff($oldSort, $new, fn ($a, $b) => $a <=> $b); // return old value and change to '0' 
            if (!empty($vintransDelete)) {
                foreach ($vintransDelete as $del) {
                    // Log::debug($new);
                    $cOldData = 0;
                    if ($del['VINTRASID'] != '') {
                        foreach ($new as $valNew) {
                            if ($del['VINTRASID'] == $valNew['VINTRASID']) {
                                $vintrasId = $valNew['VINTRASID']; //no_nota vintras
                                $tahunVintras = $valNew['tahun']; //year of inquiry
                                $tipeInquiry = 'Tipe_Inquiry'; //field name on vintras
                                $paramVintras = '2'; //for delete on vintras
                                $userVintras = $request->head['CREATOR'];
                                $itemPath = ""; //path file reference
                                $uniParam = "SO||" . $request->head['jenis'] . "||" . $request->head['TGL_BUKTI'] . "||" . $request->head['tgl_due'] . "||" . $request->head['PO_CUST'] . "||" . $valNew['QTY'] . " " . $valNew['SAT'] . "||" . $valNew['KET'] . "||" . $valNew['merk'] . "||" . $request->head['no_ref'] . "||" . $request->head['NO_BUKTI'] . "||" . $request->head['NM_SALES'] . "||" . $itemPath . "||" . $request->head['TEMPO'] . " days " . $request->head['pay_term']; //value update vintras
                                $cOldData++;
                            }
                        }
                        if ($cOldData == 0) {
                            $vintrasId = $del['VINTRASID']; //no_nota vintras
                            $tahunVintras = $del['tahun']; //year of inquiry
                            $tipeInquiry = 'Tipe_Inquiry'; //field name on vintras
                            $paramVintras = '0'; //for delete on vintras
                            $userVintras = $request->head['CREATOR'];
                            $itemPath = ""; //path file reference
                            $uniParam = "SO||" . $request->head['jenis'] . "||" . $request->head['TGL_BUKTI'] . "||" . $request->head['tgl_due'] . "||" . $request->head['PO_CUST'] . "||" . $del['QTY'] . " " . $del['SAT'] . "||" . $del['KET'] . "||" . $del['merk'] . "||" . $request->head['no_ref'] . "||" . $request->head['NO_BUKTI'] . "||" . $request->head['NM_SALES'] . "||" . $itemPath . "||" . $request->head['TEMPO'] . " days " . $request->head['pay_term']; //value update vintras
                        }
                        DB::select("CALL SP_UPDATE_VINTRAS('$vintrasId','$tahunVintras','$tipeInquiry','$paramVintras','$userVintras','$uniParam')");
                    }
                }
            }
            $vintransInsert = array_udiff($new, $oldSort, fn ($a, $b) => $a <=> $b); // return new value and change to '2'
            if (!empty($vintransInsert)) {
                foreach ($vintransInsert as $ins) {
                    if ($ins['VINTRASID'] != '') {
                        $vintrasId = $ins['VINTRASID']; //no_nota vintras
                        $tahunVintras = $ins['tahun']; //year of inquiry
                        $tipeInquiry = 'Tipe_Inquiry'; //field name on vintras
                        $paramVintras = '0'; //for insert on vintras
                        $userVintras = $request->head['CREATOR'];
                        $itemPath = ""; //path file reference
                        $uniParam = "SO||" . $request->head['jenis'] . "||" . $request->head['TGL_BUKTI'] . "||" . $request->head['tgl_due'] . "||" . $request->head['PO_CUST'] . "||" . $ins['QTY'] . " " . $ins['SAT'] . "||" . $ins['KET'] . "||" . $ins['merk'] . "||" . $request->head['no_ref'] . "||" . $request->head['NO_BUKTI'] . "||" . $request->head['NM_SALES'] . "||" . $itemPath . "||" . $request->head['TEMPO'] . " days " . $request->head['pay_term']; //value update vintras
                        DB::select("CALL SP_UPDATE_VINTRAS('$vintrasId','$tahunVintras','$tipeInquiry','$paramVintras','$userVintras','$uniParam')");
                    }
                }
            }
            SalesOrderDetail::where('NO_BUKTI', $request->where['id'])->delete();
            SalesOrderDetailUm::where('NO_BUKTI', $request->where['id'])->delete();
            $model = SalesOrder::updateData($request->head, $request->where);
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

    public function soGetLastDetail()
    {
        $model = SalesOrderDetail::getSoDetail()
            ->latest('nourut')
            ->first();
        return response()->json($model);
    }

    public function getlistHead(Request $request)
    {
        $model = new SalesOrder();
        $fields = $model->getTableColumns();
        $so = SalesOrder::getPopulateSalesOrderHead();

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

    public function soGetById(Request $request)
    {
        $data = [
            'result' => true,
            'so' => SalesOrder::where('NO_BUKTI', $request->input('so_id'))->get()
        ];
        return response()->json($data);
    }

    public function salesOrderStatus(Request $request)
    {

        $status = SalesOrder::rightjoin('po_det', 'po_det.no_so', 'kontrak_head.NO_BUKTI')
            ->where('kontrak_head.NO_BUKTI', $request->input('NO_BUKTI'))
            ->count();
        // $po = SalesOrder::rightjoin('po_det', 'po_det.no_so', 'kontrak_head.NO_BUKTI')
        // ->where('kontrak_head.NO_BUKTI', $request->input('NO_BUKTI'))
        // ->count();
        // $pi = SalesOrder::rightjoin('beli_det', 'beli_det.NO_BUKTI', 'kontrak_head.NO_BUKTI')
        //     ->where('kontrak_head.NO_BUKTI', $request->input('NO_BUKTI'))
        //     ->count();
        // $ri = SalesOrder::rightjoin('ri_det', 'ri_det.no_so', 'kontrak_head.NO_BUKTI')
        //     ->where('kontrak_head.NO_BUKTI', $request->input('NO_BUKTI'))
        //     ->count();
        // $si = SalesOrder::rightjoin('jual_head', 'jual_head.no_so', 'kontrak_head.NO_BUKTI')
        //     ->where('kontrak_head.NO_BUKTI', $request->input('NO_BUKTI'))
        //     ->count();
        if ($status == 0) {
            $status = SalesOrder::rightjoin('sj_head', 'sj_head.no_So', 'kontrak_head.NO_BUKTI')
                ->where('kontrak_head.NO_BUKTI', $request->input('NO_BUKTI'))
                ->count();
        }
        // $do = SalesOrder::leftjoin('sj_head', 'sj_head.no_So', 'kontrak_head.NO_BUKTI')
        //     ->where('kontrak_head.NO_BUKTI', $request->input('NO_BUKTI'))
        // ->count();


        // 1 = void
        $data = [
            'status' => $status,
            // 'PO' => $po,  // po_det
            // 'PI' => $pi,  // beli_det
            // 'RI' => $ri, // ri_det
            // 'SI' => $si, // jual_head
            // 'DO' => $do, // sj_head
        ];

        // SO->PO->PI->RI->SI->DO
        // Log::debug($do);
        return response()->json($data);
    }

    public function salesOrderDelete(Request $request)
    {
        try {
            // DB::enableQueryLog();
            $model = SalesOrder::deleteData($request->NO_BUKTI);
            DB::commit();
            $message = 'Succesfully delete data.';
            $data = [
                "result" => true,
                'message' => $message,
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
            return $data;
        }
    }

    public function SalesOrderUpdateState(Request $request)
    {
        DB::beginTransaction();
        try {
            $NO_BUKTI = $request->input('where')['NO_BUKTI'];
            $NO_STOCK = $request->input('where')['NO_STOCK'];
            $state = $request->input('post')['state'];
            $do = salesDeliveryDetail::where('NO_STOCK', $NO_BUKTI)->where('NO_BUKTI', $NO_STOCK)->select(DB::RAW('ifnull(sum(qty),0) as qty'))->get();
            $sr = SalesReturnDetail::where('NO_STOCK', $NO_BUKTI)->where('NO_BUKTI', $NO_STOCK)->select(DB::RAW('ifnull(sum(qty),0) as qty'))->get();
            if ($state == 'B') {
                if ($do[0]->qty - $sr[0]->qty > 0) {
                    $message = 'can not cancel this item because there is already delivery';
                } else {
                    $query = salesOrderDetail::where('NO_STOCK', $NO_STOCK)
                        ->where('NO_BUKTI', $NO_BUKTI)
                        ->update([
                            'state' => $state,
                            'alasan' => $request->input('post')['alasan']
                        ]);
                    if ($query) {
                        $message = 'success cancel item : ' . $NO_STOCK;
                    }
                }
            } else if ($state == 'F') {
                if ($do[0]->qty - $sr[0]->qty >= $request->input('post')['qty']) {
                    $message = 'can not finish this item because there is already delivery';
                } else {
                    $query = salesOrderDetail::where('NO_STOCK', $NO_STOCK)
                        ->where('NO_BUKTI', $NO_BUKTI)
                        ->update([
                            'state' => $state,
                            'alasan' => $request->input('post')['alasan']
                        ]);
                    if ($query) {
                        $message = 'success finish item : ' . $NO_STOCK;
                    }
                }
            } else if ($state == '') {
                $query = salesOrderDetail::where('NO_STOCK', $NO_STOCK)
                    ->where('NO_BUKTI', $NO_BUKTI)
                    ->update([
                        'state' => $state,
                        'alasan' => $request->input('post')['alasan']
                    ]);
                if ($query) {
                    $message = 'success update item : ' . $NO_STOCK;
                }
            }
            DB::commit();
            $data = [
                "result" => 'success',
                'message' => $message,
            ];
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Terjadi Error Server.';
            $data = [
                "result" => 'danger',
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            return $data;
        }
    }

    public function salesOrderCek(request $request)
    {
        try {
            $so = SalesOrder::where('NO_BUKTI', $request['post']['so_id'])
                ->count();
            $customer = SalesOrder::where('ID_CUST', $request['post']['customer'])
                ->where('PO_CUST', $request['post']['po'])
                ->get();
            $po = SalesOrder::where('PO_CUST', $request['post']['po'])
                ->get();
            $data = [
                "result" => true,
                'so' => $so,
                'customer' => $customer,
                'po' => $po,
            ];
            return $data;
        } catch (\Exception $e) {
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
}
