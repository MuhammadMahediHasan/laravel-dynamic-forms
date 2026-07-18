<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('df_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('df_form_id')->constrained('df_forms')->cascadeOnDelete();

            $table->nullableMorphs('respondent');
            $table->nullableMorphs('subject');

            $table->date('date');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lon', 11, 8)->nullable();
            $table->json('meta_data')->nullable();

            $table->string('status')->default('Pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('df_responses');
    }
};
