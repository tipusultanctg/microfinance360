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
        Schema::table('members', function (Blueprint $table) {
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'widowed', 'divorced'])->nullable();
            $table->string('spouse')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('workplace')->nullable();
            $table->string('occupation')->nullable();
            $table->string('religion')->nullable();
            $table->date('registration_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'father_name',
                'mother_name',
                'gender',
                'marital_status',
                'spouse',
                'present_address',
                'permanent_address',
                'workplace',
                'occupation',
                'religion',
                'registration_date',
            ]);
        });
    }
};
