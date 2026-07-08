Markdown
# Laravel 11 + Vue 3 + MySQL 8.0 Docker 一體化全棧開發環境

本專案使用 Docker Compose 部署，將 **Laravel 11 後端**、**Vue 3 前端 (透過 Vite 打包)** 以及 **MySQL 8.0 資料庫** 整合於單一容器架構中。前端 Vue 3 直接內嵌於 Laravel 的 Blade 視圖中，透過 Vite 實現容器雙向熱更新 (HMR)。

---

## 📂 專案目錄結構

```text
hismp/
├── docker-compose.yml     # Docker 容器編排設定
├── Dockerfile             # PHP 8.4 + Node.js 混合環境鏡像構建檔
├── package.json           # 前端依賴與 Vite 指令
├── vite.config.js         # Vite 打包與 HMR 熱更新設定
├── app/                   # Laravel 後端核心程式
├── routes/web.php         # 後端路由 (全倒向前端)
├── resources/
│   ├── js/
│   │   ├── app.js         # Vue 3 進入點
│   │   └── Components/    # Vue 元件目錄 (如 App.vue)
│   └── views/
│       └── app.blade.php  # 承載 Vue 的主模板
└── public/                # 靜態資源與 Vite 編譯產出地
🛠️ 第一階段：環境初始化與構建
1. 清空目錄並初始化 Laravel 專案
由於 Composer 限制目標目錄必須為空，需先清空目錄後再下載 Laravel 11 核心檔案：

Bash
# 清空當前目錄 (含隱藏檔)
rm -rf * .[^.]* 2>/dev/null

# 使用官方 Composer 鏡像直接在根目錄生成 Laravel 專案
docker run --rm -v $(pwd):/app -w /app composer:2 create-project laravel/laravel .
2. 設定專案設定檔
① Dockerfile
建立自定義的混合環境，確保解決 Laravel 11 依賴套件所需的 PHP 8.4 限制，並內建 Node.js：

Dockerfile
FROM php:8.4-cli

# 一次性安裝前端與資料庫必備環境
RUN apt-get update && apt-get install -y \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql

WORKDIR /app
② docker-compose.yml
YAML
services:
  webserver:
    build: .
    container_name: laravel-integrated-app
    user: "root"
    ports:
      - "8000:8000"  # Laravel 服務埠
      - "5173:5173"  # Vite HMR 埠
    volumes:
      - .:/app
    working_dir: /app
    environment:
      - DB_HOST=database
      - DB_PORT=3306
      - DB_DATABASE=laravel_db
      - DB_USERNAME=laravel_user
      - DB_PASSWORD=laravel_password
    command: php artisan serve --host=0.0.0.0 --port=8000
    depends_on:
      - database
    networks:
      - app-network

  database:
    image: mysql:8.0
    container_name: mysql-database
    ports:
      - "3306:3306"
    environment:
      - MYSQL_ROOT_PASSWORD=root_password
      - MYSQL_DATABASE=laravel_db
      - MYSQL_USER=laravel_user
      - MYSQL_PASSWORD=laravel_password
    volumes:
      - db-data:/var/lib/mysql
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  db-data:
    driver: local
③ .env (Laravel 環境變數)
修改專案根目錄下的 .env 檔案，確保資料庫主機指向 Docker 內部的服務名稱 database：

程式碼片段
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_password
🚀 第二階段：啟動容器與安裝 Vue 3
1. 建立並啟動 Docker 服務
Bash
docker compose up -d --build
2. 安裝 Vue 3 與 Vite 插件
Bash
docker compose exec webserver npm install vue @vitejs/plugin-vue
3. 修正 Linux 檔案所有權權限 (VS Code SSH 存檔失敗必用)
由於容器內部以 root 身份安裝套件，會導致外部一般使用者 (hcis) 無法儲存變更。請在主機執行：

Bash
sudo chown -R hcis:hcis /home/hcis/hismp
🎨 第三階段：前後端程式碼整合設定
1. 調整 vite.config.js
JavaScript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: { base: null, includeAbsolute: false },
            },
        }),
    ],
    server: {
        host: '0.0.0.0', // 允許外部/ZeroTier 穿透連線
        hmr: { host: 'localhost' },
    },
});
2. 建立 Vue 3 測試元件 (resources/js/Components/App.vue)
程式碼片段
<template>
  <div style="padding: 40px; font-family: sans-serif; text-align: center;">
    <h1 style="color: #41B883;">🚀 Laravel 11 + Vue 3 整合成功！</h1>
    <button @click="count++" style="padding: 10px 20px; font-size: 16px; background-color: #41B883; color: white; border: none; border-radius: 5px; cursor: pointer;">
      測試點擊次數：{{ count }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const count = ref(0)
</script>
3. 修改進入點與路由
resources/js/app.js: 掛載 Vue 到主畫面上。

JavaScript
import './bootstrap';
import { createApp } from 'vue';
import App from './Components/App.vue';
createApp(App).mount('#app');
resources/views/app.blade.php: 建立承載用的 HTML 骨架。

HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Vue3 App</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
routes/web.php: 將後端網頁路由全權交由前端處理。

PHP
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
🏃 日常開發常用指令
1. 執行資料庫遷移 (Migration)
Bash
docker compose exec webserver php artisan migrate
2. 啟動前端 Vite 熱更新監聽 (開發時必跑)
Bash
docker compose exec webserver npm run dev
啟動後即可透過瀏覽器造訪：http://你的伺服器IP:8000

3. 正式上線前端靜態打包 (Production)
Bash
docker compose exec webserver npm run build
4. 關閉所有容器服務
Bash
docker compose down