<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAteccSerialAndPetNameToCmsTable extends Migration
{
    public function up()
    {
        Schema::table('cms', function (Blueprint $table) {
            $table->text('atecc_serial')->nullable();
            $table->text('pet_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cms', function (Blueprint $table) {
            $table->dropColumn('atecc_serial');
            $table->dropColumn('pet_name');
        });
    }
}
