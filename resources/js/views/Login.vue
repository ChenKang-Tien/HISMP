<template>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="ti ti-building-hospital"></i>
                </div>
                <h2>泰安診所</h2>
                <p>智慧血液透析系統 (HISMP)</p>
            </div>

            <form @submit.prevent="handleLogin" class="login-form">
                <div class="form-group">
                    <label
                        ><i class="ti ti-user"></i> 醫護人員帳號 (Email)</label
                    >
                    <input
                        type="email"
                        v-model="form.email"
                        placeholder="請輸入醫護人員電子郵件"
                        required
                        :disabled="isLoading"
                    />
                </div>

                <div class="form-group">
                    <label><i class="ti ti-lock"></i> 密碼</label>
                    <input
                        type="password"
                        v-model="form.password"
                        placeholder="請輸入密碼"
                        required
                        :disabled="isLoading"
                    />
                </div>

                <div v-if="errorMessage" class="error-msg">
                    <i class="ti ti-alert-circle"></i> {{ errorMessage }}
                </div>

                <button type="submit" class="submit-btn" :disabled="isLoading">
                    <span v-if="isLoading">
                        <i class="ti ti-loader quarter-spin"></i> 驗證身分中...
                    </span>
                    <span v-else>
                        安全登入 <i class="ti ti-arrow-right"></i>
                    </span>
                </button>
            </form>

            <div class="dev-test-zone">
                <div class="dev-divider">
                    <span>⚙️ 開發期快捷測試通道</span>
                </div>
                <div class="dev-buttons">
                    <button
                        @click="quickLogin('doctor', 4)"
                        class="test-btn doc-test"
                        :disabled="isLoading"
                    >
                        👨‍⚕️ 模擬醫師登入 (Teal)
                    </button>
                    <button
                        @click="quickLogin('nurse', 5)"
                        class="test-btn nurse-test"
                        :disabled="isLoading"
                    >
                        👩‍⚕️ 模擬護理師登入 (Blue)
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const isLoading = ref(false);
const errorMessage = ref("");
const rememberMe = ref(true); // 🎯 記住帳號的狀態開關

// 💡 關鍵修正：後端原生 Auth 看的是 email，將變數名稱與輸入框對齊
const form = reactive({
    email: "",
    password: "",
});

// 🏥 頁面一載入時，檢查以前有沒有存過帳號
onMounted(() => {
    const savedEmail = localStorage.getItem("hismp_remembered_email");
    if (savedEmail) {
        form.email = savedEmail;
        rememberMe.value = true; // 自動幫他把勾勾勾起來
    }
});

// ═══════════════════════════════════
// 1. 標準 API 登入邏輯（完美對接新版後端與路由守衛）
// ═══════════════════════════════════
const handleLogin = async () => {
    isLoading.value = true;
    errorMessage.value = "";

    try {
        // 🚀 呼叫後端 Sanctum 原生認證 API
        const response = await axios.post("/api/v1/login", {
            email: form.email,
            password: form.password,
        });

        // 💡 關鍵解構：對齊後端吐出的真實結構 { success: true, token: "...", user: {...}, user_id: 1 }
        if (response.data.success) {
            const { token, user, user_id } = response.data;

            // 💾 記錄本次登入成功的 Email
            localStorage.setItem("hismp_remembered_email", form.email);

            // 🔒 將金鑰與醫護身份安全存入瀏覽器儲存庫 (全面對齊新版路由守衛的 Key)
            localStorage.setItem("hismp_token", token);
            localStorage.setItem("hismp_user_id", user_id);
            localStorage.setItem("hismp_user_name", user.name);
            localStorage.setItem("hismp_role_id", user.role_id); // 存入 'doctor' 或 'nurse'
            localStorage.setItem("hismp_role_name", user.role_name); // 存入 '主治醫師' 或 '護理長'
            // 🏥 根據後端判定後的真實角色字串，精準分流跳轉
            if (user.role_id === 4) {
                router.push("/doctor"); // 順暢滑入醫生控制台
            } else if (user.role_id == 5 || user.role_id == 6) {
                router.push("/nurse"); // 順暢滑入護理控制台
            } else {
                // 保留管理員或其他身份彈性，預設去護理端
                router.push("/nurse");
            }
        }
    } catch (error) {
        console.error("登入出錯:", error);
        if (error.response && error.response.data) {
            // 抓取後端 AuthController 回傳的 'message' 錯誤訊息
            errorMessage.value =
                error.response.data.message || "帳號或密碼錯誤，請重新輸入。";
        } else {
            errorMessage.value =
                "系統連線異常，請確保後端 Docker 容器正常點火。";
        }
    } finally {
        isLoading.value = false;
    }
};

// ═══════════════════════════════════
// 2. ⚡ 快捷開發測試登入（模擬真實後端回傳的 Session 結構）
// ═══════════════════════════════════
const quickLogin = (roleName, roleId) => {
    isLoading.value = true;
    setTimeout(() => {
        // 模擬後端核發的偽金鑰與結構
        console.log(roleId);
        if (roleId == 4) {
            form.email = "doctor1@gmail.com";
            form.password = "12345678";
        } else {
            form.email = "nurse1@gmail.com";
            form.password = "12345678";
        }

        handleLogin();
    }, 400);
};
</script>

<style scoped>
/* ═══════════════════════════════════
   登入頁專用：承襲醫護端 Slate 設計語言 (保持你原汁原味的漂亮 CSS)
   ═══════════════════════════════════ */
.login-wrapper {
    width: 100vw;
    height: 100vh;
    background: #f0f4f8;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: "Noto Sans TC", sans-serif;
}

.login-card {
    background: #ffffff;
    width: 100%;
    max-width: 400px;
    padding: 35px 30px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(71, 85, 105, 0.08);
    border: 1px solid #e2e8f0;
}

.login-header {
    text-align: center;
    margin-bottom: 28px;
}

.logo-icon {
    width: 56px;
    height: 56px;
    background: #0f766e;
    color: white;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28px;
    margin: 0 auto 12px auto;
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.25);
}

.login-header h2 {
    font-size: 22px;
    color: #1e293b;
    margin: 0;
    font-weight: 700;
    letter-spacing: 1px;
}

.login-header p {
    font-size: 13px;
    color: #64748b;
    margin: 4px 0 0 0;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    font-size: 12px;
    color: #475569;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.form-group input {
    height: 42px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 0 12px;
    font-size: 14px;
    color: #1e293b;
    background: #f8fafc;
    outline: none;
    transition: all 0.2s ease;
}

.form-group input:focus {
    border-color: #0f766e;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
}

.error-msg {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    color: #b91c1c;
    font-size: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.submit-btn {
    height: 44px;
    background: #0f766e;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
    margin-top: 5px;
}

.submit-btn:hover:not(:disabled) {
    background: #0d615a;
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.dev-test-zone {
    margin-top: 25px;
}

.dev-divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin-bottom: 12px;
}

.dev-divider::before,
.dev-divider::after {
    content: "";
    flex: 1;
    border-bottom: 1px dashed #cbd5e1;
}

.dev-divider span {
    padding: 0 10px;
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
}

.dev-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.test-btn {
    height: 34px;
    border: 1px solid;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s ease;
}

.test-btn.doc-test {
    border-color: #99f6e4;
    color: #0f766e;
}
.test-btn.doc-test:hover {
    background: #f0fdfa;
}

.test-btn.nurse-test {
    border-color: #bfdbfe;
    color: #1e40af;
}
.test-btn.nurse-test:hover {
    background: #eff6ff;
}

.quarter-spin {
    animation: spin 1s linear infinite;
    display: inline-block;
}
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
