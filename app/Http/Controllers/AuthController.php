<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json($user, 201);
    }

    public function login(Request $request)
    {
        // 1. 後端驗證前端傳過來的欄位
        $request->validate([
            // 'role'     => 'required|in:doctor,nurse', // 前端頁籤傳入的值
            'email' => 'required',                 // 前端表單的工號/帳號輸入框
            'password' => 'required',
        ]);

        // 💡 核心對齊：因為你的 User 模型是用 email 欄位登入，所以把前端的 username 對齊到 email
        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        // 2. 執行 Laravel 標準安全驗證
        if (\Auth::attempt($credentials)) {

            // 登入成功，把 User 連同他的 Role 一起撈出來
            $user = \App\Models\User::with('role')->findOrFail(\Auth::user()->id);

            // 3. 🛡️ 醫療級權限精準防護
            $isValidRole = false;

            if ($user->role_id == 4) {
                $isValidRole = true; // 確定是醫生
            }
            // 護理長(5) 或 護理師(6) 都允許進入護理控制台
            elseif (($user->role_id == 5 || $user->role_id == 6)) {
                $isValidRole = true;
            }
            // 如果是最高權限(1) 想兩邊巡視，也可以放行
            elseif ($user->role_id == 1) {
                $isValidRole = true;
            }

            // 職責身份不符，立刻踢出去
            if (!$isValidRole) {
                \Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => '醫事憑證錯誤：您的帳號權限與選擇的職責身份（醫生/護理）不符！'
                ], 403);
            }

            // 4. 核發 Sanctum 安全 Token 憑證
            $token = $user->createToken('app_token')->plainTextToken;

            // 5. 回傳精準資料給 Vue 3 前端
            return response()->json([
                'success'   => true,
                'message'   => '醫事憑證驗證成功',
                'token'     => $token,
                'user'      => [
                    'name'     => $user->name,
                    'username' => $user->email, // 對齊帳號
                    'role_id'     => $user->role_id, // 回傳 'doctor' 或 'nurse' 讓前端知道要跳轉去哪
                    'role_name'=> $user->role->name // 例如：護理長、主治醫師
                ],
                'user_id'   => $user->id
            ], 200);
        }

        // 驗證失敗
        return response()->json([
            'success' => false,
            'message' => '工號/帳號或密碼錯誤，請重新輸入。'
        ], 401);
    }
}
