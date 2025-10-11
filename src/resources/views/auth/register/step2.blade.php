@extends('layouts.auth')

@section('title', '新規会員登録')

@section('page-title', '新規会員登録')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/step2.css')}}">
@endsection

@section('content')
    {{-- STEP情報は共通レイアウトにはないので、content内で定義 --}}
    <div class="step-info">STEP 2 体重データの入力</div>

    <!-- 認証成功後のメッセージ表示 (STEP 1からリダイレクトされた場合) -->
    @if (session('status'))
        <div class="alert-message success">
            {{ session('status') }}
        </div>
    @endif

    <form class="form" action="{{ route('register.target.save') }}" method="post" novalidate>
        @csrf

        {{-- 現在の体重 --}}
        <div class="form-group">
            <label for="weight">現在の体重</label>
            <div class="input-with-unit">
                {{-- input-with-unitクラスが`div`タグに修正されました --}}
                <input id="weight" type="number" step="0.1" class="form-control" name="weight" value="{{ old('weight') }}" required autofocus placeholder="現在の体重を入力">
                <span class="unit-text">kg</span>
            </div>
            <p class="register-form__error-message">
                @error('weight')
                {{ $message }}
                @enderror
            </p>
        </div>

        {{-- 目標の体重 --}}
        <div class="form-group">
            <label for="target_weight">目標の体重</label>
            <div class="input-with-unit">
                {{-- input-with-unitクラスが`div`タグに修正されました --}}
                <input id="target_weight" type="number" step="0.1" class="form-control" name="target_weight" value="{{ old('target_weight') }}" required placeholder="目標の体重を入力">
                <span class="unit-text">kg</span>
            </div>
            @error('target_weight')
                <p class="error-message">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- アカウント作成ボタン --}}
        <button type="submit" class="next-btn">
            アカウント作成
        </button>
    </form>
@endsection
