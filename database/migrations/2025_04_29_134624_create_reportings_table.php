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
        Schema::create('reportings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informer', 'reporting_informer')
            ->nullable()
            ->references('id')
            ->on('users')
            ->onUpdate('NO ACTION')
            ->onDelete('NO ACTION');
            $table->string('informer_name')->nullable()->comment('Nama pelapor');
            $table->foreignId('room_id')
            ->nullable()
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('set null'); 
            $table->foreignId('assign_to', 'reporting_assign')
            ->nullable()
            ->references('id')
            ->on('users')
            ->onUpdate('NO ACTION')
            ->onDelete('NO ACTION');
            $table->foreignId('condition_id')
            ->nullable()
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('set null');
            $table->text('description')->comment('Rincian laporan');
            $table->foreignId('status_id')
            ->nullable()
            ->constrained()
            ->onUpdate('NO ACTION')
            ->onDelete('NO ACTION');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportings');
    }
};
