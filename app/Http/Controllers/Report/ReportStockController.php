<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Master\Tmp_Postok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Tmp_PostokResult;

class ReportStockController extends Controller
{
    public function reportPosisiStock(request $request)
    {
        $edate = $request->input('edate');
        $qty = $request->input('qty');
        $isNilai = $request->input('isNilai');
        $lokasi = $request->input('lokasi');
        if ($lokasi == 'all') {
            $lokasi = '%';
        }
        $isGrouping = $request->input('isGrouping');
        $inventory = $request->input('inventory');
        if ($inventory == 'all') {
            $inventory = '%';
        }
        $merk = $request->input('merk');
        if ($merk == 'all') {
            $merk = '%';
        }
        $kategori = $request->input('kategori');
        if ($kategori == 'all') {
            $kategori = '%';
        }
        $subkategori = $request->input('subkategori');
        if ($subkategori == 'all') {
            $subkategori = '%';
        }

        if ($qty == '0') {
        }

        DB::select("CALL TF_STOCK('$lokasi', '$inventory', '$edate', '$isGrouping')");

        $model = new Tmp_PostokResult();
        $stok = Tmp_PostokResult::getPopulateStok();
        $stok->leftjoin('stock', '_postokresult.no_stock', 'stock.no_stock');
        
        $stok->where('stock.merk', 'LIKE', $merk);
        $stok->where('stock.kategori', 'LIKE', $kategori);
        $stok->where('stock.kategori2', 'LIKE', $subkategori);
        if ($qty == '1') {
            $stok->where('_postokresult.qty', '>', '0');
        } else if ($qty == '-1') {
            $stok->where('_postokresult.qty', '<', '0');
        } else if ($qty == '0') {
            $stok->where('_postokresult.qty', '=', '0');
        }

        $data = [
            'result' => true,
            'posisiStock' => $stok->get()
        ];

        return response()->json($data);
    }
}
