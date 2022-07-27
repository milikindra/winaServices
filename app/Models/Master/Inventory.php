<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{

    protected $table = 'stock';
    protected $primaryKey = 'no_stock';
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

    public static function getPopulateInventory()
    {
        // $model = self::select('no_stock', DB::RAW('IF(LENGTH(nm_stock > 50),SUBSTRING(nm_stock,1,50),nm_stock) as nama_barang'), 'sat', 'sisa_qty as saldo', DB::RAW('"0" as booked'), DB::RAW('"0" as orders'), DB::RAW('"0" as transit'), 'kategori', 'kategori2');
        $model = self::select('no_stock', 'nm_stock as nama_barang', 'sat', 'sisa_qty as saldo', DB::RAW('"0" as booked'), DB::RAW('"0" as orders'), DB::RAW('"0" as transit'), 'kategori', 'kategori2', 'kodeBJ', 'VINTRASID', 'TAHUN', 'merk');
        return $model;
    }

    public static function addData($request)
    {
        $model = new self();
        $model->creator = $request->input('creator');
        $model->editor = $request->input('creator');
        $model->no_stock = $request->input('no_stock');
        $model->nm_stock = $request->input('nm_stock');
        $model->sat = $request->input('sat');
        $model->minstock = $request->input('minstock');
        $model->kategori = $request->input('kategori');
        $model->kategori2 = $request->input('kategori2');
        $model->merk = $request->input('merk');
        $model->hrg_jual = $request->input('hrg_jual');
        $model->keterangan = $request->input('keterangan');
        $model->aktif = $request->input('aktif');
        $model->isKonsi = $request->input('isKonsi');
        $model->isMinus = $request->input('isMinus');
        $model->NO_REK1 = $request->input('NO_REK1');
        $model->NO_REK2 = $request->input('NO_REK2');
        $model->PPhPs23 = $request->input('PPhPs23');
        $model->PPhPs21 = $request->input('PPhPs21');
        $model->PPhPs4Ayat2 = $request->input('PPhPs4Ayat2');
        $model->PPhPs21OP = $request->input('PPhPs21OP');
        $model->kodeBJ = $request->input('kodeBJ');
        $model->VINTRASID = $request->input('VINTRASID');
        $model->TAHUN = $request->input('TAHUN');
        $model->save();
        return $model;
    }
    public static function updateData($request)
    {
        $model = self::find($request->input('no_stock_old'));
        $model->editor = $request->input('creator');
        $model->no_stock = $request->input('no_stock');
        $model->nm_stock = $request->input('nm_stock');
        $model->sat = $request->input('sat');
        $model->minstock = $request->input('minstock');
        $model->kategori = $request->input('kategori');
        $model->kategori2 = $request->input('kategori2');
        $model->merk = $request->input('merk');
        $model->hrg_jual = $request->input('hrg_jual');
        $model->keterangan = $request->input('keterangan');
        $model->aktif = $request->input('aktif');
        $model->isKonsi = $request->input('isKonsi');
        $model->isMinus = $request->input('isMinus');
        $model->NO_REK1 = $request->input('NO_REK1');
        $model->NO_REK2 = $request->input('NO_REK2');
        $model->PPhPs23 = $request->input('PPhPs23');
        $model->PPhPs21 = $request->input('PPhPs21');
        $model->PPhPs4Ayat2 = $request->input('PPhPs4Ayat2');
        $model->PPhPs21OP = $request->input('PPhPs21OP');
        $model->kodeBJ = $request->input('kodeBJ');
        $model->VINTRASID = $request->input('VINTRASID');
        $model->TAHUN = $request->input('TAHUN');
        $model->save();
        return $model;
    }

    public static function deleteData($request)
    {
        $model = self::where('no_stock', $request)->delete();
        return $model;
    }
}
