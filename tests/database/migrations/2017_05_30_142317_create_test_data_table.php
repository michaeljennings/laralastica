<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_data', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sort_order');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->boolean('active');
            $table->boolean('online');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_data');
    }
}
