<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('appointmenttb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patreg')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctb')->cascadeOnDelete();
            $table->date('appdate');
            $table->time('apptime');
            $table->enum('status', ['Pending','Confirmed','Cancelled','Completed'])->default('Pending');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('appointmenttb'); }
};
