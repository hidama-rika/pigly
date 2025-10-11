@extends('layouts.auth')

@section('title', '新規会員登録')

@section('page-title', '新規会員登録')

{{-- 固有のCSSを@yield('styles')に差し込む --}}
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/step1.css')}}">
@endsection

@section('content')
    {{-- STEP情報は共通レイアウトにはないので、content内で定義 --}}
    <div class="step-info">STEP 1 アカウント情報の登録</div>

    <form class="form" action="{{ route('register') }}" method="post" novalidate>
        @csrf

        {{-- お名前 --}}
        <div class="form-group">
            <label for="name">お名前</label>
            <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus placeholder="名前を入力">
            <p class="register-form__error-message">
                @error('name')
                {{ $message }}
                @enderror
            </p>
        </div>

        {{-- メールアドレス --}}
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="メールアドレスを入力">
            <p class="register-form__error-message">
                @error('email')
                {{ $message }}
                @enderror
            </p>
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" class="form-control" name="password" required placeholder="パスワードを入力">
            <p class="register-form__error-message">
                @error('password')
                {{ $message }}
                @enderror
            </p>
        </div>

        {{-- 次に進むボタン --}}
        <button type="submit" class="next-btn">
            次へ進む
        </button>
    </form>

    {{-- ログインはこちらリンク --}}
    <a class="login-link" href="{{ route('login') }}">
        ログインはこちら
    </a>
@endsection
