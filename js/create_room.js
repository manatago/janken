document.addEventListener('DOMContentLoaded', () => {
    // Cookie確認
    const cookies = document.cookie.split(';').reduce((acc, cookie) => {
        const [key, value] = cookie.trim().split('=');
        acc[key] = value;
        return acc;
    }, {});

    // 管理者確認
    if (cookies.is_admin !== 'true' || !cookies.player_name) {
        window.location.href = './index.php';
        return;
    }

    const roomForm = document.getElementById('roomForm');
    const roomNameInput = document.getElementById('roomName');
    const roomUrlDisplay = document.getElementById('roomUrlDisplay');
    const roomUrlInput = document.getElementById('roomUrl');
    const copyUrlButton = document.getElementById('copyUrl');

    roomForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const roomName = roomNameInput.value.trim();
        if (!roomName) {
            alert('ルーム名を入力してください');
            return;
        }

        try {
            const response = await fetch('./api/room.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ room_name: roomName })
            });

            if (!response.ok) {
                throw new Error('ルームの作成に失敗しました');
            }

            const data = await response.json();
            if (data.success) {
                // 現在のURLからベースURLを取得
                const baseUrl = window.location.href.split('index.php')[0];
                // ルームURLを生成（index.phpを含める）
                const roomUrl = `${baseUrl}index.php?room=${encodeURIComponent(roomName)}`;
                roomUrlInput.value = roomUrl;
                roomUrlDisplay.style.display = 'block';
                roomForm.style.display = 'none';
            } else {
                alert(data.message || 'ルームの作成に失敗しました');
            }
        } catch (error) {
            alert('エラーが発生しました: ' + error.message);
        }
    });

    // URLコピー機能
    copyUrlButton.addEventListener('click', () => {
        roomUrlInput.select();
        document.execCommand('copy');
        alert('URLをクリップボードにコピーしました');
    });
});