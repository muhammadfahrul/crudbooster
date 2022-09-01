<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = \DB::table('cms_moduls')->where("name", "Users")->first();
        if (!$module) {
            \DB::table("cms_moduls")->insert(
                [
                    "name" => "Users",
                    "icon" => "fa fa-users",
                    "path" => "user",
                    "table_name" => "tb_users",
                    "connection" => "pgsql",
                    "controller" => "AdminUserController",
                    "is_protected" => false,
                    "is_active" => false,
                    "created_at" => date('Y-m-d H:i:s')
                ]
            );
        }

        $menu = \DB::table('cms_menus')->where("name", "Users")->first();
        if (!$menu) {
            \DB::table("cms_menus")->insert(
                [
                    "name"       => "Users",
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
        \DB::table('cms_menus')->where("name", "Users")->delete();
        \DB::table('cms_moduls')->where("name", "Users")->delete();
    }
}
