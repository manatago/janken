document.addEventListener('DOMContentLoaded', () => {
    const adminNameForm = document.getElementById('adminNameForm');
    const adminNameInput = document.getElementById('adminName');

    adminNameForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const adminName = adminNameInput.value.trim();
        if (!adminName) {
            alert('名前を入力してください');
            return;
        }

        // Cookieに管理者名と管理者フラグを保存（1時間有効）
        document.cookie = `player_name=${encodeURIComponent(adminName)};max-age=3600;path=/`;
        document.cookie = 'is_admin=true;max-age=3600;path=/';

        // ルーム作成ページへリダイレクト
        window.location.href = './index.php';
    });

    // すでに管理者としてログインしている場合は、ルーム作成ページへリダイレクト
    const cookies = document.cookie.split(';').reduce((acc, cookie) => {
        const [key, value] = cookie.trim().split('=');
        acc[key] = value;
        return acc;
    }, {});

    if (cookies.is_admin === 'true' && cookies.player_name) {
        window.location.href = './index.php';
    }

    // 既存の名前があれば入力欄に設定
    const existingName = cookies.player_name;
    if (existingName) {
        adminNameInput.value = decodeURIComponent(existingName);
    }
});