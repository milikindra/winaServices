<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Area;

class AreaController extends Controller
{
    public function areaGetRawData()
    {
        $model = Area::getAll();
        return response()->json($model);
    }
}
