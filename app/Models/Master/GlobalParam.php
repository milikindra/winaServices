<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GlobalParam extends Model
{

    protected $table = 'wina_m_global_param';
    protected $primaryKey = 'internal_id';
    public $incrementing = true;
    const CREATED_AT = 'dt_record';
    const UPDATED_AT = 'dt_modified';

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'user_record');
    }

    public static function getAll()
    {
        $model = self::get();
        return $model;
    }

    public static function getPopulate()
    {
        $model = self::select('*');
        return $model;
    }

    public static function getTopManagement()
    {
    }
}
