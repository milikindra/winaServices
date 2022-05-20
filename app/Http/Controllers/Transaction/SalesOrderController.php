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

        if ($fdate == "Y") {
            if ($sdate == null) {
                $sdate = Carbon::parse($request->sdate)->format('Y-m-d');
            }

            if ($edate == null) {
                $edate = Carbon::parse($request->edate)->format('Y-m-d');
            }
            $so->whereBetween('TGL_BUKTI', [$sdate, $edate]);
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

    // public function inventoryAddSave(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $model = Inventory::addData($request);
    //         DB::commit();
    //         $message = 'Succesfully save data.';
    //         $data = [
    //             "result" => true,
    //             'message' => $message,
    //             "data" => $model
    //         ];
    //         return $data;
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         $message = 'Terjadi Error Server.';
    //         $data = [
    //             "result" => false,
    //             'message' => $message
    //         ];
    //         Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
    //         return $data;
    //     }
    // }

    // public function inventoryEdit(Request $request)
    // {
    //     try {
    //         $model = Inventory::find($request->no_stock);
    //         $model->get();
    //         $data = [
    //             "result" => true,
    //             'inv' => $model,
    //         ];
    //         return $data;
    //     } catch (\Exception $e) {
    //         $message = 'Terjadi Error Server.';
    //         $data = [
    //             "result" => false,
    //         ];
    //         Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
    //         return $data;
    //     }
    // }

    // public function inventoryUpdate(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $model = Inventory::updateData($request);
    //         DB::commit();
    //         $message = 'Succesfully save data.';
    //         $data = [
    //             "result" => true,
    //             'message' => $message,
    //             "data" => $model
    //         ];
    //         return $data;
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         $message = 'Terjadi Error Server.';
    //         $data = [
    //             "result" => false,
    //             'message' => $message
    //         ];
    //         Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
    //         return $data;
    //     }
    // }

    // public function inventoryDelete(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         DB::enableQueryLog();
    //         $model = Inventory::deleteData($request->no_stock);
    //         DB::commit();
    //         $message = 'Succesfully save data.';
    //         $data = [
    //             "result" => true,
    //             'message' => $message,
    //         ];
    //         return $data;
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         $message = 'Terjadi Error Server.';
    //         $data = [
    //             "result" => false,
    //             'message' => $message
    //         ];
    //         Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
    //         return $data;
    //     }
    // }

    // public function kartuStokGetList(Request $request)
    // {
    //     $kode = $request->input('kode');
    //     $sdate = $request->input('sdate');
    //     $edate = $request->input('edate');
    //     $lokasi = $request->input('lokasi');
    //     $item_transfer = $request->input('item_transfer');
    //     if ($lokasi == 'all') {
    //         $lokasi = '%';
    //     }
    //     DB::select("CALL TF_STOCK('$lokasi', '$kode', '$edate', '$item_transfer')");
    //     DB::select("CALL wina_tf_stock()");
    //     $model = new Tmp_Postok();
    //     $fields = $model->getTableColumns();
    //     $stok = Tmp_Postok::getPopulateStok($sdate, $edate);
    //     if ($item_transfer != "Y") {
    //         $stok->orWhere('tx.trx', 'Awal');
    //         $stok->orwhere('tx.trx', 'PI');
    //         $stok->orWhere('tx.trx', 'WIP');
    //         $stok->orwhere(function ($query) {
    //             $query->Where('tx.trx', 'PK');
    //             $query->WhereNull('pakai_head.no_so');
    //         });
    //     }
    //     if ($request->has('search')) {
    //         $keyword = $request->input('search');
    //         if (!empty($keyword)) {
    //             $stok->where(function ($query) use ($keyword, $fields) {
    //                 $query->orWhere('no_bukti', 'LIKE', "%$keyword%");
    //             });
    //         }
    //     }

    //     $filteredData = $stok->get();
    //     $totalRows = $stok->count();

    //     $stok->orderBy('tgl_bukti', 'asc');
    //     if ($request->has('sort')) {
    //         if (!is_array($request->input('sort'))) {
    //             $message = "Invalid array for parameter sort";
    //             $data = [
    //                 'result' => FALSE,
    //                 'message' => $message
    //             ];
    //             return response()->json($data);
    //         }

    //         foreach ($request->input('sort') as $key => $sort) {
    //             $column = $sort['column'];
    //             $direction = $sort['dir'];
    //             $stok->orderBy($column, $direction);
    //         }
    //     } else {
    //         $stok->orderBy('tgl_bukti', 'asc');
    //     }

    //     if ($request->has('current_page')) {
    //         $page = $request->input('current_page');
    //         $limit = $stok->count();
    //         if ($request->has('per_page')) {
    //             $limit = $request->input('per_page');
    //         }
    //         $offset = ($page - 1) * $limit;
    //         if ($offset > 0) {
    //             $stok->skip($offset)->take($limit);
    //         }
    //     }

    //     $data = [
    //         'result' => true,
    //         'total' => $totalRows,
    //         'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
    //         'recordsFiltered' => count($filteredData),
    //         'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
    //         'kartuStok' => $stok->get()
    //     ];

    //     return response()->json($data);
    // }
}
