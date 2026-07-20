<?php

namespace App\Http\Controllers;

use App\Models\HospitalAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    //
    public function index()
    {
        $hospitalAnnouncements = HospitalAnnouncement::with('establish')
            ->where('deleted', false)
            ->where('date_to', '>=', date('Y-m-d'))
            ->orderBy('date_from', 'desc')
            ->get();
        return response()->json([
            'status' => 200,
            'announcements' => $hospitalAnnouncements
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'content' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $data['deleted'] = false;
        $data['establish_id'] = Auth::user()->id;
        

        $announcement = HospitalAnnouncement::create($data);

        return response()->json([
            'status' => 200,
            'message' => '公告新增成功',
            'announcement' => $announcement
        ]);
    }

    public function show($id)
    {
        $announcement = HospitalAnnouncement::with('establish')->findOrFail($id);

        return response()->json([
            'status' => 200,
            'announcement' => $announcement
        ]);
    }

    public function update(Request $request, $id)
    {
        $announcement = HospitalAnnouncement::findOrFail($id);

        $data = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'content' => 'required|string',
            'note' => 'nullable|string',
        ]);

        // 更新（保留 establish_id & deleted）
        $data['establish_id'] = Auth::user()->id;
        $announcement->update($data);

        return response()->json([
            'status' => 200,
            'message' => '公告更新成功',
            'announcement' => $announcement
        ]);
    }

    public function destroy($id)
    {
        $announcement = HospitalAnnouncement::findOrFail($id);
        $announcement->deleted = true;
        $announcement->save();

        return response()->json([
            'status' => 200,
            'message' => '公告已刪除'
        ]);
    }
}
