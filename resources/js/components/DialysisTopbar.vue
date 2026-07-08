<template>
    <div
        class="topbar"
        :class="{ 'role-doctor-bg': isDoctor, 'role-nurse-bg': isNurse }"
    >
        <i class="ti ti-building-hospital topbar-icon"></i>
        <div class="logo">
            HISMP 智慧血液透析資訊管理平台
            <span
                class="role-badge"
                :class="{ 'badge-blue': isDoctor, 'badge-teal': isNurse }"
            >
                {{ roleTitle }}
            </span>
        </div>

        <div class="topbar-search" id="topbar-search-wrap">
            <i class="ti ti-search search-icon"></i>
            <input
                v-model="searchQueryModel"
                type="text"
                placeholder="搜尋病患姓名 / 病歷號 / 床號..."
                class="search-input"
            />
        </div>

        <div class="topbar-right">
            <span class="shift-tag">{{ currentShiftText }}</span>
            <span class="user-badge">
                <i class="ti ti-user-circle"></i>
                {{ userName }} {{ userJobTitle }}
            </span>
            <button
                class="logout-btn-link"
                @click="handleLogout"
                title="安全登出"
            >
                <i class="ti ti-logout"></i> 登出
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

// 只有當 Pinia 已經在 app.use 啟用後才能安全呼叫
const store = useDialysisStore();

// 1. 從 LocalStorage 抓取權限與人員基本資料
const roleId = localStorage.getItem("hismp_role_id") || "6";
const userName = localStorage.getItem("hismp_user_name") || "楚心瑜";

const isDoctor = computed(() => roleId == "4");
const isNurse = computed(() => roleId == "5" || roleId == "6");

// 2. 智慧角色與職稱動態計算
const roleTitle = computed(() => {
    if (isDoctor.value) return "👨‍⚕️ 醫師巡房端 V38";
    if (roleId == "5") return "👩‍⚕️ 護理長督導端";
    return "👩‍⚕️ 護理臨床端 V24";
});

const userJobTitle = computed(() => {
    if (isDoctor.value) return "醫師";
    if (roleId == "5") return "護理長";
    return "護理師";
});

const currentShiftText = computed(() => {
    return isDoctor.value ? "今日總班別" : store.currentShift;
});

// 3. 安全的搜尋大腦防線：如果是護理端，與 Pinia Store 對齊；否則用本地變數隔離，避免醫師端噴錯
const localSearch = ref("");
const searchQueryModel = computed({
    get() {
        return isNurse.value ? store.searchQuery : localSearch.value;
    },
    set(val) {
        if (isNurse.value) {
            store.searchQuery = val;
        } else {
            localSearch.value = val;
        }
    },
});

// 4. 登出清理機制
const handleLogout = () => {
    if (confirm("確認要安全登出透析系統並關閉當前工作站嗎？")) {
        localStorage.clear();
        window.location.href = "/login";
    }
};
</script>

<style scoped>
.topbar {
    padding: 7px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    z-index: 200;
    transition: background-color 0.25s ease;
}

/* ✨ 關鍵角色配色防線 (Design Rules 1.1 絕對不可搞混原則) */
.role-doctor-bg {
    background: #1e3a8a; /* 醫師端深藍色 */
    border-bottom: 3px solid #1d4ed8;
}
.role-nurse-bg {
    background: #134e4a; /* 護理端青綠色 */
    border-bottom: 3px solid #14b8a6;
}

.topbar-icon {
    color: white;
    font-size: 18px;
    flex-shrink: 0;
}
.logo {
    font-size: 14px;
    font-weight: 700;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}
.role-badge {
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 20px;
}
.badge-blue {
    background: rgba(29, 78, 216, 0.4);
    border: 1px solid #bfdbfe;
}
.badge-teal {
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.35);
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
    gap: 12px;
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
.logout-btn-link {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: rgba(255, 255, 255, 0.8);
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}
.logout-btn-link:hover {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    border-color: #ef4444;
}
</style>
