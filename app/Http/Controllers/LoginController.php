<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // 1. 嚴格盤查前端 Vue 傳過來的欄位
        $request->validate([
            'username' => 'required|string', // 舊庫的登入帳號欄位
            'password' => 'required|string',
        ]);

        // 2. 深入大寫 HCIS 庫的 users 表，精準搜捕該帳號
        // 💡 提示：如果你的舊 users 表登入帳號欄位叫 'account' 或 'user_id'，請把下面的 'username' 換掉
        $user = User::where('username', $request->username)->first();

        // 3. 帳密核對防禦
        // 💡 提示：如果你的舊資料庫密碼是「明文」或特殊的 MD5 加密，請把 Hash::check 改成你舊系統的驗證方式！
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => '🏥 認證失敗：醫護人員帳號或密碼輸入錯誤！'
            ], 401);
        }

        // 4. 通過驗證！調用舊庫裡本來就有的 personal_access_tokens 機制發放金鑰
        // 這裡會建立一個名為 'hismp-token' 的安全金鑰
        $token = $user->createToken('hismp-token')->plainTextToken;

        // 5. 大獲全勝！將 Token 與醫護人員的角色（role）打包吐給前端 Vue 3
        // 前端 Vue 拿到 role（例如 'doctor' 或 'nurse'）就能光速進行介面路由分流！
        return response()->json([
            'status' => 'success',
            'message' => '✨ 醫護人員登入成功！安全防線已開通。',
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'role_id' => $user->role->id, // 舊庫裡區分 醫師/護理師 的角色欄位
                'role_name' => $user->role->name
            ]
        ], 200);
    }
}
