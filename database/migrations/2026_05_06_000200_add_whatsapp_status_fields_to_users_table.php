<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_device_status')->nullable()->after('whatsapp_device_id');
            $table->string('whatsapp_device_jid')->nullable()->after('whatsapp_device_status');
            $table->timestamp('whatsapp_device_last_synced_at')->nullable()->after('whatsapp_device_jid');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_device_status',
                'whatsapp_device_jid',
                'whatsapp_device_last_synced_at',
            ]);
        });
    }
};
