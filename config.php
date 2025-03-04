<?php
// エラーログの設定
$logFile = __DIR__ . '/php-error.log';
ini_set('log_errors', 1);
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 環境変数の読み込み
function loadEnv() {
    static $loaded = false;
    if ($loaded) return;

    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        error_log('環境設定ファイルが見つかりません: ' . $envFile);
        die('アプリケーションの設定が完了していません。README.mdを参照して.envファイルを設定してください。');
    }

    try {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new Exception('.envファイルの読み込みに失敗しました');
        }

        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (empty($name)) continue;
            
            $_ENV[$name] = $value;
            putenv(sprintf('%s=%s', $name, $value));
        }

        // 必須環境変数のチェック
        $required = ['APP_URL', 'ADMIN_SECRET_KEY', 'ROOMS_DIR'];
        foreach ($required as $var) {
            if (!getenv($var)) {
                throw new Exception('必須の環境変数が設定されていません: ' . $var);
            }
        }

        // グローバル定数の設定
        define('ADMIN_URL_KEY', 'admin_' . getenv('ADMIN_SECRET_KEY'));
        define('ROOMS_DIR', rtrim(getenv('ROOMS_DIR'), '/') . '/');
        define('APP_URL', rtrim(getenv('APP_URL'), '/'));

        $loaded = true;
    } catch (Exception $e) {
        error_log('環境変数の読み込みエラー: ' . $e->getMessage());
        if (getenv('APP_ENV') === 'development') {
            die('設定エラー: ' . $e->getMessage());
        } else {
            die('アプリケーションの設定エラーが発生しました。管理者に連絡してください。');
        }
    }
}

// 環境変数の読み込みを実行
loadEnv();

// ディレクトリ構造の確認と作成
if (!file_exists(ROOMS_DIR)) {
    if (!@mkdir(ROOMS_DIR, 0755, true)) {
        error_log('ルームディレクトリの作成に失敗しました: ' . ROOMS_DIR);
        die('ディレクトリの作成権限がありません。パーミッションを確認してください。');
    }
}

if (!is_writable(ROOMS_DIR)) {
    error_log('ルームディレクトリに書き込み権限がありません: ' . ROOMS_DIR);
    die('ディレクトリの書き込み権限がありません。パーミッションを確認してください。');
}

// 未処理のエラーをキャッチ
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP エラー: [$errno] $errstr in $errfile on line $errline");
    return false;
});

// 未処理の例外をキャッチ
set_exception_handler(function($e) {
    error_log('未処理の例外: ' . $e->getMessage());
    if (getenv('APP_ENV') === 'development') {
        die('エラー: ' . $e->getMessage());
    } else {
        die('アプリケーションエラーが発生しました。管理者に連絡してください。');
    }
});