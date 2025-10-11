<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeightLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // weight_logsテーブルの定義
        Schema::create('weight_logs', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')
                ->constrained('users') // usersテーブルを参照
                ->onDelete('cascade') // ユーザー削除時にログも削除
                ->comment('ユーザーID');
            $table->date('date')->comment('日付');
            // decimal(4, 1) は「合計4桁、小数点以下1桁」
            $table->decimal('weight', 4, 1)->comment('体重');
            $table->integer('calories')->nullable()->comment('食事量');
            $table->time('exercise_time')->nullable()->comment('運動時間');
            $table->text('exercise_content')->nullable()->comment('運動内容');
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
        Schema::dropIfExists('weight_logs');
    }
}
