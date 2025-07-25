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
            Schema::table('stores', function (Blueprint $table) {
        $table->boolean('has_paid')->default(false);
        $table->string('payment_intent_id')->nullable();
        // حقل is_approved موجود عندك بالفعل لذا لا تضيفه
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::table('stores', function (Blueprint $table) {
        $table->dropColumn(['has_paid', 'payment_intent_id']);
    });
    }
};
