<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Tree\ModuleNode;
use App\Models\Finance\Tmp_OutstandingSalesInvoice;
use App\Models\Finance\Tmp_OutstandingPurchaseInvoice;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class StatementOfAccountController extends Controller
{
    public function getListCustomerSOA(request $request)
    {
        $model = new Tmp_OutstandingSalesInvoice();
        $fields = $model->getTableColumns();
        $edate = $request->input('edate');
        $customer = $request->input('customer');
        $so = $request->input('so');
        $sales = $request->input('sales');
        $overdue = $request->input('overdue');
        $isTotal = $request->input('isTotal');
        // DB::enableQueryLog();
        DB::select("CALL TF_OutstandingPiutang('$edate', '$customer', '$so', '$sales', '$overdue', '$isTotal')");
        // Log::debug(DB::getQueryLog());
        $model = new Tmp_OutstandingSalesInvoice();
        $soa = Tmp_OutstandingSalesInvoice::getPopulate();
        $data = [
            'result' => true,
            'soa' => $soa->get()
        ];

        return response()->json($data);
    }

    public function getListSupplierSOA(request $request)
    {
        $model = new Tmp_OutstandingSalesInvoice();
        $fields = $model->getTableColumns();
        $edate = $request->input('edate');
        $supplier = $request->input('supplier');
        $inventory = $request->input('inventory');
        $tag = $request->input('tag');
        $overdue = $request->input('overdue');
        $isTotal = $request->input('isTotal');
        // DB::enableQueryLog();
        DB::select("CALL TF_OutstandingHutang('$edate', '$supplier', '$inventory', '$tag', '$overdue', '$isTotal')");
        // Log::debug(DB::getQueryLog());
        $model = new Tmp_OutstandingPurchaseInvoice();
        $soa = Tmp_OutstandingPurchaseInvoice::getPopulate();
        $data = [
            'result' => true,
            'soa' => $soa->get()
        ];

        return response()->json($data);
    }
}
