@extends('layouts.app')

{{-- このページが Tailwind CSS を必要とするため、親レイアウトに伝達 --}}
@section('is_index_page', true)

@section('title', '体重管理')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

    {{-- コントローラから渡される変数をそのまま利用します --}}
    @php
        // コントローラから渡される想定の変数にフォールバックを設定（環境依存の変数はBladeでは定義できませんが、ここではエラー回避のため）
        // 実際のアプリケーションではコントローラが確実に渡す必要があります
        $todayDate = date('Y-m-d');
        $startDateValue = $startDateValue ?? Request::get('start_date', '');
        $endDateValue = $endDateValue ?? Request::get('end_date', '');
        $target_weight = $target_weight ?? null;
        // logsはページネーションされた結果を想定
        $logs = $logs ?? (new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10));
        $searchInfo = $searchInfo ?? ['count_message' => '', 'count' => $logs->count()];
        $isSearching = $isSearching ?? (Request::has('start_date') && Request::get('start_date') !== '');

        // 最新の体重と目標体重の計算
        // ページネーションされたコレクションの場合、最新のログは通常最初の要素
        $latestLog = $logs->isNotEmpty() ? $logs->first() : null;
        $latestWeight = $latestLog ? (float)$latestLog->weight : 0.0;
        $targetWeight = $target_weight ? (float)$target_weight : 0.0;
        $diffToTarget = $targetWeight !== 0.0 ? round($targetWeight - $latestWeight, 1) : 0.0;

        // 表示用の整形
        $targetDisplay = $targetWeight === 0.0 ? '---' : number_format($targetWeight, 1);
        $diffDisplay = $latestWeight === 0.0 || $targetWeight === 0.0 ? '---' : number_format($diffToTarget, 1);
        $latestDisplay = $latestWeight === 0.0 ? '---' : number_format($latestWeight, 1);
    @endphp

    <!-- サマリーカードセクション -->
    <div class="summary-container">
        <div class="summary-grid">

            <!-- 目標体重カード -->
            <div class="summary-card">
                <div class=summary-card-container>
                    <p class="card-label">目標体重</p>
                    <p class="card-value">{{ $targetDisplay }} <span class="card-unit">kg</span></p>
                </div>
            </div>

            <!-- 目標までカード -->
            <div class="summary-card">
                <div class=summary-card-container>
                    <p class="card-label">目標まで</p>
                    <p class="card-value">{{ $diffDisplay }} <span class="card-unit">kg</span></p>
                </div>
            </div>

            <!-- 最新体重カード -->
            <div class="summary-card last-card">
                <div class=summary-card-container>
                    <p class="card-label">最新体重</p>
                    <p class="card-value">{{ $latestDisplay }} <span class="card-unit">kg</span></p>
                </div>
            </div>

        </div>
    </div>

    <!-- データ一覧とフィルタリングセクション -->
    <div class="data-section summary-card-shadow">

        <!-- フィルタリングとデータ追加ボタン -->
        <div class="filter-controls">

            <div class="filter-inputs-group">
                <!-- フォームとして定義し、GETで送信 -->
                <form method="GET" action="{{ url('/weight_logs') }}" class="filter-inputs-row">
                    <!-- 開始日（古い日付） -->
                    <input type="date" name="start_date" class="form-input" value="{{ $startDateValue }}">
                    <span>～</span>
                    <!-- 終了日（新しい日付） -->
                    <input type="date" name="end_date" class="form-input" value="{{ $endDateValue }}">

                    <!-- 検索ボタン -->
                    <button type="submit" class="search-btn">
                        検索
                    </button>
                </form>

                <!-- リセットボタン (検索実行中のみ表示) -->
                <!-- URLに日付パラメータがあれば検索実行中とみなし、リセットボタンを表示 -->
                @if($isSearching)
                    <a href="{{ url('/weight_logs') }}" class="reset-btn">
                        リセット
                    </a>
                @endif
            </div>

            <!-- リンクからlabelに変更し, CSS駆動モーダルを起動させる -->
            <label for="modal-toggle" class="add-data-btn cursor-pointer">
                <span>データ追加</span>
            </label>
        </div>

        <!-- 検索結果と件数表示エリア -->
        <div class="search-info-area">
            @if (!empty($searchInfo['count_message']))
                {{-- コントローラから渡された $searchInfo['count_message'] を使用 --}}
                <div class="count_massage">
                    {!! $searchInfo['count_message'] !!} {{-- HTMLタグを許可して表示 --}}
                </div>
            @else
                {{-- 検索メッセージがない場合、期間と件数を表示 --}}
                @if($isSearching)
                    {{ $startDateValue }}～{{ $endDateValue }}の検索結果：<span class="font-bold">{{ $searchInfo['count'] }}件</span>
                @endif
            @endif
        </div>

        <!-- 体重ログテーブル -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>体重</th>
                        <th>食事摂取カロリー</th>
                        <th>運動時間</th>
                        <th class="text-right"></th> <!-- 編集アイコン用 -->
                    </tr>
                </thead>
                <tbody>

                    <!-- コントローラーから渡された $logs を使用 -->
                    @forelse ($logs as $log)
                    <tr>
                        <!-- Eloquent Modelのプロパティとしてアクセス -->
                        <td>{{ \Carbon\Carbon::parse($log->date)->format('Y/m/d') }}</td>
                        <td>{{ number_format($log->weight, 1) }}kg</td>
                        <td>{{ number_format($log->calories) }}cal</td>
                        <td>{{ $log->exercise_time }}</td>
                        <td class="text-right">
                            <!-- 編集アイコン (鉛筆) -->
                            <button class="edit-btn" onclick="window.location='{{ route('weight_logs.edit', $log->id) }}'">
                                <img src="{{ asset('storage/images/pen.png') }}" alt="ペンアイコン" class="icon">
                            </button>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">
                                この期間にはログがありません。「データ追加」ボタンから登録してください。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ページネーション ( Tailwind CSS を適用する唯一のセクション ) -->
        <div class="pagination-container">
            {{ $logs->appends(request()->input())->links() }}
        </div>

    </div>
@endsection

{{-- モーダル部分とJavaScriptの定義 --}}
@section('scripts')

    <!-- 1. モーダル起動状態を保持する非表示チェックボックス -->
    <!-- モーダル表示制御のために必須 -->
    <!-- ブラウザのフォーム復元を無効化 -->
    <input
        type="checkbox"
        id="modal-toggle"
        class="hidden"
        autocomplete="off"
    @if ($errors->any())
        checked
    @endif
    >

    <!-- 2. モーダルオーバーレイとコンテンツ -->
    <div class="modal-overlay">
        <div class="modal-content">

            <!-- モーダルヘッダー -->
            <div class="modal-header">
                <h1 class="modal-title">Weight Logを追加</h1>
            </div>

            <!-- フォームエリア -->
            <form id="weightLogForm" action="{{ route('weight_logs.store') }}" method="POST" class="space-y-5" novalidate>
                @csrf

                <div class="modal-form-group">
                    <label for="date" class="form-title">
                        日付<span class="required">必須</span>
                    </label>

                    <div class="date-input-wrapper">

                        <!-- 1. 値の保持・送信とカレンダーピッカーの起動を担当（透明にして上から重ねる） -->
                        <!-- NOTE: type="date" の入力欄はブラウザのデフォルト表示形式があり、これを opacity: 0 で隠しています。 -->
                        <input type="date" name="date" id="date"
                            value="{{ old('date') }}"
                            class="modal-form-input select-input date-picker-hidden">

                        <!-- 2. inputと同じ見た目にして、Y年m月d日を表示する（見た目を担当） -->
                        <div id="display-date" class="date-display-overlay">
                            <!-- 日本語形式の日付が表示されます -->
                            <span id="date-text" class="placeholder">年 / 月 / 日</span>
                            <!-- カレンダーアイコンは Lucide Iconsではなく、シンプルな絵文字で代替しています -->
                            <span class="calendar-icon">
                                <img src="{{ asset('storage/images/Polygon 2.png') }}" alt="ドロップダウンアイコン" class="drop-icon">
                            </span>
                        </div>
                    </div>
                    @error('date')
                        <p class="error-text">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- 体重フィールド -->
                <div class="modal-form-group">
                    <label for="weight" class="form-title">
                        体重<span class="required">必須</span>
                    </label>
                    <div class="input-with-unit-modal">
                        <input type="text" id="weight" name="weight" step="0.1" min="0" placeholder="50.0" required class="modal-form-input" value="{{ old('weight') }}">
                        <span class="unit-text">kg</span>
                    </div>
                    @error('weight')
                        <p class="error-text">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- 食事摂取カロリーフィールド -->
                <div class="modal-form-group">
                    <label for="calories" class="form-title">
                        食事摂取カロリー<span class="required">必須</span>
                    </label>
                    <div class="input-with-unit-modal">
                        <input type="number" id="calories" name="calories" min="0" placeholder="1200" class="modal-form-input" inputmode="numeric" value="{{ old('calories') }}">
                        <span class="unit-text">kcal</span>
                    </div>
                    @error('calories')
                        <p class="error-text">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- 運動時間フィールド -->
                <div class="modal-form-group">
                    <label for="exercise_time" class="form-title">
                        運動時間<span class="required">必須</span>
                    </label>
                    <!-- モデルのアクセサにより H:i:s から H:i 形式に自動変換される -->
                    <input type="time"
                        id="exercise_time"
                        name="exercise_time"
                        value="{{ old('exercise_time', '00:00') }}"
                        class="modal-form-input">
                    @error('exercise_time')
                        <p class="error-text">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- 運動内容フィールド -->
                <div class="modal-form-group">
                    <label for="exercise_content" class="form-title">運動内容</label>
                    <textarea id="exercise_content" name="exercise_content" rows="2" placeholder="運動内容を追加" class="modal-form-input">{{ old('exercise_content') }}</textarea>
                    @error('exercise_content')
                        <p class="error-text">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- アクションボタン -->
                <div class="btn-container">
                    <!-- 戻るボタン (labelタグでチェックボックスをOFFにする) -->
                    <label for="modal-toggle" class="btn-modal-secondary cursor-pointer">
                        戻る
                    </label>
                    <!-- 登録ボタン -->
                    <button type="submit" class="btn-modal-primary">
                        登録
                    </button>
                </div>
            </form>

        </div>
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lucideアイコンの初期化はapp.blade.phpで行われるため不要ですが、念のため残すことも可能です。
        // lucide.createIcons();

        // --------------------------------------------------------
        // DOM要素の取得
        // --------------------------------------------------------
        const form = document.getElementById('weightLogForm');
        const exerciseTimeInput = document.getElementById('exercise_time');

        // モーダル内の日付入力フィールド
        const dateInput = document.getElementById('date');
        const dateTextSpan = document.getElementById('date-text');

        // モーダル開閉トグル
        const modalToggle = document.getElementById('modal-toggle');


        // フォーム要素が存在しない場合、以降の処理を中断
        if (!form || !exerciseTimeInput || !dateInput || !dateTextSpan || !modalToggle) {
            console.error("Missing required form elements (form, exercise_time, date, date-text, or modal-toggle).");
            return;
        }

        // --------------------------------------------------------
        // 1. 運動時間ロジック
        // --------------------------------------------------------
        form.addEventListener('submit', function(e) {
            // ブラウザが自動補完する '00:00' または空欄の場合をチェック
            const isBrowserDefaultValue = exerciseTimeInput.value === '00:00';
            const isEmptyValue = exerciseTimeInput.value === '';

            if (isBrowserDefaultValue || isEmptyValue) {
                // 値を強制的に空欄（''）にして、サーバーに「未入力」として送信させる
                // これにより、Laravel側の required バリデーションが確実に動作する
                exerciseTimeInput.value = '';
            }
        });

        // --------------------------------------------------------
        // 2. 日付の表示形式を変換・初期値を設定するJavaScript
        // --------------------------------------------------------

        /**
         * YYYY-MM-DD形式の日付を「Y年m月d日」形式に変換する
         * @param {string} dateString - YYYY-MM-DD形式の文字列
         * @returns {string} - Y年m月d日形式の文字列
         */
        function formatJapaneseDate(dateString) {
            if (!dateString) return '';

            // タイムゾーンの影響を回避するため、UTCとして解釈させる
            const parts = dateString.split('-');
            if (parts.length !== 3) return '';

            const year = parts[0];
            // 月と日の先頭の0を取り除いて表示 (例: 09月 -> 9月)
            const month = parseInt(parts[1], 10);
            const day = parseInt(parts[2], 10);

            // Dateオブジェクトを使わずに直接整形することで、より確実なローカルタイムゾーン回避
            return `${year}年${month}月${day}日`;
        }

        /**
         * 今日の日付を YYYY-MM-DD 形式で取得するヘルパー関数
         * @returns {string} フォーマットされた日付文字列 (例: "2025-10-08")
         */
        function getTodayDateISO() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        /**
         * モーダル内の日付表示を更新し、初期設定として今日の日付を設定する
         * @param {boolean} shouldSetTodayIfEmpty - 値が設定されていない場合に今日の日付を設定するかどうか
         */
        function updateDateDisplay(shouldSetTodayIfEmpty) {
            let dateValue = dateInput.value;

            // shouldSetTodayIfEmptyがtrueで、かつ現在値が設定されていない場合に今日の日付を設定
            if (shouldSetTodayIfEmpty && (!dateValue || dateValue === '')) {
                const todayISO = getTodayDateISO();

                // input[type="date"] に 'YYYY-MM-DD' 形式で今日の日付を設定
                dateValue = todayISO;
                dateInput.value = dateValue; // inputにも今日の日付をセット
            }

            const formattedDate = formatJapaneseDate(dateValue);

            if (formattedDate) {
                // 有効な日付がある場合
                dateTextSpan.textContent = formattedDate;
                // 日付が設定されている場合は、プレースホルダークラスを削除（文字色を濃くする）
                dateTextSpan.classList.remove('placeholder');
            } else {
                // 日付がない場合、プレースホルダーテキストを使用
                dateTextSpan.textContent = '年 / 月 / 日';
                // プレースホルダーの場合はクラスを追加（文字色を薄くする）
                dateTextSpan.classList.add('placeholder');
            }
        }

        // ページロード完了時（DOMContentLoaded）に初期値を反映させる
        // Laravelの old() の値や初期値がある場合を処理する。
        // isInitialLoad=trueで今日の日付が設定される。
        updateDateDisplay(true);

        // ユーザーが日付を変更したときに表示を更新
        dateInput.addEventListener('change', () => updateDateDisplay(false));

        // --------------------------------------------------------
        // 3. モーダル開閉時の日付リセット・初期値設定ロジック
        // --------------------------------------------------------
        modalToggle.addEventListener('change', function() {
            // チェックボックスがチェックされた（モーダルが開いた）とき
            if (this.checked) {

                // ★エラーでold()の値が残っている場合を除き、フォームの値をクリアしてから今日の日付をセットする★
                // Laravelのerrors->any() が true の場合は old() の値を保持したまま
                const hasLaravelErrors = {{ $errors->any() ? 'true' : 'false' }};

                if (!hasLaravelErrors) {
                    // フォーム全体をリセット（optional: 他のフィールドもクリアしたい場合）
                    // form.reset();

                    // date inputの値をクリアしてから、今日の日付をセット
                    dateInput.value = '';
                }

                // todayDate（今日の日付）を設定し、表示を更新
                updateDateDisplay(true);

            } else {
                // チェックボックスが解除された（モーダルが閉じた）とき
                // 特に処理は不要
            }
        });
    });
    </script>
@endsection
