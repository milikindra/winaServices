<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InventoryGroup extends Model
{

    protected $table = 'grouping_head';
    protected $primaryKey = 'no_bukti';
    public $incrementing = false;
    const CREATED_AT = 'TGLCREATE';
    const UPDATED_AT = 'TGLEDIT';

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

    public static function addData($request)
    {
        $model = new self();
        $model->creator = $request->input('creator');
        $model->NO_BUKTI = $request->input('no_stock');
        $model->TGL_BUKTI = date('Y-m-d');
        $model->KETERANGAN = $request->input('nm_stock');
        $model->ismuncul = 0;
        $model->sat = $request->input('sat');
        $model->kategori = $request->input('kategori');
        $model->merk = $request->input('merk');
        $model->kategori2 = $request->input('kategori2');
        $model->save();
        return $model;
    }
    public static function updateData($request)
    {
        $model = self::find($request->input('no_stock_old'));
        $model->editor = $request->input('creator');
        $model->NO_BUKTI = $request->input('no_stock');
        $model->TGL_BUKTI = date('Y-m-d');
        $model->KETERANGAN = $request->input('nm_stock');
        $model->ismuncul = 0;
        $model->sat = $request->input('sat');
        $model->kategori = $request->input('kategori');
        $model->merk = $request->input('merk');
        $model->kategori2 = $request->input('kategori2');
        $model->save();
        return $model;
    }

    public static function deleteData($request)
    {
        $model = self::where('NO_BUKTI', $request)->delete();
        return $model;
    }
}
