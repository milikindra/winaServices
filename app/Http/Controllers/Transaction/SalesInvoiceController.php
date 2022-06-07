<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use App\Tree\ModuleNode;
use App\Models\Transaction\SalesOrder;
use App\Models\Transaction\SalesInvoice;

class SalesInvoiceController extends Controller
{

    public function siGetEfaktur(Request $request)
    {
        $no_bukti2 = str_replace(":", "/", $request->no_bukti2);
        $model = SalesInvoice::getSiEfaktur()->where('jual_head.no_bukti2', $no_bukti2);

        $modelGet = $model->get()->toArray();

        $no_so = $modelGet[0]['no_so'];
        if (empty($modelGet[0]['no_so'])) {
            $no_so = $modelGet[0]['no_so_um'];
        }

        $modelSo = SalesOrder::getById()->where('kontrak_head.NO_BUKTI', $no_so)->get();
        $data = [
            'si' => $modelGet,
            'so' => $modelSo
        ];


        // dd($model);

        // Log::info($modelSo);
        // ->latest('nourut')
        // ->first();
        return response()->json($data);
    }
}
