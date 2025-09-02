<?php

// app/Http/Controllers/AssignController.php
namespace App\Http\Controllers;

use App\Models\CourseAssign;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // include course & user for table display
        $assigns = CourseAssign::with([
            'course:id,name,code',
            'user:id,name,email'
        ])->orderByDesc('id')->get();

        return response()->json($assigns, 200);
    }

    // Select options for UI: all courses + students
    public function options()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $courses  = Course::select('id','name','code')->orderBy('name')->get();
        $students = User::select('id','name','email')
                        ->where('role','student') // adjust if different
                        ->orderBy('name')->get();

        return response()->json(['courses' => $courses, 'students' => $students], 200);
    }

    public function create(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'nullable|date',
        ]);

        // prevent duplicate assignment
        $exists = CourseAssign::where('course_id', $data['course_id'])
            ->where('user_id', $data['user_id'])->exists();
        if ($exists) {
            return response()->json(['error' => 'Already assigned'], 409);
        }

        $assign = CourseAssign::create($data)->load([
            'course:id,name,code',
            'user:id,name,email'
        ]);

        return response()->json(['message' => 'Assigned', 'assign' => $assign], 201);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $assign = CourseAssign::find($id);
        if (!$assign)
            return response()->json(['error' => 'Assignment not found'], 404);

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'nullable|date',
        ]);

        // avoid duplicating another row with same pair
        $dup = CourseAssign::where('course_id', $data['course_id'])
            ->where('user_id', $data['user_id'])
            ->where('id', '!=', $assign->id)
            ->exists();
        if ($dup) {
            return response()->json(['error' => 'Already assigned'], 409);
        }

        $assign->update($data);
        $assign->load(['course:id,name,code', 'user:id,name,email']);

        return response()->json(['message' => 'Updated', 'assign' => $assign], 200);
    }

    public function delete($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $assign = CourseAssign::find($id);
        if (!$assign)
            return response()->json(['error' => 'Assignment not found'], 404);

        $assign->delete();
        return response()->json(['message' => 'Deleted'], 200);
    }
}
