<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Tmp_PnlProjectList extends Model
{

    // protected $table = '_BBRLHasilSO';
    protected $table = 'wina_sv_pnlprojectlist';
    protected $primaryKey = null;
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

    // public static function getPopulate()
    // {
    //     $model = self::select(DB::RAW(' no_SO, tgl_SO, tgl_Last_DO, jenisSO, note_PH, nm_cust, Sales, no_po, Tag, REVENUE, COGS, 
	// 	InOrdered, StockInHand, ItemAdjustment, 
	// 	Gross_Profit, 
	// 	case when ifnull(REVENUE,0) <> 0 then round(Gross_Profit / REVENUE * 100,2) else 0 end as prosen1, 
	// 	Opr_Exp, Ass_Exp, OH_Exp, other_exp, Other_inc, 
	// 	Profit,
	// 	case when ifnull(REVENUE,0) <> 0 then round(Profit / REVENUE * 100,2) else 0 end as prosen2,
	// 	tgl_clear, tgl_create_cr, DATEDIFF(tgl_clear,tgl_SO) as umur'));
    //     return $model;
    // }
}
