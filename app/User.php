<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Laravel\Lumen\Auth\Authorizable;
use App\Models\UserAccess;
use App\Models\Module;
use Log;
use DB;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = 'wina_m_user';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    const CREATED_AT = 'dt_record';
    const UPDATED_AT = 'dt_modified';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'email',
        'name',
        'gender',
        'user_type',
        'join_date',
        'is_login',
        'status',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];
    public static function getLogin($email)
    {
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        $model = self::where('email', $email)->first();
        $model->user_access = UserAccess::where('user_id', $model->user_id)->get();
        $model->menu_name = Module::select('wina_m_module.module_name', 'wina_m_module.module_id', 'parent.module_name as parent_name')
            ->join('wina_m_module AS parent', 'parent.module_id', 'wina_m_module.parent_id')
            ->join('wina_m_module_function', 'wina_m_module.module_id', 'wina_m_module_function.module_id')
            ->join('wina_m_user_access', 'wina_m_module_function.module_function_id', 'wina_m_user_access.module_function_id')
            ->where('wina_m_user_access.user_id', $model->user_id)->distinct()->get()->keyBy('module_id');
        return $model;
    }
    public function user_access()
    {
        return $this->hasMany('App\Models\UserAccess', 'user_id', 'user_id');
    }

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public static function getSourceByUser($id)
    {
        $model = self::where('username', $id)->first();
        return $model;
    }

    public static function getByID($id)
    {
        $model = self::find($id);

        return $model;
    }

    public static function getByUsername($username)
    {
        $model = self::where('username', $username)->first();

        return $model;
    }

    public static function updatePassword($id, $password)
    {
        $model = self::find($id);
        $model->password = Hash::make($password);
        $model->save();

        return $model;
    }

    // public static function getPopulate()
    // {
    //     $model = self::select('m_user.email', 'm_user.username', 'm_user.user_id', 'm_user.status', 'm_user_type.user_type_name')
    //         ->join('m_user_type', 'm_user_type.user_type_id', 'm_user.user_type_id');
    //     return $model;
    // }

    public static function resetPassword($id, $password)
    {
        $model = self::find($id);
        $model->password = Hash::make($password);
        $model->save();

        return $model;
    }

    public static function randomString($length = 60)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }
}
