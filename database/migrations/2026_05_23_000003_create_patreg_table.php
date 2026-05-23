<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('patreg', function (Blueprint $table) {
            $table->id();
            $table->string('fname', 100);
            $table->string('lname', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('contact', 20);
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('patreg'); }
};
