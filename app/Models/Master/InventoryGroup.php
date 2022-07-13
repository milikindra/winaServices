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
    // public static function updateData($request)
    // {
    //     $model = self::find($request->input('no_stock'));
    //     $model->editor = $request->input('creator');
    //     $model->no_stock = $request->input('no_stock');
    //     $model->nm_stock = $request->input('nm_stock');
    //     $model->sat = $request->input('sat');
    //     $model->minstock = $request->input('minstock');
    //     $model->kategori = $request->input('kategori');
    //     $model->kategori2 = $request->input('kategori2');
    //     $model->merk = $request->input('merk');
    //     $model->hrg_jual = $request->input('hrg_jual');
    //     $model->keterangan = $request->input('keterangan');
    //     $model->aktif = $request->input('aktif');
    //     $model->isKonsi = $request->input('isKonsi');
    //     $model->isMinus = $request->input('isMinus');
    //     $model->NO_REK1 = $request->input('NO_REK1');
    //     $model->NO_REK2 = $request->input('NO_REK2');
    //     $model->PPhPs23 = $request->input('PPhPs23');
    //     $model->PPhPs21 = $request->input('PPhPs21');
    //     $model->PPhPs4Ayat2 = $request->input('PPhPs4Ayat2');
    //     $model->PPhPs21OP = $request->input('PPhPs21OP');
    //     $model->VINTRASID = $request->input('VINTRASID');
    //     $model->save();
    //     return $model;
    // }

    // public static function deleteData($request)
    // {
    //     $model = self::where('no_stock', $request)->delete();
    //     return $model;
    // }
}
