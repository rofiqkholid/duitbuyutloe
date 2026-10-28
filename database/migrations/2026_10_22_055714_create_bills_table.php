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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->constrained('users')->onDelete('cascade'); // User yang bayar duluan/upload
            $table->string('title'); // e.g. "Tagihan Wifi Indihome Bulan Oktober"
            $table->string('category'); // wifi, listrik, air, galon, kebersihan, dll
            $table->decimal('total_amount', 12, 2); // e.g. 100000.00
            $table->text('description')->nullable();
            $table->string('invoice_image')->nullable(); // path upload foto/PDF faktur
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
