<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up(): void
    {
        Schema::create('environments', function (Blueprint $table) {
            $table->id();
            $table->string('server_ip')->default('http://127.0.0.1:8080/api');
            $table->string('server_version')->default('v1');
            $table->string('default_currency')->default('USD');
            $table->string('enabled_payments')->default('CASH');
            $table->string('socket_ip')->default('127.0.0.1');
            $table->string('socket_port')->default('23001');
            $table->string('location')->default('Development Desk');
            $table->string('terminal')->default('ESADZA01');
            $table->string('printer')->default('Microsoft Print to PDF');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environments');
    }
};
