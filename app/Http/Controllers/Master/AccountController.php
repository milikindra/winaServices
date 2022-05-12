<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\accountGl;

class AccountController extends Controller
{
    public function accountGetRawData()
    {
        $model = accountGl::getAll();
        return response()->json($model);
    }
}
