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
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');           // Jabatan: CEO, CTO, dll
            $table->string('department')->nullable(); // Departemen: Leadership, Management, Department
            $table->string('level')->default('department'); // leadership | management | department
            $table->string('photo')->nullable();    // path storage atau URL
            $table->string('email')->nullable();
            $table->string('linkedin')->nullable();
            $table->text('description')->nullable();
            $table->string('icon_type')->nullable(); // untuk department cards: code|content|marketing|support
            $table->integer('member_count')->default(0); // jumlah anggota tim (department)
            $table->integer('order')->default(0);   // urutan tampil
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
