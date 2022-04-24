<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    protected $table = 'wina_m_user';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
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

    public static function getPopulateEmployee($status)
    {
        $model = self::select('wina_m_user.*', 'wina_m_global_param.name as religion')
            ->leftJoin('wina_m_global_param', 'wina_m_global_param.code', 'wina_m_user.religion_id')
            ->where('wina_m_user.status', $status);
        return $model;
    }
}
