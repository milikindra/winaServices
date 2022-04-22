<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{

    protected $table = 'wina_m_module';
    protected $primaryKey = 'module_function_id';
    public $incrementing = false;
    const CREATED_AT = 'dt_record';
    const UPDATED_AT = 'dt_modified';

    public static function getMenu($request)
    {
        $model = self::whereNull('parent_id')->orderBy('module_sequence', 'asc')->get();
        return $model;
    }

    public static function getMenuList($user_id)
    {
        $menu = array();
        $userid = $user_id;
        $module = self::whereNull('parent_id')->orderBy('module_sequence', 'asc')->get();
        foreach ($module as $parent) {
            $childe = self::createMenuFromModule($parent->module_id, $parent->module_name, $userid);
            if (count($childe) > 0) {
                $template = array(
                    "children" => $childe,
                    "module_id" => $parent->module_id,
                    "module_name" => $parent->module_name,
                    "path" => $parent->module_name,
                    "icon_class" => $parent->icon_class
                );
                array_push($menu, $template);
            }
        }

        return $menu;
    }

    public static function createMenuFromModule($module, $path, $userid)
    {
        $modulSkrg = self::where('parent_id', $module)->orderBy('module_sequence', 'asc')->get();
        if (count($modulSkrg) == 0) { //kalo sdh sampe node paling bawah, sisa function
            $functionModule = ModuleFunction::join("wina_m_user_access_acc as mu", "wina_m_module_function_acc.module_function_id", "mu.module_function_id")
                ->where('user_id', $userid)
                ->where('module_id', $module)
                ->count();
            return $functionModule;
        }
        $tmpTree = array();
        foreach ($modulSkrg as $m) {
            $access = self::createMenuFromModule($m->module_id, $path . "." . $m->module_name, $userid);
            if (is_array($access) && count($access) > 0) { //bukan node terakhir
                $template = array(
                    "children" => $access,
                    "module_id" => $m->module_id,
                    "module_name" => $m->module_name,
                    "path" => $path . "." . $m->module_name,
                    "route" => $m->module_routes,
                    "end" => false,
                    "icon_class" => $m->icon_class
                );
                array_push($tmpTree, $template);
            } else if (!is_array($access) && $access != 0) { //node terakhir dan ada akses
                $template = array(
                    "children" => $access,
                    "module_id" => $m->module_id,
                    "module_name" => $m->module_name,
                    "path" => $path . "." . $m->module_name,
                    "route" => $m->module_routes,
                    "end" => true,
                    "icon_class" => $m->icon_class
                );
                array_push($tmpTree, $template);
            }
        }
        return $tmpTree;
    }
}
