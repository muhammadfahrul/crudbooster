<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = \DB::table('cms_moduls')->where("name", "Merchant")->first();
        if (!$module) {
            \DB::table("cms_moduls")->insert(
                [
                    "name" => "Merchant",
                    "icon" => "fa fa-user-secret",
                    "path" => "merchant",
                    "table_name" => "tb_merchant",
                    "connection" => "pgsql",
                    "controller" => "AdminMerchantController",
                    "is_protected" => false,
                    "is_active" => false,
                    "created_at" => date('Y-m-d H:i:s')
                ]
            );
        }

        $menu = \DB::table('cms_menus')->where("name", "Merchant")->first();
        if (!$menu) {
            \DB::table("cms_menus")->insert(
                [
                    "name"       => "Merchant",
                    "type"    => "Module",
                    "path"    => "merchant",
                    "color"     => "normal",
                    "icon"     => "fa fa-user-secret",
                    "parent_id"      => 0,
                    "is_active"   => true,
                    "is_dashboard"       => false,
                    "id_cms_privileges"  => 1,
                    "sorting"     => 2,
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
        \DB::table('cms_menus')->where("name", "Merchant")->delete();
        \DB::table('cms_moduls')->where("name", "Merchant")->delete();
    }
}
