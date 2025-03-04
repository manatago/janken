<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ページが見つかりません</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .error-container {
            text-align: center;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 600px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #2c3e50;
            margin-bottom: 2rem;
        }
        .back-link {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-message">ページが見つかりません</h1>
        <p>お探しのページは存在しないか、移動した可能性があります。</p>
        <div class="error-actions">
            <a href="./index.php" class="back-link">トップページへ戻る</a>
        </div>
    </div>
</body>
</html>