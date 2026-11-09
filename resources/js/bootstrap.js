import axios from 'axios';
window.axios = axios;

// 設定 Axios 的預設請求標頭，這在未來與 Laravel API 溝通時非常重要
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.request.use((config) => {
    const token =
        sessionStorage.getItem("hismp_token") ||
        localStorage.getItem("hismp_token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            sessionStorage.removeItem("hismp_token");
            localStorage.removeItem("hismp_token");
            if (window.location.pathname !== "/login") {
                window.location.href = "/login";
            }
        }
        return Promise.reject(error);
    },
);