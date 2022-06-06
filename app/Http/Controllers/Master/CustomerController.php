<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Customer;

class CustomerController extends Controller
{
    public function customerGetRawData(Request $request)
    {
        $model = Customer::getAll($request->field, $request->sort);
        return response()->json($model);
    }

    public function customerGetById(Request $request)
    {
        $model = Customer::getById();
        $model->leftJoin(DB::RAW('(SELECT DISTINCT curr, rate
		FROM masrate ORDER BY tanggal DESC) as rate_tmp'), 'mascustomer.curr', 'rate_tmp.curr');
        $model->where('mascustomer.ID_CUST', $request->id_cust);
        return $model->get();
    }
}
