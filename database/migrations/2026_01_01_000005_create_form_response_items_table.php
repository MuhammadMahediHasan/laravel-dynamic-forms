<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('df_response_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('df_response_id')->constrained('df_responses')->cascadeOnDelete();
            $table->foreignId('df_field_id')->constrained('df_fields')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('df_fields')->nullOnDelete();

            $table->json('label');
            $table->string('component');
            $table->json('options')->nullable();

            $table->json('value')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->integer('manual_mark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('df_response_items');
    }
};
