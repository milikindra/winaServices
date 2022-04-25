<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Employee;

class EmployeeController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $model = new Employee();
            $fields = $model->getTableColumns();

            $employee = Employee::getPopulateEmployee($request->input('void'));
            if ($request->has('search')) {
                $keyword = $request->input('search');
                if (!empty($keyword)) {
                    $employee->where(function ($query) use ($keyword, $fields) {
                        $query->orWhere('wina_m_user.employee_id', 'LIKE', "%$keyword%")
                            ->orWhere('wina_m_user.full_name', 'LIKE', "%$keyword%");
                    });
                }
            }

            $filteredData = $employee->get();
            $totalRows = $employee->count();

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
                    $employee->orderBy($column, $direction);
                }
            } else {
                $employee->orderBy('employee_id', 'asc');
            }

            if ($request->has('current_page')) {
                $page = $request->input('current_page');
                $limit = $employee->count();
                if ($request->has('per_page')) {
                    $limit = $request->input('per_page');
                }
                $offset = ($page - 1) * $limit;
                if ($offset < 0) {
                    $offset = 0;
                }

                $employee->skip($offset)->take($limit);
            }

            $data = [
                'result' => true,
                'total' => $totalRows,
                'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
                'recordsFiltered' => count($filteredData),
                'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
                'employee' => $employee->get()
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $message = 'Failed to fetch employee data.';
            Log::debug($request->path() . " | " . $message . " | " . print_r($_POST, TRUE));
            return response()->json([
                'result' => FALSE,
                'message' => $message
            ]);
        }
    }

    public function getMenuList(Request $request)
    {
        $menu = array();
        $userid = $request->get("user_id");
        $module = DB::select("call wina_sp_get_module_user ('$userid')");
        $module = collect($module)->keyBy('module_id')->toArray();
        $moduleParent = DB::select("call wina_sp_get_module_user_parent ('$userid')");

        // $max = 9999;
        $level = 0;
        $list = [];
        $menu = [];
        foreach ($moduleParent as $node) {
            $tree = new ModuleNode($node->module_id, $module, $level);
            $tree->addChildren($module, $node->module_id, $list);
            array_push($menu, $tree);
        }


        return json_encode($menu);
    }
}
