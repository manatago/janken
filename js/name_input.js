document.addEventListener('DOMContentLoaded', () => {
    const nameForm = document.getElementById('nameForm');
    const playerNameInput = document.getElementById('playerName');

    nameForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const playerName = playerNameInput.value.trim();
        if (!playerName) {
            alert('名前を入力してください');
            return;
        }

        // Cookieに名前を保存（1時間有効）
        document.cookie = `player_name=${encodeURIComponent(playerName)};max-age=3600;path=/`;

        // URLパラメータを確認
        const urlParams = new URLSearchParams(window.location.search);
        const adminKey = urlParams.get('key');
        const room = urlParams.get('room');

        if (adminKey) {
            // 管理者の場合
            document.cookie = 'is_admin=true;max-age=3600;path=/';
            // ルーム作成ページへリダイレクト
            window.location.href = './index.php';
        } else if (room) {
            // 既存のルームに参加する場合
            // ゲームページへリダイレクト（現在のURLを再読み込み）
            window.location.reload();
        } else {
            // 通常の参加者（不正なアクセス）
            alert('無効なURLです');
            window.location.href = './index.php';
        }
    });

    // 既存の名前があれば入力欄に設定
    const existingName = document.cookie
        .split('; ')
        .find(row => row.startsWith('player_name='))
        ?.split('=')[1];
    
    if (existingName) {
        playerNameInput.value = decodeURIComponent(existingName);
    }
});