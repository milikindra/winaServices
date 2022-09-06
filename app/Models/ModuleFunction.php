<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleFunction extends Model
{

    protected $table = 'wina_m_module_function';
    protected $primaryKey = 'module_function_id';
    public $incrementing = false;
    const CREATED_AT = 'dt_record';
    const UPDATED_AT = 'dt_modified';

    public function module()
    {
        return $this->belongsTo('App\Models\Module', 'module_id', 'module_id');
    }
}
