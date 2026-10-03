<?php
declare(strict_types=1);

$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
if (!$isLocal) {
    http_response_code(404);
    exit;
}

header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; img-src 'self'; font-src 'self'; style-src 'self' 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");

$password = '';
$hash = '';
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    preg_match_all('/./us', $password, $characters);
    if (count($characters[0]) < 12) {
        $error = 'Use a password with at least 12 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }
    $password = '';
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <title>Password hash | I.D.S Admin</title>
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="stylesheet" href="admin.css">
  <style>
    .hash-tool{width:min(100% - 40px,620px);margin:72px auto;padding:34px;background:#fff;border:1px solid #d9dfdc}
    .hash-tool h1{margin:0 0 10px;color:#202624;font-size:30px;font-weight:500}
    .hash-tool p{color:#69716e;font-size:13px;line-height:1.6}
    .hash-tool label{display:grid;gap:8px;margin:24px 0 12px;color:#35403b;font-size:12px;font-weight:500}
    .hash-tool input{min-height:46px;padding:10px 12px;border:1px solid #d5dcd8;background:#fff;font:inherit}
    .hash-output{display:grid;gap:10px;margin-top:24px}
    .hash-output textarea{width:100%;min-height:96px;padding:12px;border:1px solid #d5dcd8;background:#f5f7f5;color:#26342d;font:12px/1.5 Consolas,monospace;overflow-wrap:anywhere;resize:vertical}
    .hash-error{color:#a4472e!important}
    .hash-back{display:inline-block;margin-top:24px;color:#35403b;font-size:12px}
    @media(max-width:600px){.hash-tool{margin:32px auto;padding:23px}.hash-tool h1{font-size:25px}}
  </style>
</head>
<body class="admin-page">
  <main class="hash-tool">
    <p class="eyebrow">Local utility</p>
    <h1>Generate an admin password hash</h1>
    <p>Enter the password you want to use. The password is hashed by PHP and is not saved. This page only works from this computer.</p>
    <form method="post" autocomplete="off">
      <label for="password">Password<input id="password" name="password" type="password" minlength="12" required autofocus autocomplete="new-password"></label>
      <button class="button button-primary" type="submit">Generate hash <span aria-hidden="true">↗</span></button>
    </form>
    <?php if ($error !== ''): ?><p class="hash-error" role="alert"><?= escapeHtml($error) ?></p><?php endif; ?>
    <?php if ($hash !== ''): ?>
      <section class="hash-output" aria-label="Generated password hash">
        <label for="hash-output">Password hash</label>
        <textarea id="hash-output" readonly><?= escapeHtml($hash) ?></textarea>
      </section>
    <?php endif; ?>
    <a class="hash-back" href="index.php">Back to admin sign in</a>
  </main>
</body>
</html>
