<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WeightLog;
use App\Models\WeightTarget;

class DatabaseSeeder extends Seeder
{
    /**
     * データベースをシード（データ投入）します。
     *
     * @return void
     */
    public function run()
    {
        // 既存のユーザーがいれば削除（クリーンなテストのため）
        // 環境によってはtruncateが使えないため、delete()を使用
        User::query()->delete();

        // 1. Userのダミーデータを1件作成
        // 開発用の初期ユーザーアカウントを作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            // パスワードはUserFactoryのデフォルト（通常は'password'）に従う
        ]);

        // 作成されたユーザーに対してデータ投入を行う
        if ($user) {
            // 2. WeightTargetのダミーデータを1件作成
            WeightTarget::factory()->create([
                'user_id' => $user->id,
            ]);

            // 3. WeightLogのダミーデータを35件作成
            // ユーザーに紐づく過去35日間のログデータを作成
            WeightLog::factory()->count(35)->create([
                'user_id' => $user->id,
            ]);

            // 投入が成功したことを確認するためのメッセージ
            echo "Seeding complete: User, 1 WeightTarget, and 35 WeightLogs created for User ID: {$user->id}\n";

        } else {
            // ユーザー作成に失敗した場合の警告
            echo "Error: Failed to create initial User.\n";
        }
    }
}
