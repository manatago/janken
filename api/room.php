<?php
// 共通設定の読み込み
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONSリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// エラーレスポンス関数
function sendError($message, $code = 400) {
    http_response_code($code);
    error_log('Room API Error: ' . $message);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// 成功レスポンス関数
function sendSuccess($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// ルーム名のバリデーション
function validateRoomName($roomName) {
    return preg_match('/^[a-zA-Z0-9_-]+$/', $roomName) && strlen($roomName) <= 50;
}

// メインの処理
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // リクエストボディの取得
            $requestBody = file_get_contents('php://input');
            error_log('Request Body: ' . $requestBody); // デバッグ用

            $data = json_decode($requestBody, true);
            if (!isset($data['room_name'])) {
                sendError('ルーム名が指定されていません');
            }

            $roomName = $data['room_name'];
            if (!validateRoomName($roomName)) {
                sendError('無効なルーム名です（使用可能: 英数字、アンダースコア、ハイフン）');
            }

            $roomPath = ROOMS_DIR . $roomName;
            if (file_exists($roomPath)) {
                sendError('このルーム名は既に使用されています');
            }

            // ルームディレクトリの作成
            if (!@mkdir($roomPath, 0755, true)) {
                error_log('ルーム作成失敗: ' . $roomPath . ' - ' . error_get_last()['message']);
                sendError('ルームの作成に失敗しました', 500);
            }

            sendSuccess(['room' => $roomName]);
            break;

        case 'GET':
            // ルームの存在確認
            if (!isset($_GET['room'])) {
                sendError('ルーム名が指定されていません');
            }

            $roomName = $_GET['room'];
            if (!validateRoomName($roomName)) {
                sendError('無効なルーム名です');
            }

            $roomPath = ROOMS_DIR . $roomName;
            if (!file_exists($roomPath)) {
                error_log('ルームが見つかりません: ' . $roomPath);
                sendError('指定されたルームが見つかりません', 404);
            }

            sendSuccess(['exists' => true]);
            break;

        default:
            sendError('未対応のメソッドです', 405);
    }
} catch (Exception $e) {
    error_log('Room API Error: ' . $e->getMessage());
    sendError('サーバーエラーが発生しました: ' . $e->getMessage(), 500);
}