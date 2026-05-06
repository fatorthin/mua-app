<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('instagram')->nullable()->after('invoice_footer_notes');
            $table->string('tiktok')->nullable()->after('instagram');
            $table->string('whatsapp_device_id')->nullable()->after('tiktok');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['instagram', 'tiktok', 'whatsapp_device_id']);
        });
    }
};
