// import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";
import router from "./Router"; // 💡 引入路由
import { createPinia } from "pinia"; // 👈 引入 Pinia

// 💡 引入 Element Plus 及其樣式
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";

const app = createApp(App);
const pinia = createPinia(); // 👈 1. 實例化 Pinia

app.use(ElementPlus); // 💡 啟用 UI 組件庫

// ════ ✨ 關鍵修正區：嚴格的金字塔生命週期順序 ════
app.use(pinia); // 👈 2. ✨ 必須最先註冊 Pinia！讓全域狀態大腦醒過來
app.use(router); // 👈 3. 接著才註冊路由（這樣路由組件內部才能安全呼叫 store）

app.mount("#app"); // 👈 4. 最後正式渲染掛載到 HTML 節點
