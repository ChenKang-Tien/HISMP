<template>
    <div class="clinic-global-layout" :class="themeClass">
        <div class="topbar">
            <i class="ti ti-building-hospital topbar-icon"></i>
            <div class="logo">
                HISMP 透析資訊系統管理平台
                <span class="role-badge">{{ roleBadgeText }}</span>
            </div>

            <div class="topbar-search">
                <i class="ti ti-search search-icon"></i>
                <input
                    v-model="store.searchQuery"
                    type="text"
                    placeholder="搜尋病患姓名 / 病歷號 / 床號..."
                    class="search-input"
                />
            </div>

            <div class="topbar-right">
                <span class="shift-tag">{{ store.currentShift }}</span>
                <span class="user-badge">
                    <i class="ti ti-user-circle"></i>
                    {{ userName }} {{ roleNameSuffix }}
                </span>
                <button
                    class="logout-btn"
                    @click="handleLogout"
                    title="安全登出系統"
                >
                    <i class="ti ti-logout"></i>
                </button>
            </div>
        </div>

        <div class="announce-bar">
            <i class="ti ti-speakerphone animate-bounce"></i>
            <span class="announce-text">
                院所公告：本週（5/20~5/23）執行常規抽血；衛生所通知開放進階新冠疫苗施打，請協助提醒家屬。
            </span>
        </div>

        <div class="layout-body-viewport">
            <router-view />
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { useDialysisStore } from "@/store/useNurseStore";

const router = useRouter();
const store = useDialysisStore();

// 從本地快取取出權限
const roleId = ref(localStorage.getItem("hismp_role_id") || "5");
const userName = ref(localStorage.getItem("hismp_user_name") || "楚心瑜");

// 根據路由守衛傳遞的 role_id 計算視覺色系 Class
const themeClass = computed(() => {
    if (roleId.value === "4") return "theme-doctor"; // 藍色系
    return "theme-nurse"; // 綠色系
});

// 計算角色小徽章文字
const roleBadgeText = computed(() => {
    if (roleId.value === "4") return "👨‍⚕️ 醫師端 V38";
    if (roleId.value === "1") return "🛠️ 系統管理員";
    return "👩‍⚕️ 護理端 V24";
});

// 計算職稱後綴
const roleNameSuffix = computed(() => {
    if (roleId.value === "4") return "醫師";
    if (roleId.value === "5") return "護理長";
    return "護理師";
});

// 全域安全登出清理機制
const handleLogout = () => {
    if (confirm("確定要安全登出智慧透析資訊平台嗎？")) {
        localStorage.removeItem("hismp_token");
        localStorage.removeItem("hismp_role_id");
        localStorage.removeItem("hismp_user_name");
        router.push("/login");
    }
};
</script>

<style scoped>
/* ════ 全域佈局基礎環境 ════ */
.clinic-global-layout {
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    font-family:
        -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial,
        sans-serif;

    /* 預設護理端青綠色系變數環境 (V24 Design Rules) */
    --theme-primary: #134e4a;
    --theme-border: #14b8a6;
    --theme-badge-bg: rgba(255, 255, 255, 0.18);
}

/* 🩺 醫師端專屬藍色系染色覆寫變數 (V38 Design Rules) */
.clinic-global-layout.theme-doctor {
    --theme-primary: #1e3a8a;
    --theme-border: #1d4ed8;
    --theme-badge-bg: rgba(59, 130, 246, 0.25);
}

/* ════ Topbar 像素級樣式繪製 ════ */
.topbar {
    background: var(--theme-primary);
    border-bottom: 3px solid var(--theme-border);
    padding: 7px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    z-index: 200;
    transition: background-color 0.25s ease;
}
.topbar-icon {
    color: white;
    font-size: 18px;
    flex-shrink: 0;
}
.logo {
    font-size: 15px;
    font-weight: 700;
    color: white;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.role-badge {
    background: var(--theme-badge-bg);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.35);
    font-size: 10px;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 20px;
}
.topbar-search {
    flex: 1;
    max-width: 320px;
    margin: 0 12px;
    position: relative;
}
.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
    pointer-events: none;
}
.search-input {
    width: 100%;
    padding: 6px 10px 6px 32px;
    border-radius: 20px;
    border: 1.5px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.15);
    color: white;
    font-size: 12px;
    outline: none;
    transition: all 0.2s ease;
}
.search-input:focus {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.6);
}
.search-input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}
.topbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}
.shift-tag {
    background: rgba(255, 255, 255, 0.18);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.35);
    font-size: 11px;
    padding: 2px 9px;
    border-radius: 20px;
    font-weight: 600;
}
.user-badge {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    gap: 4px;
}
.logout-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    font-size: 15px;
    padding: 4px;
    display: flex;
    align-items: center;
    transition: color 0.2s;
}
.logout-btn:hover {
    color: #ef4444;
}

/* ════ 公告欄與主體視窗 ════ */
.announce-bar {
    background-color: #f0fdf4; /* 原稿 --green-lt */
    border-bottom: 1.5px solid #86efac; /* 原稿 --green-bd */
    padding: 5px 14px;
    font-size: 11px;
    font-weight: 700;
    color: #15803d; /* 原稿 --green */
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.announce-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.layout-body-viewport {
    flex: 1;
    overflow: hidden;
    position: relative;
    background-color: #f0f4f8;
}

/* 閃爍動畫擴充 */
.animate-bounce {
    animation: bounce 1s infinite;
}
@keyframes bounce {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-3px);
    }
}
</style>
