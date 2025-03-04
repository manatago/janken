<?php
// 共通設定の読み込み
require_once __DIR__ . '/config.php';

// CORS設定
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // リクエストパラメータの取得
    $room = isset($_GET['room']) ? htmlspecialchars($_GET['room']) : null;
    $isAdmin = isset($_GET['key']) && $_GET['key'] === ADMIN_URL_KEY;

    // メイン処理
    function main() {
        global $room, $isAdmin;
        
        // Cookieから名前を取得
        $playerName = isset($_COOKIE['player_name']) ? $_COOKIE['player_name'] : '';
        $isAdminCookie = isset($_COOKIE['is_admin']) && $_COOKIE['is_admin'] === 'true';
        
        // 管理者URLの場合
        if ($isAdmin) {
            require_once 'templates/admin.html';
            return;
        }
        
        // ルームが指定されている場合
        if ($room) {
            if (!$playerName) {
                require_once 'templates/name_input.html';
            } else {
                require_once 'templates/game.html';
            }
            return;
        }
        
        // 管理者でルーム作成画面
        if ($isAdminCookie && $playerName) {
            require_once 'templates/create_room.html';
            return;
        }
        
        // デフォルトは名前入力画面
        require_once 'templates/name_input.html';
    }

    // メイン処理の実行
    main();

} catch (Exception $e) {
    error_log('アプリケーションエラー: ' . $e->getMessage());
    if (getenv('APP_ENV') === 'development') {
        die('エラー: ' . $e->getMessage());
    } else {
        die('アプリケーションエラーが発生しました。システム管理者に連絡してください。');
    }
}