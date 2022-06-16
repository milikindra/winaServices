<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AccountGl extends Model
{

    protected $table = 'masbesar';
    protected $primaryKey = 'no_rek';
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

    public static function getPopulateAccount($norek, $sdate, $edate)
    {
        $model = self::select('tx.*', 'masbesar.curr', DB::raw('@saldo := @saldo + tx.debet - tx.kredit AS saldo'), DB::raw('@saldo_valas := @saldo_valas + tx.debet_us - tx.kredit_us AS saldo_valas'))
            ->from(DB::Raw('(
                SELECT
                    0 as idxurut,
                    "Saldo Awal" as trx,
                    glcard.no_rek,
                    glcard.nm_rek,
                    "" AS no_bukti,
                    CAST("' . $sdate . '" AS DATE) as tgl_bukti,
                    "" as no_SO,
                    "" as id_kyw,
                    "" as no_pajak,
                    "" as tag,
                    "Initial Balance" as uraian,
                   IF	(
                    sum( ifnull(glcard.debet,0) - ifnull(glcard.kredit,0) )+ ifnull(besarthbl.sawal,0) > 0,
                    sum( ifnull(glcard.debet,0) - ifnull(glcard.kredit,0) )+ ifnull(besarthbl.sawal,0),
                    0 
                    ) AS debet,
                    IF
                        (
                            sum( ifnull(glcard.debet_us,0) - ifnull(glcard.kredit_us,0) )+ ifnull(besarthbl.sawal_us,0) > 0,
                            sum( ifnull(glcard.debet_us,0) - ifnull(glcard.kredit_us,0) )+ ifnull(besarthbl.sawal_us,0),
                            0 
                        ) AS debet_us,
                    IF
                        (
                            sum( ifnull(glcard.debet,0) - ifnull(glcard.kredit,0) )+ ifnull(besarthbl.sawal,0) < 0,
                            sum( ifnull(glcard.debet,0) - ifnull(glcard.kredit,0) )+ ifnull(besarthbl.sawal,0),
                            0 
                        )*- 1 AS kredit,
                    IF
                        (
                            sum( ifnull(glcard.debet_us,0) - ifnull(glcard.kredit_us,0) )+ ifnull(besarthbl.sawal_us,0) < 0,
                            sum( ifnull(glcard.debet_us,0) - ifnull(glcard.kredit_us,0) )+ ifnull(besarthbl.sawal_us,0),
                            0 
                        )*- 1 AS kredit_us,
                "" AS dept 
                    
                FROM
                    glcard
                LEFT JOIN besarthbl ON glcard.no_rek = besarthbl.no_rek 
            
                WHERE
                glcard.no_rek = "' . $norek . '" AND
                glcard.tgl_bukti < "' . $sdate . '"
                    
                UNION ALL
                
                SELECT
                    idxurut,
                    trx,
                    no_rek,
                    nm_rek,
                    no_bukti,
                    tgl_bukti,
                    no_SO,
                    id_kyw,
                    no_pajak,
                    tag,
                    uraian,
                    COALESCE ( debet,0) AS debet,
                    COALESCE ( debet_us,0) AS debet_us,
                    COALESCE ( kredit,0) AS kredit,
                    COALESCE ( kredit_us,0) AS kredit_us,
                    dept 
                FROM
                    glcard 
                WHERE
                no_rek ="' . $norek . '" AND
                tgl_bukti BETWEEN "' . $sdate . '" AND "' . $edate . '"
                ORDER BY tgl_bukti ASC, debet DESC
                ) as tx'))
            ->leftjoin('masbesar', 'tx.no_rek', 'masbesar.NO_REK')
            ->join(DB::raw('(SELECT @saldo:=0) as sx'), DB::raw('"1"'), DB::raw('"1"'))
            ->join(DB::raw('(select @saldo_valas:=0) as rx'), DB::raw('"1"'), DB::raw('"1"'));
        return $model;
    }

    public static function populateRaw()
    {
        $model = self::select('*');
        return $model;
    }

}
