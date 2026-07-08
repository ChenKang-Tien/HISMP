import axios from 'axios';
window.axios = axios;

// 設定 Axios 的預設請求標頭，這在未來與 Laravel API 溝通時非常重要
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';