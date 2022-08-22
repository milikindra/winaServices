<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesInvoiceDetail extends Model
{

    protected $table = 'jual_det';
    protected $primaryKey = 'IDXURUT';
    public $incrementing = false;
    const CREATED_AT = 'URUT';
    const UPDATED_AT = NULL;

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
        $model->NO_BUKTI = $request['NO_BUKTI'];
        $model->NO_STOCK = $request['NO_STOCK'];
        $model->NM_STOCK = $request['NM_STOCK'];
        $model->QTY = $request['QTY'];
        $model->SAT = $request['SAT'];
        $model->HARGA = $request['HARGA'];
        $model->DISC1 = $request['DISC1'];
        $model->DISC2 = $request['DISC2'];
        $model->DISC3 = $request['DISC3'];
        $model->DISCRP = $request['DISCRP'];
        $model->discrp2 = $request['discrp2'];
        $model->KET = $request['KET'];
        $model->id_lokasi = $request['id_lokasi'];
        $model->tax = $request['tax'];
        $model->kode_group = $request['kode_group'];
        $model->no_sj = $request['no_sj'];
        $model->save();
        return $model;
    }
}
