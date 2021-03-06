<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatMessagesTable extends Migration
{
    /**
     * Create chat messages table with foreign key on application users table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message');
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
     * Drop chat messages table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
}
