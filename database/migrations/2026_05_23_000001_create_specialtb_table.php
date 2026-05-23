<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('specialtb', function (Blueprint $table) {
            $table->id();
            $table->string('specialization', 100)->unique();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('specialtb'); }
};
