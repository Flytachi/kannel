<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kannel - <?= $_error['code'] ?></title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="shortcut icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/favicon.svg" type="image/svg+xml">

    <link rel="stylesheet" href="/static/xp/style.css" type="text/css">
</head>
<body style="display: flex;justify-content: center;align-items: center;height: 100vh;">

<div class="error-window">
    <div class="error-header">
        <span class="title">Error <?= $_error['code'] ?></span>
    </div>
    <div class="error-body">
        <span class="error-icon" style="color: red">&times;</span>
        <div class="error-text"><b><?= $_error['code'] ?></b> <?= $_error['message'] ?></div>
    </div>
    <div class="error-footer"></div>
</div>

</body>
</html>
