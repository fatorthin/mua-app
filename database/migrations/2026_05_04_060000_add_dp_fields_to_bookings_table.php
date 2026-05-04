<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_dp_paid')->default(false)->after('price');
            $table->decimal('dp_amount', 12, 2)->default(0)->after('is_dp_paid');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['is_dp_paid', 'dp_amount']);
        });
    }
};
