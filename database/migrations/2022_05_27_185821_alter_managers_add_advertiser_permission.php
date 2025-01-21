<?php

use Illuminate\Database\Migrations\Migration;

class AlterManagersAddAdvertiserPermission extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement("ALTER TABLE `users_managers` CHANGE `advertisers` `advertisers` "
            . "SET ('List','Create','Update','Delete','Block','Activate','Send Email','Add Fund','Remove Fund','Login Behalf','Domains') "
            . "CHARSET ASCII COLLATE ascii_general_ci NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement("ALTER TABLE `users_managers` CHANGE `advertisers` `advertisers` "
            . "SET ('List','Create','Update','Delete','Block','Activate','Send Email','Add Fund','Remove Fund','Login Behalf') "
            . "CHARSET ASCII COLLATE ascii_general_ci NULL");
    }
}
