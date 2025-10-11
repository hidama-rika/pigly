@extends('layouts.app')

@section('title', 'ログ更新')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/update.css') }}">
@endsection

@section('content')
    <!-- ログ更新カード -->
    <div class="log-card card-shadow">

        <h1 class="card-title">Weight Log</h1>

        <!-- フォーム（更新） -->
        <form method="POST" action="{{ route('weight_logs.update', $log->id) }}" novalidate>
            @csrf
            @method('PUT')

            <!-- 日付 (ドロップダウン) -->
            <div class="form-group">
                <label for="date" class="input-label">日付</label>

                <div class="date-input-wrapper">
                    <!-- 1. 値の保持・送信とカレンダーピッカーの起動を担当（透明にして上から重ねる） -->
                    <!-- NOTE: type="date" の入力欄はブラウザのデフォルト表示形式があり、これを opacity: 0 で隠しています。 -->
                    <input type="date" name="date" id="date"
                        value="{{ old('date', $log->date->format('Y-m-d')) }}"
                        class="form-input select-input date-picker-hidden">

                    <!-- 2. inputと同じ見た目にして、Y年m月d日を表示する（見た目を担当） -->
                    <div id="display-date" class="date-display-overlay">
                        <!-- 日本語形式の日付が表示されます -->
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

            <!-- 体重 -->
            <div class="form-group">
                <label for="weight" class="input-label">体重</label>
                <div class="input-with-unit">
                    <input type="text" id="weight" name="weight"
                        value="{{ old('weight', $log->weight) }}" step="0.1" required
                        class="form-input">
                    <span class="unit-text">kg</span>
                </div>
                @error('weight')
                    <p class="error-text">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- 食事摂取カロリー -->
            <div class="form-group">
                <label for="calories" class="input-label">食事摂取カロリー</label>
                <div class="input-with-unit">
                    <input type="text" id="calories" name="calories"
                        value="{{ old('calories', $log->calories) }}" required
                        class="form-input" inputmode="numeric">
                    <span class="unit-text">cal</span>
                </div>
                @error('calories')
                    <p class="error-text">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- 運動時間 -->
            <div class="form-group">
                <label for="exercise_time" class="input-label">運動時間</label>
                {{-- 画像の「00:00」形式に合わせて、type="time"を使用します --}}
                <input type="time" id="exercise_time" name="exercise_time"
                    value="{{ old('exercise_time', $log->exercise_time) }}" step="60" placeholder="00:00" required
                    class="form-input time-input">
                @error('exercise_time')
                    <p class="error-text">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- 運動内容 -->
            <div class="form-group">
                <label for="exercise_content" class="input-label">運動内容</label>
                <textarea id="exercise_content" name="exercise_content" rows="4"
                    class="form-input textarea-input">{{ old('exercise_content', $log->exercise_content) }}</textarea>
                @error('exercise_content')
                    <p class="error-text">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            <!-- ボタンエリア -->
            <div class="button-area">
                <div class=form-btn-area>
                    <!-- 戻るボタン -->
                    <button type="button" onclick="window.history.back()" class="btn btn-back">
                        戻る
                    </button>
                    <!-- 更新ボタン -->
                    <button type="submit" class="btn btn-update">
                        更新
                    </button>
                </div>

            </div>
        </form>

        <!-- フォーム（削除）: 削除ボタンと紐付け -->
        <form id=delete-form action="{{ route('weight_logs.destroy', $log->id) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>

        <!-- 削除ボタン（画像右下のゴミ箱アイコン） -->
        <button type="submit"
            class="btn-delete"
            form="delete-form"
            onclick="return confirm('この体重ログを削除しますか？\nこの操作は元に戻せません。');"
            aria-label="削除">
            <img src="{{ asset('storage/images/Frame 406.png') }}" alt="削除アイコン" class="delete-icon">
        </button>

    </div>
@endsection

{{-- ページ固有のJavaScriptを app.blade.php の @yield('scripts') に渡す --}}
@section('scripts')
    <script>
        // --------------------------------------------------------
        // 日付の表示形式を変換するJavaScript
        // --------------------------------------------------------
        const dateInput = document.getElementById('date');
        // 表示用のテキストが入る span 要素を取得
        const dateTextSpan = document.getElementById('date-text');

        /**
        * YYYY-MM-DD形式の日付を「Y年m月d日」形式に変換する
        * @param {string} dateString - YYYY-MM-DD形式の文字列
        * @returns {string} - Y年m月d日形式の文字列
        */
        function formatJapaneseDate(dateString) {
            if (!dateString) return '';

            // タイムゾーンの影響を回避するため、UTCとして解釈させる
            const date = new Date(dateString + 'T00:00:00');

            if (isNaN(date.getTime())) return '';

            const year = date.getFullYear();
            const month = date.getMonth() + 1;
            const day = date.getDate();

            // NaNチェック (無効な日付の場合)
            if (isNaN(year)) return '';

            return `${year}年${month}月${day}日`;
        }

        /**
        * 表示を更新する
        */
        function updateDateDisplay(isInitialLoad = false) {
            const dateValue = dateInput.value;
            const formattedDate = formatJapaneseDate(dateValue);

            if (formattedDate) {
                // 有効な日付がある場合
                dateTextSpan.textContent = formattedDate;
                dateTextSpan.classList.remove('placeholder');
            } else if (isInitialLoad) {
                // 初期読み込みで日付が空の場合、プレースホルダーテキストを使用
                dateTextSpan.textContent = '年 / 月 / 日';
                dateTextSpan.classList.add('placeholder');
            } else {
                // ユーザーが空欄にした場合（通常は date input が date type のため発生しないが安全策）
                dateTextSpan.textContent = '';
                dateTextSpan.classList.remove('placeholder');
            }
        }

        // ユーザーが日付を変更したときに表示を更新
        dateInput.addEventListener('change', () => updateDateDisplay(false));

        // ページ読み込み完了時ではなく、DOMツリー構築完了時に初期値を反映させる
        document.addEventListener('DOMContentLoaded', () => {
            updateDateDisplay(true); // 初期読み込みとして実行
        });

    </script>
@endsection
