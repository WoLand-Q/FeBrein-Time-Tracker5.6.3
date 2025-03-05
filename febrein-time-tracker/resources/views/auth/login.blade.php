<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        /* Небольшое оформление */
        body {
            background: #121212;
            color: #fff;
            font-family: sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-container {
            width: 400px;
            background: #1f1f1f;
            padding: 2rem;
            border-radius: 8px;
        }
        label {
            display: block;
            margin-top: 1rem;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #888;
            background: #2d2d2d;
            color: #fff;
        }
        button {
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background-color: #ff4545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .error-msg {
            color: #ff8888;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h1>Login</h1>

    @if(session('status'))
        <div class="error-msg">{{ session('status') }}</div>
    @endif

    <!-- Форма логина отправится POST /login -->
    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <label for="login">Логин</label>
        <input id="login" type="text" name="login" required autofocus>

        <label for="password">Пароль</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
