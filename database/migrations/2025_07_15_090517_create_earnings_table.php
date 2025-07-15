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
    Schema::create('earnings', function (Blueprint $table) {
        $table->id();

        // نوع البائع: store أو user
        $table->enum('seller_type', ['store', 'user']);
        $table->unsignedBigInteger('seller_id');

        $table->unsignedBigInteger('buyer_id');
        $table->decimal('price', 10, 2);

        $table->decimal('commission', 10, 2);    // عمولة الموقع
        $table->decimal('seller_earning', 10, 2); // ربح البائع (المتبقي بعد العمولة)

        // مندوب التوصيل
        $table->unsignedBigInteger('delivery_agent_id')->nullable();
        $table->decimal('delivery_fee', 10, 2)->nullable();

        $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
        $table->timestamp('completed_at')->nullable();

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
