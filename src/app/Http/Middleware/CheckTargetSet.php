<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WeightTarget;
use Symfony\Component\HttpFoundation\Response;

class CheckTargetSet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // ユーザーが認証されていない場合は、次の処理に進む (authミドルウェアが別途処理するため)
        if (!Auth::check()) {
            return $next($request);
        }

        $userId = Auth::id();

        // 目標体重が既に設定されているか確認
        $targetExists = WeightTarget::where('user_id', $userId)->exists();

        // 現在アクセス中のルート名を取得
        $currentRouteName = $request->route()->getName();

        // 目標体重が設定されていない、かつ、現在のルートがSTEP 2ではない場合
        if (!$targetExists && $request->route()->getName() !== 'register.target.form') {

            // STEP 2 登録フォームへ強制リダイレクト
            return redirect()->route('register.target.form')->with('status', '目標体重を先に設定してください。');
        }

        // それ以外の場合（目標設定済み、または現在STEP 2の画面にいる場合）は、リクエストを続行
        return $next($request);
    }
}
