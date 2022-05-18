<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Tmp_PostokResult extends Model
{

    protected $table = '_postokresult';
    protected $primaryKey = 'no_stock';
    public $timestamps = false;
    public $incrementing = false;


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

    public static function getPopulateStok()
    {
        $model = self::select('_postokresult.no_stock', DB::RAW('IF(LENGTH(stock.nm_stock > 50),SUBSTRING(stock.nm_stock,1,50),stock.nm_stock) as nm_stock'), 'stock.sat', '_postokresult.qty', '_postokresult.jml_pok',  DB::RAW('(_postokresult.jml_pok/_postokresult.qty) as rata'));

        return $model;
    }
}
