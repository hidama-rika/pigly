<?php

namespace App\Http\Controllers;

use App\Models\WeightTarget;
use App\Models\WeightLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\TargetRegisterRequest;

class TargetRegisterController extends Controller
{
    // STEP 2 (目標体重登録フォーム) を表示します。
    public function showTargetForm()
    {
        // ユーザーが認証済みであることを確認
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 目標設定済みチェックロジックはapp/Http/Middleware/CheckTargetSet.php
        // 目標体重が未設定の場合のみ、STEP 2ビューを表示
        return view('auth.register.step2');
    }

    // STEP 2 のデータを受け取り、目標体重と初期体重ログを保存します。
    public function saveTarget(TargetRegisterRequest $request)
    {
        // ログイン中のユーザーIDを取得
        $userId = Auth::id();

        // トランザクションを開始し、どちらかの保存が失敗したらロールバックするように設定
        DB::beginTransaction();

        try {
            // 1. 目標体重 (WeightTarget) の保存
            // updateOrCreateで重複作成を防ぐ
            WeightTarget::updateOrCreate(
                ['user_id' => $userId], // 検索条件: ユーザーIDが一致するレコードを探す
                [
                    'target_weight' => $request->target_weight, // 登録または更新する値
                ]
            );

            // 2. 初期体重ログ (WeightLog) の保存
            // 初期ログの日付は、登録が完了した今日の日付 (現在時刻) とします。
            WeightLog::create([
                'user_id' => $userId,
                'date' => now()->toDateString(), // 今日の日付を YYYY-MM-DD 形式で保存
                'weight' => $request->weight,
                'calories' => null, // STEP 2では入力しないためnullを許容
                'exercise_time' => null, // STEP 2では入力しないためnullを許容
                'exercise_content' => null, // STEP 2では入力しないためnullを許容
            ]);

            DB::commit();

            // 登録フロー完了後、ダッシュボード（ルート名 'weight_logs.index'）へリダイレクト
            // routes/web.php の定義に基づき 'weight_logs.index' ルートを使用します
            return redirect()->route('weight_logs.index')->with('success', '目標設定が完了しました！早速記録を始めましょう！');

        } catch (\Exception $e) {
            DB::rollBack();
            // ログに出力し、ユーザーを前の画面に戻してエラーメッセージを表示
            logger()->error('目標体重・初期体重の保存に失敗しました。', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            // エラーをセッションにフラッシュして、STEP 2 フォームに戻す
            return back()->withErrors(['save_error' => 'データの保存中にエラーが発生しました。再度お試しください。'])
                        ->withInput();
        }
    }
}
