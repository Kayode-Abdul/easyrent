<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('profoma_receipt', function (Blueprint $table) {
            $table->integer('duration')->nullable()->after('apartment_id');
        });
    }

    public function down()
    {
        Schema::table('profoma_receipt', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
