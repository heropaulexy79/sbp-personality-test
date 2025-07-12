<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->json('metadata')->nullable();
        });

        $defaultMetadata = json_encode([
            'tags' => [],
            'resources' => [],
        ]);

        // Wrap the default value in DB::raw to ensure it's treated as a literal JSON string
        // when inserting into the database, for older MySQL versions this is important.
        DB::table('courses')->update(['metadata' => DB::raw("'" . $defaultMetadata . "'")]);

        // If you want the column to be NOT NULL after filling defaults,
        // you would run another schema alteration here, but it's generally safer
        // to leave it nullable if you anticipate new records might temporarily
        // not have metadata set immediately.
        // If you truly need it NOT NULL, uncomment the following block,
        // but ensure all existing rows are correctly populated above.

        Schema::table('courses', function (Blueprint $table) {
            $table->json('metadata')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
