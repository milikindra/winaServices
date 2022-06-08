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
}
