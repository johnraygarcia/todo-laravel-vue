<?php

use App\Models\User;
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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(false);
            $table->string('description');
            $table->foreignIdFor(User::class);
            $table->dateTime('created_date');
            $table->dateTime('due_date');
            $table->boolean('status');
            $table->smallInteger('priority');
            $table->boolean('is_archived');
            $table->smallInteger('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
