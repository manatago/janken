# じゃんけんゲーム

クラスみんなで遊べるオンラインじゃんけんゲーム

## システム要件

- PHP 8.0以上
- Apache（mod_rewrite不要）
- ファイルの書き込み権限

## 重要な注意点

URLには必ず `index.php` を含めてください。
```
OK: https://tagomori.biz/janken/index.php?key=abc123
NG: https://tagomori.biz/janken/?key=abc123
```

この制限は、共有サーバーでmod_rewriteが使用できない環境でも動作させるためのものです。

## インストール

1. ファイルをサーバーにアップロード
```bash
# FTPなどでファイルをアップロード
public_html/janken/
  ├── index.php
  ├── config.php
  ├── .htaccess
  └── その他のファイル
```

2. パーミッション設定
```bash
# 基本のパーミッション設定
chmod 644 *.php
chmod 644 .htaccess
chmod 644 .env
chmod 755 api/
chmod 755 js/
chmod 755 css/
chmod 755 templates/

# roomsディレクトリに書き込み権限を付与
chmod 755 rooms/
```

3. 環境設定
```bash
# .env.product.exampleをコピーして設定
cp .env.product.example .env

# .envの内容を編集
APP_URL=https://tagomori.biz/janken  # サイトのURL
APP_ENV=production                    # 本番環境
ADMIN_SECRET_KEY=abc123              # 管理者キー（変更推奨）
ROOMS_DIR=rooms                      # 相対パスで指定
```

## 使い方

### 管理者（教師）

1. 管理者用URLにアクセス
```
https://tagomori.biz/janken/index.php?key=admin_abc123
```

2. 管理者名を入力

3. ルーム作成
   - ルーム名を入力（英数字とハイフン、アンダースコアのみ使用可）
   - 生成されたURLを生徒に共有
   - 生成されるURL例: https://tagomori.biz/janken/index.php?room=room1

### 生徒

1. 共有されたURLにアクセス
   - 必ずindex.phpを含むURLを使用してください
   - 例: https://tagomori.biz/janken/index.php?room=room1

2. 名前を入力して参加
3. じゃんけんの手を選択
4. 全員の選択が完了すると結果が表示

## トラブルシューティング

### "404 Not Found" が発生する場合

1. URLの確認
```
# 正しいURL形式
https://tagomori.biz/janken/index.php?key=admin_abc123
https://tagomori.biz/janken/index.php?room=room1

# 誤ったURL形式
https://tagomori.biz/janken/?key=admin_abc123
https://tagomori.biz/janken/?room=room1
```

2. その他の確認事項
- ファイルの存在確認
```bash
ls -la
# 必要なファイルがすべてあるか確認
```

3. パーミッションの確認
```bash
ls -la
# 以下の権限になっているか確認
# -rw-r--r-- (644) : 通常のファイル
# drwxr-xr-x (755) : ディレクトリ
```

### エラーログの確認

エラーが発生した場合は、以下のログを確認してください：
```bash
tail -f php-error.log
```

## セキュリティ注意事項

1. ADMIN_SECRET_KEYの設定
- デフォルトの`abc123`は必ず変更してください
- 推測されにくい値を使用してください

2. パーミッション
- roomsディレクトリ以外は書き込み権限を付与しないでください
- .envファイルは644（rw-r--r--）に設定してください

3. エラー表示
- 本番環境では`APP_ENV=production`に設定してください

## ファイル構成
```
janken/
├── .env                # 環境設定（644）
├── .htaccess          # Apache設定（644）
├── index.php          # メインスクリプト（644）
├── config.php         # 共通設定（644）
├── api/               # APIディレクトリ（755）
├── js/               # JavaScriptファイル（755）
├── css/              # スタイルシート（755）
├── templates/        # HTMLテンプレート（755）
└── rooms/            # ルームデータ（755）
```

## 開発者向け注意事項

今後の機能追加時に気をつけるべきポイント：

### 1. 共有サーバー環境の制約

- mod_rewriteに依存しない実装を心がける
  - すべてのURLに明示的に`index.php`を含める
  - .htaccessは最小限の設定のみ使用

- サーバー設定の制限を考慮
  - PHPディレクティブの変更は避ける
  - .htaccessでの高度な設定は避ける
  - ファイルパーミッションは必要最小限に

### 2. URL設計

- すべてのリダイレクト処理で`index.php`を明示
  - JavaScript内での`window.location.href`
  - PHPでのリダイレクト
  - formのaction属性

- URL生成時の注意点
  - ベースURLは環境変数から取得
  - クエリパラメータは適切にエンコード
  - 相対パスと絶対パスの使い分け

### 3. エラーハンドリング

- エラーログの集中管理
  - すべてのエラーを`php-error.log`に記録
  - 本番環境ではエラー表示を無効化
  - エラーメッセージは具体的に記録

- ユーザーへのフィードバック
  - エラー時は適切なメッセージを表示
  - 本番環境では詳細エラーを隠蔽
  - リカバリー手段を提供

### 4. 設定管理

- 環境変数の利用
  - 環境依存の設定は.envで管理
  - サンプル設定は.env.exampleに記載
  - 本番設定は.env.product.exampleに記載

- 設定値の検証
  - 必須項目のチェック
  - フォーマットの検証
  - デフォルト値の提供

### 5. ファイル構成

- 機能追加時のディレクトリ構成
  - 関連ファイルは適切なディレクトリに配置
  - パーミッションは役割に応じて設定
  - 共通処理は適切に分離

- 命名規則
  - ファイル名は機能を反映
  - 一貫性のある命名
  - わかりやすい構造化

### 6. セキュリティ

- ファイルアクセス制御
  - 設定ファイルは直接アクセス不可
  - データディレクトリは適切に保護
  - 実行権限は必要な場合のみ付与

- 入力検証
  - すべてのユーザー入力を検証
  - XSS対策の実施
  - SQLインジェクション対策（DB使用時）

### 7. デバッグ

- ローカル開発環境
  - エラー表示を有効化
  - 詳細なログ出力
  - デバッグモードの提供

- 本番環境
  - エラーログの活用
  - ユーザーフレンドリーなエラー表示
  - トラブルシューティングガイドの提供

これらの点に注意を払うことで、共有サーバー環境でも安定して動作する拡張性の高いアプリケーションを維持できます。

## ライセンス

MIT License