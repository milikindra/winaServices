<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PnlProjectDef extends Model
{

    protected $table = 'trl_project_def';
    protected $primaryKey = null;
    const CREATED_AT = false;
    const UPDATED_AT = false;

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
}
