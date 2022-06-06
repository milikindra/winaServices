<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BussinessUnit extends Model
{

    protected $table = 'masdivisi';
    protected $primaryKey = 'divisi';
    public $incrementing = false;
    const CREATED_AT = 'tglcreate';
    const UPDATED_AT = 'tgledit';

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
}
