@extends('layouts.auth')

{{-- ページタイトルをセット --}}
@section('title', 'ログイン')

{{-- mainタグのクラスをログイン専用に変更 --}}
@section('container_class', 'login-container')

{{-- 認証フォームのメインタイトルをセット --}}
@section('page-title', 'ログイン')

{{-- 固有のCSSを@yield('styles')に差し込む --}}
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css')}}">
@endsection

@section('content')
    <form class="form" action="/login" method="post" novalidate>
        @csrf

        {{-- メールアドレス --}}
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="メールアドレスを入力">
            <p class="login-form__error-message">
                @error('email')
                {{ $message }}
                @enderror
            </p>
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" class="form-control" name="password" required placeholder="パスワードを入力">
            <p class="login-form__error-message">
                @error('password')
                {{ $message }}
                @enderror
            </p>
        </div>

        {{-- ログインボタン --}}
        <button type="submit" class="login-btn">
            ログイン
        </button>
    </form>

    {{-- アカウント作成リンク --}}
    <div class="link">
        <a class="register-link" href="/register">
            アカウント作成はこちら
        </a>
    </div>
@endsection
