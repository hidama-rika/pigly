@extends('layouts.app')

@section('title', '目標体重設定')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/goal_setting.css') }}">
@endsection

@section('content')
    <!-- 目標設定カード -->
    <div class="goal-setting-card">

        <h1 class="card-title">目標体重設定</h1>

        <!-- フォーム -->
        <form action="{{ route('weight_logs.storeTarget') }}" method="POST" class="goal-form" novalidate>
            @csrf

            <!-- 目標体重入力フィールド -->
            <div class="input-group">
                <label for="target_weight" class="input-label hidden">目標体重</label>
                <div class="input-with-unit">
                    <!-- $targetWeight変数がコントローラーから渡されることを想定 -->
                    <input type="text" id="target_weight" name="target_weight"
                        value="{{ old('target_weight', $targetWeight) }}" step="0.1" required
                        class="form-input large-input text-center">
                    <span class="unit-text">kg</span>
                </div>
                {{-- エラーメッセージの表示 --}}
                @error('target_weight')
                    <p class="error-text">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- ボタンエリア -->
            <div class="button-area">
                <!-- 戻るボタン -->
                <a href="{{ route('weight_logs.index') }}" class="btn btn-back">
                    戻る
                </a>
                <!-- 更新ボタン -->
                <button type="submit" class="btn btn-update">
                    更新
                </button>
            </div>
        </form>
    </div>
@endsection
