<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeightTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // weight_targetテーブルの定義
        Schema::create('weight_target', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade') // ユーザーが削除されたら関連レコードも削除
                ->unique()             // ★ UNIQUE制約で「現在の目標」のみを保持
                ->comment('ユーザーID');
            // decimal(4, 1) 「合計4桁、小数点以下1桁」
            $table->decimal('target_weight', 4, 1)->comment('目標体重');
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
        Schema::dropIfExists('weight_target');
    }
}
