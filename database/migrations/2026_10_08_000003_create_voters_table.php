<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voters', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();   // NIK KTP 16 digit (DPT dari Excel)
            $table->string('name');                // Nama warga dari Excel
            $table->string('rt', 5)->default('006');
            $table->string('rw', 5)->default('001');
            $table->boolean('is_voted')->default(false);
            $table->foreignId('candidate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ticket_code')->nullable(); // kode tiket bukti memilih
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voters');
    }
};
