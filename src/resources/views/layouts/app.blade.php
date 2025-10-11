<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PiGLy - @yield('title')</title>

    @if(View::hasSection('is_index_page'))
    <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    @yield('styles')

</head>
<body>

    <header class="header-shadow header">
        <div class="header-content">
            <h1 class="logo">PiGLy</h1>
            <nav class="nav-menu">

                <button
                    class="nav-button setting-button @if(request()->routeIs('weight_logs.goal_setting')) active-nav @endif"
                    onclick="window.location='{{ route('weight_logs.goal_setting') }}'">
                    <img src="{{ asset('storage/images/Vector.png') }}" alt="目標体重設定アイコン" class="icon">
                    <span class="nav-text">目標体重設定</span>
                </button>

                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="nav-button logout-button">
                        <img src="{{ asset('storage/images/Group.png') }}" alt="ログアウトアイコン" class="icon">
                        <span class="nav-text">ログアウト</span>
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="main-content">
        @yield('content')
    </main>

    @yield('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

</body>
</html>