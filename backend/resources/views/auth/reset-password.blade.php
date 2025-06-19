<!DOCTYPE html>
<html>
<head>
    <title>Resetar Senha</title>
</head>
<body>
    <h1>Resetar Senha</h1>
    <p>Token: {{ $token }}</p>
    <form method="POST" action="/api/v1/password/reset">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="email" name="email" placeholder="Seu email" required>
        <input type="password" name="password" placeholder="Nova senha" required>
        <input type="password" name="password_confirmation" placeholder="Confirmar senha" required>
        <button type="submit">Resetar Senha</button>
    </form>
</body>
</html>
