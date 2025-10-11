<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fortifyの登録処理で使用する「users」テーブルを定義します。
        // カラム定義は、共有いただいた画像（id, name, email, password, timestamps）に基づいています。
        Schema::create('users', function (Blueprint $table) {

            $table->id();
            $table->string('name', 255)->comment('お名前');
            $table->string('email', 255)->unique()->comment('メールアドレス');
            $table->string('password', 255)->comment('パスワード (ハッシュ値)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
