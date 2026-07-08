<template>
    <div class="hismp-app-root">
        <router-view></router-view>
    </div>
</template>

<script setup>
// 使用 Vue 3 Composition API 標準架構
</script>

<style>
/* ═══════════════════════════════════════════════════
  ✨ 全局樣式初始化（完美繼承您原本精緻 HTML 的 Slate 灰色調底色）
  ═══════════════════════════════════════════════════
*/
:root {
    --slate: #475569;
    --slate-lt: #94a3b8;
    --slate-ul: #f1f5f9;
    --slate-bg: #f0f4f8; /* 您原本靜態檔中最舒適的灰藍色底 */
    --white: #ffffff;
    --border: #e2e8f0;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body,
html {
    width: 100%;
    height: 100%;
    font-family:
        -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue",
        Arial, "Noto Sans TC", sans-serif;
    background-color: var(--slate-bg); /* 全院共用的 Slate 灰藍色底層 */
    color: #1e293b;
    overflow: hidden; /* 確保最外層不出現多餘的網頁雙重滾動條 */
}

/* 撐滿瀏覽器畫面的核心容器 */
.hismp-app-root {
    width: 100%;
    height: 100vh;
    display: flex;
    flex-direction: column;
}

/* 精緻的全局滾動條 (Scrollbar) 視覺微調 */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
