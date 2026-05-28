<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tournament_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('player'); // player|admin|owner
            $table->timestamps();
            $table->unique(['user_id', 'tournament_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('tournament_memberships'); }
};
