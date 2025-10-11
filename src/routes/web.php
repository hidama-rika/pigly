<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TargetRegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\WeightLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- 一時的なビュー確認用ルート (認証なし) ---
// /test/register/step1 にアクセスすると、
// resources/views/auth/register/step1.blade.php を表示します。
// Route::get('/auth/register/step1', function () {
//     return view('auth.register.step1');
// });
// Route::get('/auth/register/step2', function () {
//     return view('auth.register.step2');
// });
// Route::get('/auth/login', function () {
//     return view('auth.login');
// });
Route::get('/weight_logs/index', [WeightLogController::class, 'index']);
Route::get('/weight_logs/update', [WeightLogController::class, 'update']);
Route::get('/weight_logs/goal_setting', [WeightLogController::class, 'goal_setting']);


// ------------------------------------------


// 認証済みユーザー向けのルート (authミドルウェア)
Route::middleware('auth')->group(function () {

    // --- 目標体重登録関連 (目標が未設定の場合にアクセス可能) ---

    // STEP 2 目標体重登録フォームの表示
    Route::get('/register/step2', [TargetRegisterController::class, 'showTargetForm'])
        // 目標設定フォーム自体なので、'check.target.set' ミドルウェアは適用しない
        ->name('register.step2');

    // 目標体重データの送信と保存 (POST /register/target)
    Route::post('/register/target', [TargetRegisterController::class, 'saveTarget'])
        ->name('register.target.save');

    // --- 体重ログ関連 (目標設定チェック 'check.target.set' を適用) ---

    Route::middleware('check.target.set')->group(function () {

        // 1. ホーム (ルートパス / ) は、体重ログ一覧画面へリダイレクト
        Route::get('/', function () {
            return redirect()->route('weight_logs.index');
        });

        // 2. 体重ログ一覧 (メイン画面) - GET /weight_logs
        Route::get('/weight_logs', [WeightLogController::class, 'index'])
            ->name('weight_logs.index');

        // 3. 体重ログの保存 - POST /weight_logs
        Route::post('/weight_logs', [WeightLogController::class, 'store'])
            ->name('weight_logs.store');


        // 4. 目標体重の再設定ルート (リソース外のカスタムルート)
        Route::prefix('weight_logs')->group(function () {
            // 目標設定フォームの表示
            Route::get('/goal_setting', [WeightLogController::class, 'goalSetting'])
                ->name('weight_logs.goal_setting');

            // 目標設定の保存/更新
            Route::post('/goal_setting', [WeightLogController::class, 'storeTarget'])
                ->name('weight_logs.storeTarget');
        });

        // 5. 編集画面の表示 - GET /weight_logs/{id}/edit
        // 元のファイルではこのルート以降がミドルウェアの外に出ていましたが、修正しました。
        Route::get('/weight_logs/{id}/edit', [WeightLogController::class, 'edit'])->name('weight_logs.edit');

        // 6. 更新処理 - PUT/PATCH /weight_logs/{id}
        Route::put('/weight_logs/{id}', [WeightLogController::class, 'update'])->name('weight_logs.update');

        // 7. 削除処理 - DELETE /weight_logs/{id} ★★★ 追加しました ★★★
        Route::delete('/weight_logs/{id}', [WeightLogController::class, 'destroy'])->name('weight_logs.destroy');

    });
});