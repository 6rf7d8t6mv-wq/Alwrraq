<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educational_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('official_id')->nullable();
            $table->string('ministerial_number')->nullable();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->enum('institution_type', ['school', 'university', 'college', 'institute']);
            $table->enum('education_stage', ['kindergarten', 'primary', 'intermediate', 'secondary', 'higher_education', 'training']);
            $table->enum('ownership_type', ['government', 'private', 'international']);
            $table->enum('gender_type', ['boys', 'girls', 'mixed'])->default('mixed');
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('source');
            $table->text('source_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->unique(['source', 'official_id'], 'educational_institutions_source_official_unique');
            $table->unique(['name_ar', 'city', 'institution_type'], 'educational_institutions_name_city_type_unique');
            $table->index(['institution_type', 'education_stage']);
            $table->index(['region', 'city']);
            $table->index('ministerial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educational_institutions');
    }
};
