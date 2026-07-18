<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('df_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('df_form_id')->constrained('df_forms')->cascadeOnDelete();
            $table->foreignId('df_form_input_id')->constrained('df_form_inputs')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('df_fields')->nullOnDelete();

            $table->json('label');
            $table->json('placeholder')->nullable();
            $table->json('hints')->nullable();
            $table->string('icon')->nullable();
            $table->json('options')->nullable();

            $table->boolean('required')->default(false);
            $table->json('correct_answer')->nullable();
            $table->integer('marks')->default(1);

            $table->foreignId('condition_input_id')->nullable()->constrained('df_fields')->nullOnDelete();
            $table->json('condition_value')->nullable();
            $table->boolean('is_repeatable')->default(false);
            $table->integer('repeat_min')->default(1);
            $table->integer('repeat_max')->nullable();

            $table->integer('sort')->default(0);
            $table->index(['df_form_id', 'sort']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('df_fields');
    }
};
