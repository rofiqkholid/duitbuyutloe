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
        Schema::create('bill_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // user yang kena bagian
            $table->decimal('amount', 12, 2); // nominal bagian (e.g. 20000.00)
            $table->enum('status', ['unpaid', 'paid', 'approved'])->default('unpaid'); 
            // unpaid: belum bayar
            // paid: sudah bayar / klaim bayar
            // approved: sudah dicek/disetujui oleh penerima (payer_id)
            $table->string('proof_image')->nullable(); // bukti transfer balik
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_shares');
    }
};
