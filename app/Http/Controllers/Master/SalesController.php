<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Sales;

class SalesController extends Controller
{
    public function salesGetRawData(Request $request)
    {
        $model = Sales::getAll($request->field, $request->sort);
        return response()->json($model);
    }
}