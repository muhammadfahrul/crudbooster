<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantGroupModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = \DB::table('cms_moduls')->where("name", "Merchant Group")->first();
        if (!$module) {
            \DB::table("cms_moduls")->insert(
                [
                    "name" => "Merchant Group",
                    "icon" => "fa fa-group",
                    "path" => "merchant_group",
                    "table_name" => "tb_merchant_group",
                    "connection" => "pgsql",
                    "controller" => "AdminMerchantGroupController",
                    "is_protected" => false,
                    "is_active" => false,
                    "created_at" => date('Y-m-d H:i:s')
                ]
            );
        }

        $menu = \DB::table('cms_menus')->where("name", "Merchant Group")->first();
        if (!$menu) {
            \DB::table("cms_menus")->insert(
                [
                    "name"       => "Merchant Group",
                    "type"    => "Module",
                    "path"    => "merchant_group",
                    "color"     => "normal",
                    "icon"     => "fa fa-group",
                    "parent_id"      => 0,
                    "is_active"   => true,
                    "is_dashboard"       => false,
                    "id_cms_privileges"  => 1,
                    "sorting"     => 1,
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
        \DB::table('cms_menus')->where("name", "Merchant Group")->delete();
        \DB::table('cms_moduls')->where("name", "Merchant Group")->delete();
    }
}
