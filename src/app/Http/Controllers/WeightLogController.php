<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeightLog;
use App\Models\WeightTarget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\GoalSettingRequest;
use App\Http\Requests\WeightLogRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WeightLogController extends Controller
{
    /**
     * コントローラのインスタンスを生成し、ミドルウェアを適用
     */
    public function __construct()
    {
        // 全てのアクションに認証ミドルウェアを適用
        $this->middleware('auth');

        // index, create, editなど（ログのデータ操作画面）には、目標設定済みチェックミドルウェアを適用
        // 目標設定フォーム(goalSetting)と目標設定の保存(storeTarget)は除外する。
        $this->middleware('check.target.set')->except('goalSetting', 'storeTarget');
    }

    /**
     * 体重ログのメイン画面（/weight_logs）を表示
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // ログインユーザーの取得とIDの確定
        $user = Auth::user();
        $userId = $user->id;

        // 1. デフォルト期間の決定（ログが存在する全期間をデフォルトとする）
        $firstLogDate = WeightLog::where('user_id', $userId)->min('date');

        // デフォルト期間 (内部処理でのフィルタリング期間やリセット時の候補として保持)
        // ログが存在しない場合は、開始日を今日にする (ここはクエリ実行用として残す)
        $defaultStartDateForQuery = $firstLogDate
            ? Carbon::parse($firstLogDate)->toDateString()
            : Carbon::now()->toDateString(); // ログがなければ今日

        // デフォルトの終了日は今日とする
        $defaultEndDateForQuery = Carbon::now()->toDateString();


        // 2. リクエスト値または初期値(null)を採用 (フォーム表示とクエリ実行に使用)
        // リクエストに 'start_date' または 'end_date' がある場合のみ、検索モードと判定
        $isSearching = $request->has('start_date') || $request->has('end_date');

        // --- フォーム表示用の値 ---
        // リクエストに start_date があればその値を、なければ null をセットする (空欄にするため)
        $startDateValue = $request->input('start_date', null);
        // リクエストに end_date があればその値を、なければ null をセットする (空欄にするため)
        $endDateValue = $request->input('end_date', null);


        // --- クエリ実行用の期間決定 ---
        // 検索時はリクエスト値を使用。
        // 初期表示時やリセット時など、リクエストに日付がない場合、$isSearchingはfalse。
        // この場合は、全ログ期間を対象としてフィルタリングするかどうかを判断する必要があります。

        // ここでは、デフォルトの動作として「最新のN件を表示」に切り替えるため、
        // 期間による絞り込みは $isSearching が true の場合のみ行います。

        $logsQuery = WeightLog::where('user_id', $userId);

        // 3. 日付フィルタリングの適用
        if ($isSearching && $startDateValue && $endDateValue) {
            // 検索パラメータが存在し、値も有効な場合のみ期間で絞り込む
            try {
                $start = Carbon::parse($startDateValue)->startOfDay();
                $end = Carbon::parse($endDateValue)->endOfDay();

                // 古い日付(start) 〜 新しい日付(end) の範囲でフィルタリング
                $logsQuery->whereBetween('date', [$start, $end]);

            } catch (\Exception $e) {
                // 日付パースエラー時の処理 (ここでは特になし)
                // \Log::error('Date parsing failed: ' . $e->getMessage());
            }
        }
        // $isSearching が false の場合（初期表示時）は、期間フィルタリングは行われず、
        // 全期間のログが取得されます。その後、ビューで最新の3件などを表示する処理が必要です。
        // もし初期表示で最新3件だけを表示したい場合は、logsQueryに->take(3)を追加。


        // 4. データ取得とソート
        $logs = $logsQuery->orderBy('date', 'desc')->paginate(8);

        // 5. 目標体重の取得
        $target = WeightTarget::where('user_id', $userId)->latest()->first();
        $targetWeight = $target ? $target->target_weight : null;

        // 6. 検索結果表示用の情報（$isSearchingによって表示内容を制御）
        // $startDateValue, $endDateValue はリクエスト値（nullの可能性あり）
        $searchInfo = $this->createSearchInfo($logs, $startDateValue, $endDateValue, $isSearching);

        // 7. ビューにデータを渡す
        return view('weight_logs.index', [
            'logs' => $logs,
            'target_weight' => $targetWeight,
            // フォームの value に設定する値は、リクエスト値か null
            'startDateValue' => $startDateValue,
            'endDateValue' => $endDateValue,
            'searchInfo' => $searchInfo,
            'isSearching' => $isSearching,
        ]);
    }

    /**
     * 検索結果の表示メッセージを生成するヘルパーメソッド
     *
     * @param  \Illuminate\Support\Collection  $logs
     * @param  string  $startDateValue
     * @param  string  $endDateValue
     * @param  bool  $isSearching  // $isSearching を引数として受け取るように修正
     * @return array
     */
    protected function createSearchInfo($logs, ?string $startDate, ?string $endDate, bool $isSearching): array
    {
        $count = $logs->count();
        $message = ''; // メッセージを初期化

        // 戻り値の型ヒントを array に修正

        if ($isSearching) {
            // Carbonで日付を日本語表示用に整形
            Carbon::setLocale('ja');

            // 修正点: nullチェックを追加し、nullの場合は '日付未指定' などで安全に処理する
            // $startDate や $endDate が null の場合、Carbon::parse() はエラーになるため、
            // 安全な処理 ('日付未指定'という代替文字列の使用) を追加します。
            $startDateJp = $startDate ? Carbon::parse($startDate)->format('Y年m月d日') : '日付未指定';
            $endDateJp = $endDate ? Carbon::parse($endDate)->format('Y年m月d日') : '日付未指定';

            // 期間と件数を含むメッセージを生成
            $message = "{$startDateJp}〜{$endDateJp}の検索結果：<span class=\"font-bold\">{$count}件</span>";
        }

        return [
            'count' => $count,
            'count_message' => $message,
        ];
    }

    /**
     * 体重ログの登録フォーム（/weight_logs/create）を表示
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('weight_logs.create');
    }

    /**
     * 体重ログをデータベースに保存 (store)
     * URL: /weight_logs (POST)
     *
     * @param  \App\Http\Requests\WeightLogRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(WeightLogRequest $request)
    {
        // WeightLogRequest でバリデーション済みのデータを取得
        $validated = $request->validated();

        // データベースに保存
        WeightLog::create([
            'user_id' => Auth::id(), // 認証ユーザーのIDをセット
            'date' => $validated['date'],
            'weight' => $validated['weight'],
            'calories' => $validated['calories'],
            'exercise_time' => $validated['exercise_time'],
            'exercise_content' => $validated['exercise_content'],
        ]);

        // 成功時にエラーや古い入力値を明示的にクリアし、クリーンなリダイレクトを保証
        Session::forget('errors');
        Session::forget('_old_input');

        // 一覧画面へリダイレクト
        return redirect()->route('weight_logs.index');
    }

    /**
     * 体重ログの更新フォーム（/weight_logs/{weightLogId}/update）を表示
     *
     * @param int $logId
     * @return \Illuminate\View\View
     */
    public function edit($logId)
    {
        // 該当するログを取得し、ユーザーが所有しているか確認
        $log = WeightLog::where('user_id', Auth::id())->findOrFail($logId);

        return view('weight_logs.update', [
            'log' => $log,
            'log_id' => $logId, // bladeファイル内で使用するために渡す
        ]);
    }

    /**
     * 既存の体重ログをデータベースで更新
     * URL: /weight_logs/{weightLogId} (PUT/PATCH)
     *
     * @param  \App\Http\Requests\WeightLogRequest  $request
     * @param  int $logId 更新対象のログID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(WeightLogRequest $request, $logId)
    {
        // 1. バリデーションは WeightLogRequest で自動的に実行される
        $validated = $request->validated();

        // 2. ログの取得とセキュリティチェック
        $log = WeightLog::where('user_id', Auth::id())->findOrFail($logId);

        // 3. ログの更新
        $log->update([
            'date' => $validated['date'],
            'weight' => $validated['weight'],
            'calories' => $validated['calories'],
            'exercise_time' => $validated['exercise_time'],
            'exercise_content' => $validated['exercise_content'],
        ]);

        // 4. ログ一覧画面へリダイレクト
        return redirect()->route('weight_logs.index');
    }

    /**
     * 目標設定フォーム（/weight_logs/goal_setting）を表示
     *
     * @return \Illuminate\View\View
     */
    public function goalSetting()
    {
        $target = WeightTarget::where('user_id', Auth::id())->latest()->first();

        // 目標が設定されていればその値を、なければDBの NOT NULL 制約に沿った数値の初期値(45.0)をセット
        $targetWeight = $target ? $target->target_weight : 45.0;

        return view('weight_logs.goal_setting', [
            'targetWeight' => $targetWeight,
        ]);
    }

    /**
     * 目標設定をデータベースに保存
     *
     * @param  \App\Http\Requests\GoalSettingRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeTarget(GoalSettingRequest $request)
    {
        // TargetRegisterRequest のバリデーション（認証済みのチェックを含む）を通過
        $validated = $request->validated();
        $userId = Auth::id();

        // indexで latest()->first() を使っているため、履歴を残すために updateOrCreate ではなく create を使用
        WeightTarget::create([
            'user_id' => $userId,
            'target_weight' => $validated['target_weight']
        ]);

        // 成功したら一覧画面へリダイレクト
        return redirect()->route('weight_logs.index');
    }

    /**
     * 指定された体重ログをデータベースから削除する。
     *
     * @param int $id 削除対象のログID
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // 1. URLから渡されたIDを使って、該当ログをDBから強制的に取得
        // ログが存在しない場合は自動的に404エラーを返す
        $log = WeightLog::findOrFail($id);

        // 2. 削除対象のログが現在のユーザーのものであるか確認（セキュリティ上非常に重要）
        if (Auth::id() != $log->user_id) {
            // ここで403エラーが発生する場合、ログにはuser_idが正しく入っているが、
            // ログイン中のユーザーとは異なるIDである、ということが確定します。
            abort(403, 'Unauthorized action.');
        }

        // 3. 所有者チェックを通過した場合のみ削除を実行
        $log->delete();

        // 4. 以前のフォーム送信で残ったエラーセッションを明示的にクリア
        // これにより、Bladeの @if ($errors->any()) が false になりモーダル表示を停止
        Session::forget('errors');

        // 5. バリデーションエラー時などに残る、古い入力値（Old Input）もクリアする。
        // これが残っていると、モーダル内のフォームフィールドにデータが残り、モーダルが開くトリガーになることがある
        Session::forget('_old_input');

        // 6. 削除が成功したら、インデックスページ（一覧画面）にリダイレクトする
        return redirect()->route('weight_logs.index');
    }
}
