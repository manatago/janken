document.addEventListener('DOMContentLoaded', async () => {
    // DOM要素の取得
    const playerNameElement = document.getElementById('playerName');
    const roomNameElement = document.getElementById('roomName');
    const choiceSection = document.getElementById('choiceSection');
    const waitingSection = document.getElementById('waitingSection');
    const resultSection = document.getElementById('resultSection');
    const playersList = document.getElementById('playersList');
    const resultContent = document.getElementById('resultContent');
    const playAgainBtn = document.getElementById('playAgainBtn');

    // URLパラメータとCookieの取得
    const urlParams = new URLSearchParams(window.location.search);
    const roomName = urlParams.get('room');
    const playerName = decodeURIComponent(document.cookie
        .split('; ')
        .find(row => row.startsWith('player_name='))
        ?.split('=')[1] || '');

    if (!roomName || !playerName) {
        window.location.href = './index.php';
        return;
    }

    // 画面の初期化
    playerNameElement.textContent = playerName;
    roomNameElement.textContent = roomName;

    let hasChosen = false;
    let isGameActive = true;
    let statusCheckTimeout = null;

    // プレイヤーファイルの初期化
    try {
        const response = await fetch('./api/game.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                room: roomName,
                player: playerName
            })
        });

        if (!response.ok) {
            console.error('プレイヤー初期化エラー:', await response.text());
            window.location.href = './index.php';
            return;
        }
    } catch (error) {
        console.error('プレイヤー初期化エラー:', error);
        window.location.href = './index.php';
        return;
    }

    // じゃんけんの手の選択
    document.querySelectorAll('.choice-btn').forEach(button => {
        button.addEventListener('click', async () => {
            if (hasChosen) return;

            const choice = button.dataset.choice;
            try {
                const response = await fetch('./api/game.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        room: roomName,
                        player: playerName,
                        choice: choice
                    })
                });

                if (!response.ok) {
                    throw new Error('選択の送信に失敗しました');
                }

                hasChosen = true;
                choiceSection.style.display = 'none';
                waitingSection.style.display = 'block';

            } catch (error) {
                console.error('選択エラー:', error);
                alert('エラーが発生しました: ' + error.message);
            }
        });
    });

    // ゲームの状態をチェック
    async function checkGameStatus() {
        if (!isGameActive) return;

        try {
            const response = await fetch(`./api/game.php?room=${encodeURIComponent(roomName)}`);
            if (!response.ok) {
                throw new Error('ゲーム状態の取得に失敗しました');
            }

            const data = await response.json();
            
            if (data.success) {
                // プレイヤーリストの更新
                updatePlayersList(data.players, data.waiting);

                // 全員が選択済みの場合
                if (data.completed && data.result) {
                    isGameActive = false;
                    displayResult(data.result);
                    return;
                }

                // 次の状態チェックをスケジュール
                scheduleNextCheck();
            }
        } catch (error) {
            console.error('状態チェックエラー:', error);
            // エラー時も次のチェックをスケジュール
            scheduleNextCheck();
        }
    }

    // 次の状態チェックをスケジュール
    function scheduleNextCheck() {
        if (statusCheckTimeout) {
            clearTimeout(statusCheckTimeout);
        }
        if (isGameActive) {
            statusCheckTimeout = setTimeout(checkGameStatus, 3000);
        }
    }

    // プレイヤーリストの更新
    function updatePlayersList(players, waiting) {
        playersList.innerHTML = players.map(player => {
            const status = waiting.includes(player) ? '⏳ 選択中...' : '✅ 選択済み';
            return `<li>${player}: ${status}</li>`;
        }).join('');
    }

    // 結果の表示
    function displayResult(result) {
        waitingSection.style.display = 'none';
        resultSection.style.display = 'block';

        let resultHTML = '<div class="result-details">';
        resultHTML += '<h3>全員の選択:</h3>';
        resultHTML += '<ul>';
        for (const [player, choice] of Object.entries(result.choices)) {
            resultHTML += `<li>${player}: ${choice}</li>`;
        }
        resultHTML += '</ul>';
        resultHTML += `<h3>結果: ${result.outcome}</h3>`;
        resultHTML += '</div>';

        resultContent.innerHTML = resultHTML;
    }

    // もう一度遊ぶ
    playAgainBtn.addEventListener('click', async () => {
        try {
            // サーバー側で選択をリセット
            const response = await fetch('./api/game.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    room: roomName,
                    player: playerName,
                    reset: true
                })
            });

            if (!response.ok) {
                throw new Error('リセットに失敗しました');
            }

            // 画面の状態をリセット
            hasChosen = false;
            isGameActive = true;
            resultSection.style.display = 'none';
            choiceSection.style.display = 'block';
            waitingSection.style.display = 'none';

            // 状態チェックを再開
            checkGameStatus();

        } catch (error) {
            console.error('リセットエラー:', error);
            alert('エラーが発生しました: ' + error.message);
            // エラー時はトップページへ
            window.location.href = './index.php';
        }
    });

    // 初回の状態チェックを開始
    checkGameStatus();
});