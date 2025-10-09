<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('profoma_receipt', function (Blueprint $table) {
            $table->decimal('security_deposit', 15, 2)->nullable()->after('duration');
            $table->decimal('water', 15, 2)->nullable()->after('security_deposit');
            $table->decimal('internet', 15, 2)->nullable()->after('water');
            $table->decimal('generator', 15, 2)->nullable()->after('internet');
            $table->text('other_charges_desc')->nullable()->after('generator');
            $table->decimal('other_charges_amount', 15, 2)->nullable()->after('other_charges_desc');
            $table->decimal('total', 15, 2)->nullable()->after('other_charges_amount');
        });
    }

    public function down()
    {
        Schema::table('profoma_receipt', function (Blueprint $table) {
            $table->dropColumn([
                'security_deposit',
                'water',
                'internet',
                'generator',
                'other_charges_desc',
                'other_charges_amount',
                'total',
            ]);
        });
    }
};
