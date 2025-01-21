<?php

use Illuminate\Database\Migrations\Migration;

class AlterManagersPermissionsAddNewPermissions extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement("ALTER TABLE `users_managers` "
            . "CHANGE `publishers` `publishers` SET ('List','Create','Update','Delete','Block','Activate','Send Email',"
            . "'Add Fund','Remove Fund','Login Behalf','Domains','Places','Withdrawal Requests') CHARSET ASCII COLLATE ascii_general_ci NULL,"
            . "ADD COLUMN `send_email` SET ('Create','Update','Delete','Send') NULL");

        DB::statement("UPDATE `users_managers` SET send_email='Send' WHERE send_mass_email=1");

        DB::statement("ALTER TABLE `users_managers` DROP COLUMN `send_mass_email`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::statement("ALTER TABLE `users_managers` "
            . "CHANGE `publishers` `publishers` SET ('List','Create','Update','Delete','Block','Activate','Send Email',"
            . "'Add Fund','Remove Fund','Login Behalf','Domains','Places') CHARSET ASCII COLLATE ascii_general_ci NULL");
    }
}
