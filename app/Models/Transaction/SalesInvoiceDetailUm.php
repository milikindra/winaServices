<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesInvoiceDetailUm extends Model
{

    protected $table = 'jual_det_um';
    protected $primaryKey = 'idxurut';
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
        $model->keterangan = $request['keterangan'];
        $model->nilai = $request['nilai'];
        $model->nourut = $request['nourut'];
        $model->TAX = $request['TAX'];
        $model->save();
        return $model;
    }
}
