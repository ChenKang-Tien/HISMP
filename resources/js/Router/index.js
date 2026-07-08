import { createRouter, createWebHistory } from "vue-router";
import Login from "../views/Login.vue";
import ClinicLayout from "../layouts/ClinicLayout.vue";

import DialysisNursing from "../views/DialysisNursing.vue";
import DialysisDoctor from "../views/DialysisDoctor.vue";

const routes = [
    {
        path: "/",
        redirect: "/login",
    },
    {
        path: "/login",
        name: "login",
        component: Login,
    },
    // 💡 核心外殼：將所有需要共用 ClinicLayout（側邊導覽列）的頁面打包進 children 中！
    {
        path: "/",
        component: ClinicLayout,
        children: [
            {
                path: "doctor",
                name: "doctor-console",
                component: DialysisDoctor,
                // 🔒 補上關鍵的 meta 權限標籤，讓下方的路由守衛可以精準核對
                meta: { requiresRole: "doctor" },
            },
            {
                path: "nurse",
                name: "nurse-console",
                component: DialysisNursing,
                // 🔒 補上關鍵的 meta 權限標籤，護理長與護理師共用護理控制台權限
                meta: { requiresRole: "nurse" },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// ═══════════════════════════════════
// 🛡️ 醫療級路由守衛 (絕對防止無限迴圈安全版)
// ═══════════════════════════════════
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem("hismp_token");

    // 💡 關鍵對齊：從 localStorage 拿出來的是你在 Login.vue 存進去的 role_id
    const roleId = localStorage.getItem("hismp_role_id");

    // 🎯 精準對齊你的 role_id 規則
    const isDoctor = roleId == "4"; // 4 是主治醫師
    const isNurse = roleId == "5" || roleId == "6"; // 5 是護理長, 6 是執業護理師
    const isAdmin = roleId == "1"; // 1 是最高權限

    // 【安全閘門 0】如果目的地本來就是去登入頁，直接放行
    if (to.path === "/login") {
        if (token) {
            if (isDoctor) return next("/doctor");
            if (isNurse || isAdmin) return next("/nurse");
        }
        return next();
    }

    // 1. 未登入攔截
    if (!token) {
        return next("/login");
    }

    // 2. 已登入者的智慧空投 (防止卡在根目錄 "/")
    if (token && to.path === "/") {
        if (isDoctor) return next("/doctor");
        if (isNurse || isAdmin) return next("/nurse");
        return next("/nurse");
    }

    // 3. 雙端權限防護線 (RBAC 身分交叉核對 - 熔斷安全版)
    if (token) {
        // 如果去醫生工作台，但目前不是醫生，也不是去巡視的管理員
        if (to.meta.requiresRole === "doctor" && !isDoctor && !isAdmin) {
            console.warn("⚠️ 權限不足：非醫師身分試圖進入醫師工作台！");
            if (!isNurse) return next("/login"); // 兩邊都不符合的壞快取，直接踢回登入頁熔斷
            return next("/nurse");
        }

        // 如果去護理工作台，但目前既不是護理端，也不是具備視察權的醫生/管理員
        if (
            to.meta.requiresRole === "nurse" &&
            !isNurse &&
            !isDoctor &&
            !isAdmin
        ) {
            console.warn("⚠️ 權限不足：非護理身分試圖進入護理工作台！");
            return next("/login"); // 直接熔斷彈回登入大門
        }
    }

    next();
});

export default router;
