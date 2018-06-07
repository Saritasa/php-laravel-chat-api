<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('notification_off')->default(0);
            $table->tinyInteger('is_read')->default(0);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('chat_id');
            $table->timestamps();

            $table->foreign(['user_id'])
                ->on(config('laravel_chat_api.usersTable'))
                ->references('id')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign(['chat_id'])->on('chats')->references('id')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
    }
}
