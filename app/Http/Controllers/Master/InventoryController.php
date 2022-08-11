<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Inventory;
use App\Models\Master\InventoryGroup;
use App\Models\Master\InventoryGroupDetail;
use App\Models\Master\Tmp_Postok;

class InventoryController extends Controller
{
    public function getList(Request $request)
    {
        $model = new Inventory();
        $fields = $model->getTableColumns();
        $void = $request->input('void');
        $kategori = $request->input('kategori');
        $subkategori = $request->input('subkategori');
        $um = $request->input('um');

        $inventory = Inventory::getPopulateInventory();
        if ($um != "Y") {
            $inventory->where('no_stock', 'NOT LIKE', '**%');
        }
        $inventory->where('no_stock', '<>', '0');

        if ($void == '1') {
            $inventory->where('sisa_qty', '>', '0');
        }

        if ($kategori != 'all') {
            $inventory->where('kategori', $kategori);
        }
        if ($subkategori != 'all') {
            $inventory->where('kategori2', $subkategori);
        }

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $inventory->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('no_stock', 'LIKE', "%$keyword%")
                        ->orWhere('nm_stock', 'LIKE', "%$keyword%")
                        ->orWhere('sat', 'LIKE', "%$keyword%")
                        ->orWhere('kategori', 'LIKE', "%$keyword%")
                        ->orWhere('kategori2', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $inventory->get();
        $totalRows = $inventory->count();

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
                $inventory->orderBy($column, $direction);
            }
        } else {
            $inventory->orderBy('no_stock', 'asc');
        }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $inventory->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $inventory->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'inventory' => $inventory->get()
        ];

        return response()->json($data);
    }

    public function inventoryAddSave(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Inventory::addData($request);
            if ($request->input('kodeBJ') == 'G') {
                $model = InventoryGroup::addData($request);
                for ($i = 0; $i < count($request->input('no_stockGroup')); $i++) {
                    $send = [
                        'no_stock' => $request->input('no_stock'),
                        'no_stockGroup' => $request->input('no_stockGroup')[$i],
                        'nm_stockGroup' => $request->input('nm_stockGroup')[$i],
                        'qtyGroup' => $request->input('qtyGroup')[$i],
                        'satGroup' => $request->input('satGroup')[$i]
                    ];
                    $model = InventoryGroupDetail::addData($send);
                }
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
            $message = 'Something went wrong.';
            $data = [
                "result" => false,
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            Log::debug($e);
            return $data;
        }
    }

    public function inventoryEdit(Request $request)
    {
        try {
            $model = Inventory::find($request->no_stock);
            $model->get();
            $child = InventoryGroupDetail::where('NO_BUKTI', $request->no_stock)->get();
            $data = [
                "result" => true,
                'inv' => $model,
                'child' => $child
            ];
            return $data;
        } catch (\Exception $e) {
            $message = 'Terjadi Error Server.';
            $data = [
                "result" => false,
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            return $data;
        }
    }

    public function inventoryUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Inventory::updateData($request);
            if ($request->input('kodeBJ') == 'G') {
                $model = InventoryGroup::updateData($request);
                $model = InventoryGroupDetail::deleteData($request->input('no_stock_old'));
                for ($i = 0; $i < count($request->input('no_stockGroup')); $i++) {
                    $send = [
                        'no_stock' => $request->input('no_stock'),
                        'no_stockGroup' => $request->input('no_stockGroup')[$i],
                        'nm_stockGroup' => $request->input('nm_stockGroup')[$i],
                        'qtyGroup' => $request->input('qtyGroup')[$i],
                        'satGroup' => $request->input('satGroup')[$i]
                    ];
                    $model = InventoryGroupDetail::addData($send);
                }
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

    public function inventoryDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            // DB::enableQueryLog();
            $model = Inventory::deleteData($request->no_stock);
            $model = InventoryGroup::deleteData($request->no_stock);
            $model = InventoryGroupDetail::deleteData($request->no_stock);
            DB::commit();
            $message = 'Succesfully save data.';
            $data = [
                "result" => true,
                'message' => $message,
            ];
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Something wrong.';
            $data = [
                "result" => false,
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            return $data;
        }
    }

    public function kartuStokGetList(Request $request)
    {
        $kode = $request->input('kode');
        $sdate = $request->input('sdate');
        $edate = $request->input('edate');
        $lokasi = $request->input('lokasi');
        $item_transfer = $request->input('item_transfer');
        if ($lokasi == 'all') {
            $lokasi = '%';
        }
        DB::select("CALL TF_STOCK('$lokasi', '$kode', '$edate', '$item_transfer')");
        DB::select("CALL wina_tf_stock()");
        $model = new Tmp_Postok();
        $fields = $model->getTableColumns();
        $stok = Tmp_Postok::getPopulateStok($sdate, $edate);
        if ($item_transfer != "Y") {
            $stok->orWhere('tx.trx', 'Awal');
            $stok->orWhere('tx.trx', 'SJ');
            $stok->orWhere('tx.trx', 'RI');
            $stok->orWhere('tx.trx', 'RJ');
            $stok->orWhere('tx.trx', 'RB');
            $stok->orWhere('tx.trx', 'SI');
            $stok->orwhere('tx.trx', 'PI');
            $stok->orWhere('tx.trx', 'WIP');
            $stok->orwhere(function ($query) {
                $query->Where('tx.trx', 'PK');
                $query->WhereNull('pakai_head.no_so');
            });
        }
        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $stok->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('no_bukti', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $stok->get();
        $totalRows = $stok->count();

        $stok->orderBy('tgl_bukti', 'asc');
        if ($request->has('sort')) {
            if (!is_array($request->input('sort'))) {
                $message = "Invalid array for parameter sort";
                $data = [
                    'result' => FALSE,
                    'message' => $message
                ];
                return response()->json($data);
            }

            foreach ($request->input('sort') as $key => $sort) {
                $column = $sort['column'];
                $direction = $sort['dir'];
                $stok->orderBy($column, $direction);
            }
        } else {
            $stok->orderBy('tgl_bukti', 'asc');
        }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $stok->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($offset > 0) {
                $stok->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'kartuStok' => $stok->get()
        ];

        return response()->json($data);
    }

    public function inventoryGetRawData()
    {
        $model = Inventory::getAll();
        return response()->json($model);
    }

    public function inventoryChildGetByHead(Request $request)
    {
        try {
            $child = Inventory::select('stock.no_stock', 'stock.nm_Stock as nama_barang', 'grouping_det.sat', 'grouping_det.qty as saldo')
                ->leftJoin('grouping_det', 'grouping_det.NO_STOCK', 'stock.no_stock')
                ->where('grouping_det.NO_BUKTI', $request->NO_BUKTI)
                ->get();
            $data = [
                "result" => true,
                'child' => $child
            ];
            return $data;
        } catch (\Exception $e) {
            $message = 'Terjadi Error Server.';
            $data = [
                "result" => false,
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            return $data;
        }
    }
}
