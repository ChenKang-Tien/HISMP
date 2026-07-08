<?php

use Illuminate\Support\Facades\Route;

// 🏥 前端萬用門戶：除了開頭是 /api 的請求，其他網址全部交給前端 Vue 3 處理
Route::get('/{any}', function () {
    return view('app'); // 指向你的 Vue 載入地基
})->where('any', '^(?!api).*$');