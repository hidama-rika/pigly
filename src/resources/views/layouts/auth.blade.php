<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- 各ページでタイトルを定義できるように @yield で設定 --}}
    <title>PiGLy-@yield('title', '認証ページ')</title>

    {{-- 共通フォントとリセットCSS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital@0;1&family=Shippori+Mincho&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{ asset('css/auth.css')}}">

    @yield('styles')
</head>

{{-- mainタグのクラス名を各ページで変更できるように修正 --}}
<body>

    <main class="@yield('container_class', 'register-container')">

        <div class="logo-container">
            <div class="logo">PiGLy</div>
            {{-- 各ページ固有のタイトル（例: 新規会員登録, ログイン） --}}
            <div class="@yield('page-title-class', 'page-title')">@yield('page-title')</div>
        </div>

        {{-- STEP情報など、固有のコンテンツがここに入る --}}
        @yield('content')

    </main>

</body>
</html>
