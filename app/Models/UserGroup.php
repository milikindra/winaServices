<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Models\ModuleFunction;

class UserGroup extends Model
{

    protected $table = 'wina_m_user_group';
    protected $primaryKey = ['description', 'module_function_id'];
    public $incrementing = false;
    const CREATED_AT = 'dt_record';
    const UPDATED_AT = 'dt_modified';

    public static function insertAllAccess($description)
    {
        $module_function = ModuleFunction::get();
        foreach ($module_function as $mf) {
            $model = new self();
            $model->description = $description;
            $model->module_function_id = $mf->module_function_id;
            $model->user_record = "engine";
            $model->user_modified = "engine";
            $model->save();
        }
        return true;
    }
}
