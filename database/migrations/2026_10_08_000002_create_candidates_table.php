<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique(); // nomor urut
            $table->string('name');
            $table->text('vision')->nullable();
            $table->string('photo')->nullable();
            $table->string('color', 20)->default('#0ea5e9');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
