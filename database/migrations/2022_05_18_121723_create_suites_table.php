<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repository_id')->constrained('repositories')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string("title");
            $table->integer("order")->nullable();
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
        Schema::dropIfExists('suites');
    }
}
