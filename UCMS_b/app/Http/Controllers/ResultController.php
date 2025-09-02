<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    // Lists results: admin sees all, student sees their own
    public function index()
    {
        $q = Result::with(['course:id,name,code', 'user:id,name,email'])->orderByDesc('id');

        if (auth()->user()->role !== 'admin') {
            $q->where('user_id', auth()->id());
        }

        return response()->json($q->get(), 200);
    }

    // Options for selects
    public function options()
    {
        if (auth()->user()->role !== 'admin') {
            // student: only their own user option, and (optionally) all courses (or filter as you like)
            return response()->json([
                'courses'  => Course::select('id','name','code')->orderBy('name')->get(),
                'students' => User::select('id','name','email')->where('id', auth()->id())->get(),
            ], 200);
        }

        return response()->json([
            'courses'  => Course::select('id','name','code')->orderBy('name')->get(),
            'students' => User::select('id','name','email')->where('role','student')->orderBy('name')->get(),
        ], 200);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id'   => 'required|exists:users,id',
            'test_no'   => 'required|integer|min:1',
            'grade'     => 'required|string|max:2', // e.g., A, A+, B
        ]);

        $result = Result::create($data)->load(['course:id,name,code','user:id,name,email']);
        return response()->json($result, 201);
    }

    public function show($id)
    {
        $result = Result::with(['course:id,name,code','user:id,name,email'])->find($id);
        if (!$result) return response()->json(['message' => 'Result not found'], 404);

        if (auth()->user()->role !== 'admin' && $result->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($result, 200);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = Result::find($id);
        if (!$result) return response()->json(['message' => 'Result not found'], 404);

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id'   => 'required|exists:users,id',
            'test_no'   => 'required|integer|min:1',
            'grade'     => 'required|string|max:2',
        ]);

        $result->update($data);
        $result->load(['course:id,name,code','user:id,name,email']);
        return response()->json($result, 200);
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = Result::find($id);
        if (!$result) return response()->json(['message' => 'Result not found'], 404);

        $result->delete();
        return response()->json(['message' => 'Result deleted'], 200);
    }
}
