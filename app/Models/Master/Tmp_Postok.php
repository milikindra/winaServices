<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Tmp_Postok extends Model
{

    protected $table = '_postok';
    protected $primaryKey = 'no_bukti';
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
        // $model = self::select('*');
        $model = self::select('tx.*', DB::raw('@saldo := @saldo+qty as saldo'), DB::raw('@nilai_saldo := @nilai_saldo+jml_pok as nilai_saldo'))
            ->from(DB::Raw('(SELECT * FROM _postok) as tx'))
            ->join(DB::raw('(SELECT @saldo:=0) as sx'), DB::raw('"1"'), DB::raw('"1"'))
            ->join(DB::raw('(select @nilai_saldo:=0) as rx'), DB::raw('"1"'), DB::raw('"1"'));




        return $model;
    }
}
