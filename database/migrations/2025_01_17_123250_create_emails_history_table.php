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
        Schema::create('emails_history', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('title');
            $table->text('body');
            $table->text('job_name');
            $table->timestamp('time')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails_history');
    }
};
