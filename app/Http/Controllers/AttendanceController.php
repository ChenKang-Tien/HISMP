<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $records = Attendance::with(['user', 'substitute'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $users = User::where('deleted', 0)->get();

        return response()->json([
            'status' => 200,
            'attendance' => $records,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'date'          => 'required|date',
            'leave_type'    => 'required|string|max:50',
            'reason'        => 'nullable|string|max:500',
            'substitute_id' => 'nullable|exists:users,id',
            'notes'         => 'nullable|string|max:500',
        ]);

        $attendance = Attendance::create($validated);

        return response()->json([
            'status' => 200,
            'message' => '出缺勤記錄新增成功',
            'data' => $attendance->load(['user', 'substitute']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'date'          => 'required|date',
            'leave_type'    => 'required|string|max:50',
            'reason'        => 'nullable|string|max:500',
            'substitute_id' => 'nullable|exists:users,id',
            'notes'         => 'nullable|string|max:500',
        ]);

        $attendance->update($validated);

        return response()->json([
            'status' => 200,
            'message' => '出缺勤記錄更新成功',
            'data' => $attendance->load(['user', 'substitute']),
        ]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'status' => 200,
            'message' => '出缺勤記錄已刪除',
        ]);
    }
}
