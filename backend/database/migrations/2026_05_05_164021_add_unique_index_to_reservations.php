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
    public function up(): void {
        DB::statement("CREATE UNIQUE INDEX reservations_space_date_active ON reservations (parking_space_id, date) WHERE status = 'confirmed'");
        DB::statement("CREATE UNIQUE INDEX reservations_user_date_active ON reservations (user_id, date) WHERE status = 'confirmed'");
    }

    public function down(): void {
        DB::statement("DROP INDEX IF EXISTS reservations_space_date_active");
        DB::statement("DROP INDEX IF EXISTS reservations_user_date_active");
    }
};
