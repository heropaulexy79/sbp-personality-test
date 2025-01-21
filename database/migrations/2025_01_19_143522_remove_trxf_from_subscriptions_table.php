<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
            $table->dropColumn('transaction_ref');
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropColumn('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
            $table->dropUnique(['transaction_ref']);
            $table->string('transaction_ref')->nullable();
            $table->string('provider')->default('PAYSTACK');
        });
    }
};
