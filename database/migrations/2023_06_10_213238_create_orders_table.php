<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('status',['Befor_Preparing','Preparing','Done'])->default('Befor_Preparing');
            $table->time('time')->nullable();//time_preparing
            $table->time('time_end')->nullable();//time_done
            $table->string('table_num')->default('sss');
            $table->double('total_price')->nullable();
            $table->boolean('is_paid')->default('0');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('orders');
    }
};



