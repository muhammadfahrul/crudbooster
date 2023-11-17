<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = \DB::table('cms_moduls')->where("name", "User")->first();
        if (!$module) {
            \DB::table("cms_moduls")->insert(
                [
                    "name" => "User",
                    "icon" => "fa fa-users",
                    "path" => "user",
                    "table_name" => "tb_user",
                    "connection" => "pgsql",
                    "controller" => "AdminUserController",
                    "is_protected" => false,
                    "is_active" => false,
                    "created_at" => date('Y-m-d H:i:s')
                ]
            );
        }

        $menu = \DB::table('cms_menus')->where("name", "User")->first();
        if (!$menu) {
            \DB::table("cms_menus")->insert(
                [
                    "name"       => "User",
                    "type"    => "Module",
                    "path"    => "user",
                    "color"     => "normal",
                    "icon"     => "fa fa-users",
                    "parent_id"      => 0,
                    "is_active"   => true,
                    "is_dashboard"       => false,
                    "id_cms_privileges"  => 1,
                    "sorting"     => 5,
                    "created_at" => date('Y-m-d H:i:s')
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('cms_menus')->where("name", "User")->delete();
        \DB::table('cms_moduls')->where("name", "User")->delete();
    }
}
