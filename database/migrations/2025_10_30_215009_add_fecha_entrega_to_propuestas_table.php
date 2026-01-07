<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('propuestas', function (Blueprint $table) {
        $table->date('fecha_entrega_estimada')->nullable()->after('fecha');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('propuestas', function (Blueprint $table) {
        $table->dropColumn('fecha_entrega_estimada');
    });
}
};
