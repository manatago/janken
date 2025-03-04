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
    error_log('Game API Error: ' . $message);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// 成功レスポンス関数
function sendSuccess($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// じゃんけんの勝敗判定
function judgeGame($choices) {
    if (!is_array($choices)) {
        error_log('Invalid choices: ' . print_r($choices, true));
        return null;
    }

    $results = [];
    $hands = array_values($choices);
    
    // 選択していないプレイヤーがいる場合はnullを返す
    if (in_array('', $hands)) {
        return null;
    }

    // 全員同じ手の場合は引き分け
    if (count(array_unique($hands)) === 1) {
        return ['outcome' => '引き分け', 'choices' => $choices];
    }

    // 勝敗判定ロジック
    $winners = [];
    foreach ($choices as $player => $hand) {
        $isWinner = true;
        foreach ($choices as $otherPlayer => $otherHand) {
            if ($player === $otherPlayer) continue;
            
            // 負けパターンをチェック
            if (
                ($hand === 'グー' && $otherHand === 'パー') ||
                ($hand === 'チョキ' && $otherHand === 'グー') ||
                ($hand === 'パー' && $otherHand === 'チョキ')
            ) {
                $isWinner = false;
                break;
            }
        }
        if ($isWinner) {
            $winners[] = $player;
        }
    }

    if (count($winners) === 0) {
        return ['outcome' => '引き分け', 'choices' => $choices];
    } else if (count($winners) === 1) {
        return ['outcome' => $winners[0] . 'の勝ち！', 'choices' => $choices];
    } else {
        return ['outcome' => implode('と', $winners) . 'の勝ち！', 'choices' => $choices];
    }
}

// プレイヤーの選択をリセット
function resetPlayerChoice($roomPath, $playerName) {
    $playerFile = $roomPath . '/' . $playerName . '.txt';
    if (file_exists($playerFile)) {
        file_put_contents($playerFile, '');
    }
}

// メインの処理
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // リクエストボディの取得
            $requestBody = file_get_contents('php://input');
            error_log('Request Body: ' . $requestBody); // デバッグ用

            $data = json_decode($requestBody, true);
            if (!isset($data['room']) || !isset($data['player'])) {
                sendError('必要なパラメータが不足しています');
            }

            $roomName = $data['room'];
            $playerName = $data['player'];
            $roomPath = ROOMS_DIR . $roomName;

            if (!file_exists($roomPath)) {
                error_log("Room directory not found: " . $roomPath);
                sendError('指定されたルームが見つかりません', 404);
            }

            // リセットリクエストの場合
            if (isset($data['reset']) && $data['reset'] === true) {
                resetPlayerChoice($roomPath, $playerName);
                sendSuccess(['message' => 'リセット完了']);
                break;
            }

            // 選択の保存（空の選択を含む）
            $choice = isset($data['choice']) ? $data['choice'] : '';
            
            // 選択値のバリデーション（選択がある場合のみ）
            if ($choice !== '' && !in_array($choice, ['グー', 'チョキ', 'パー'])) {
                sendError('無効な選択です');
            }

            // プレイヤーの選択を保存
            $playerFile = $roomPath . '/' . $playerName . '.txt';
            if (@file_put_contents($playerFile, $choice) === false) {
                error_log("Failed to write player choice: " . $playerFile . ' - ' . error_get_last()['message']);
                sendError('選択の保存に失敗しました', 500);
            }

            sendSuccess(['message' => '選択を保存しました']);
            break;

        case 'GET':
            if (!isset($_GET['room'])) {
                sendError('ルーム名が指定されていません');
            }

            $roomName = $_GET['room'];
            $roomPath = ROOMS_DIR . $roomName;
            if (!file_exists($roomPath)) {
                error_log("Room directory not found: " . $roomPath);
                sendError('指定されたルームが見つかりません', 404);
            }

            // ルーム内のプレイヤー情報を取得
            $files = glob($roomPath . '/*.txt');
            if ($files === false) {
                error_log("Failed to read room directory: " . $roomPath);
                sendError('ルーム情報の取得に失敗しました', 500);
            }

            $players = [];
            $choices = [];
            $waiting = [];

            foreach ($files as $file) {
                $playerName = basename($file, '.txt');
                $players[] = $playerName;
                
                $choice = @file_get_contents($file);
                if ($choice === false) {
                    error_log("Failed to read player file: " . $file);
                    continue;
                }
                
                $choice = trim($choice);
                $choices[$playerName] = $choice;
                if ($choice === '') {
                    $waiting[] = $playerName;
                }
            }

            // 全員が選択済みかチェック
            $completed = empty($waiting);
            $result = $completed ? judgeGame($choices) : null;

            sendSuccess([
                'players' => $players,
                'waiting' => $waiting,
                'completed' => $completed,
                'result' => $result
            ]);
            break;

        default:
            sendError('未対応のメソッドです', 405);
    }
} catch (Exception $e) {
    error_log("Game API Error: " . $e->getMessage());
    sendError('サーバーエラーが発生しました: ' . $e->getMessage(), 500);
}