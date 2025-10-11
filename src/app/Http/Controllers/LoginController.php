<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Authファサードを追加
// use Laravel\Fortify\Contracts\RegistersNewUsers; //  本来は使用が推奨されるがインターフェース読み込みエラー回避
use Illuminate\Routing\Controller; // 親クラスがControllerであることを明示
use App\Actions\Fortify\CreateNewUser; //  アクションクラスを直接インポート

class LoginController extends Controller
{
    // RegistersNewUsers $creator を削除し、CreateNewUserを直接使用
    public function register(Request $request)
    {
        // FortifyのCreateNewUserアクションを直接インスタンス化して呼び出す
        $creator = new CreateNewUser();

        // ユーザー作成（FortifyのCreateNewUserが実行される）
        $user = $creator->create($request->all());

        // ログイン
        Auth::login($user);

        // STEP 2 の目標体重登録画面へリダイレクト
        return redirect()->route('register.step2');
    }

    // ログイン画面表示 (routes/web.phpで参照されている)
    public function create()
    {
        return view('auth.login');
    }

    // ログイン処理 (routes/web.phpで参照されている)
    public function store(LoginRequest $request)
    {
        // nameフィールドは認証には不要なため、emailとpasswordのみをcredentialsとして使用します。
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // ログイン成功後、体重管理画面へリダイレクト
            return redirect()->intended(route('weight_logs.index'));
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->onlyInput('email');
    }

    // ログアウト処理 (routes/web.phpで参照されている)
    public function destroy(Request $request)
    {
        Auth::logout(); // Auth::guard('web')->logout(); から修正

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}


