document.addEventListener('DOMContentLoaded', function() {
    // 桜の花びらを生成する要素を作成
    const sakuraContainer = document.createElement('div');
    sakuraContainer.className = 'sakura-container';
    document.body.appendChild(sakuraContainer);

    // 花びらの数
    const numberOfPetals = 20;

    // 花びらを生成
    for (let i = 0; i < numberOfPetals; i++) {
        createPetal();
    }

    // 定期的に新しい花びらを生成
    setInterval(createPetal, 3000);

    function createPetal() {
        const petal = document.createElement('span');
        petal.className = 'sakura';
        
        // ランダムな位置とアニメーション時間を設定
        const startPositionLeft = Math.random() * 100;
        const animationDuration = 10 + Math.random() * 5;
        const animationDelay = Math.random() * 5;
        const scale = 0.5 + Math.random() * 0.5;

        petal.style.left = startPositionLeft + 'vw';
        petal.style.animationDuration = animationDuration + 's';
        petal.style.animationDelay = animationDelay + 's';
        petal.style.transform = `scale(${scale})`;

        sakuraContainer.appendChild(petal);

        // アニメーション終了後に要素を削除
        setTimeout(() => {
            petal.remove();
        }, (animationDuration + animationDelay) * 1000);
    }
});