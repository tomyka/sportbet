<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->smallInteger('ord')->default(0);
            $table->string('type'); // group_stage|round_robin|knockout
            $table->json('config')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('locks_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stages'); }
};
