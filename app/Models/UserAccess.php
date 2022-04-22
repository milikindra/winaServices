<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use App\Models\UserGroup;
// use App\Models\DefaultApp;
use DB;

class UserAccess extends Model
{

    protected $table = 'wina_m_user_access';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    const CREATED_AT = 'dt_record';
    const UPDATED_AT = 'dt_modified';

    public function module_function()
    {
        return $this->belongsTo('App\Models\ModuleFunction', 'module_function_id', 'module_function_id');
    }

    // public static function deleteAllAccess($id){
    //     $model = self::where('user_id', $id)->delete();
    //     return $model;
    // }
    // public static function setUserAccessByUserGroup($description, $user_id, $warehouse){
    // 	$user_group = UserGroup::where('description',$description)->get();
    // 	foreach($user_group as $ug)
    // 	{
    // 		$model = new self();
    // 		$model->user_id = $user_id;
    // 		$model->warehouse_id = $warehouse;
    // 		$model->module_function_id = $ug->module_function_id;
    // 		$model->save();
    // 	}
    // 	return true;
    // }

    // public static function deleteAccess($module_function, $user_type)
    // {
    //     $model = DB::statement("exec sp_d_user_access @user_type = '$user_type', @module = '$module_function'");

    //     return $model;
    // }

    // public static function insertAccess($module_function, $user_type)
    // {
    //     $defaultWarehouse = DefaultApp::find("Default Warehouse")->value;
    //     $model = DB::statement("exec sp_i_user_access @warehouseD = '$defaultWarehouse', @user_type = '$user_type', @module = '$module_function'");

    //     return $model;
    // }
}
