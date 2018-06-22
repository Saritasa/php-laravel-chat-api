<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatsTable extends Migration
{
    /**
     * Creates chats table with foreign key on application users table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('created_by');
            $table->tinyInteger('is_closed')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['created_by'])->on(config('laravel_chat_api.usersTable'))->references('id');
        });
    }

    /**
     * Drop chats table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
}
