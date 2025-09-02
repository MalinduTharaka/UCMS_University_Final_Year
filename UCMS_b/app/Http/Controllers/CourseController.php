<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CourseController extends Controller
{
    public function index()
    {
        if (Auth::user()->role == 'admin') {
            $courses = Course::all();
        } else {
            $courses = Auth::user()->assignedCourses;
        }
        return response()->json($courses, 200);
    }

    public function create(Request $request)
    {
        if (Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:courses,code',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:0,1',
        ]);

        $data = [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'status' => (int) $validated['status'],
        ];

        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('images/courses'), $imageName);
            $data['image'] = 'images/courses/' . $imageName;
        }

        $course = Course::create($data);
        return response()->json(['message' => 'Course created', 'course' => $course], 201);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $course = Course::find($id);
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:courses,code,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:0,1',
        ]);

        $data = [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'status' => (int) $validated['status'],
        ];

        if ($request->hasFile('image')) {
            if ($course->image && file_exists(public_path($course->image))) {
                @unlink(public_path($course->image));
            }
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('images/courses'), $imageName);
            $data['image'] = 'images/courses/' . $imageName;
        }

        $course->update($data);
        return response()->json(['message' => 'Course updated', 'course' => $course], 200);
    }

    public function delete($id)
    {
        if (Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $course = Course::find($id);
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        if ($course->image && file_exists(public_path($course->image))) {
            @unlink(public_path($course->image));
        }

        $course->delete();
        return response()->json(['message' => 'Course deleted'], 200);
    }


    public function getContent($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $contents = CourseContent::where('course_id', $id)->get();

        return response()->json($contents, 200);
    }

    public function addContent(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $course = Course::find($id);
        if (!$course)
            return response()->json(['error' => 'Course not found'], 404);

        $validated = $request->validate([
            'title' => 'required|string',
            'file' => 'required|file|max:20480', // 20MB
        ]);

        $dir = public_path('uploads/contents/' . $id);
        File::ensureDirectoryExists($dir);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move($dir, $filename);

        $relPath = 'uploads/contents/' . $id . '/' . $filename;
        $mime = File::mimeType(public_path($relPath));
        $type = str_starts_with($mime, 'image/') ? 'image'
            : ($mime === 'application/pdf' ? 'pdf'
                : (str_starts_with($mime, 'video/') ? 'video' : 'other'));

        $content = CourseContent::create([
            'course_id' => $id,
            'title' => $validated['title'],
            'path' => $relPath,
            'type' => $type,
        ]);

        return response()->json(['message' => 'Content added', 'content' => $content], 201);
    }

    public function updateContent(Request $request, $contentId)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $content = CourseContent::find($contentId);
        if (!$content)
            return response()->json(['error' => 'Content not found'], 404);

        $validated = $request->validate([
            'title' => 'required|string',
            'file' => 'nullable|file|max:20480',
        ]);

        $data = ['title' => $validated['title']];

        if ($request->hasFile('file')) {
            if ($content->path && file_exists(public_path($content->path))) {
                @unlink(public_path($content->path));
            }

            $dir = public_path('uploads/contents/' . $content->course_id);
            File::ensureDirectoryExists($dir);

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($dir, $filename);
            $relPath = 'uploads/contents/' . $content->course_id . '/' . $filename;

            $mime = File::mimeType(public_path($relPath));
            $type = str_starts_with($mime, 'image/') ? 'image'
                : ($mime === 'application/pdf' ? 'pdf'
                    : (str_starts_with($mime, 'video/') ? 'video' : 'other'));

            $data['path'] = $relPath;
            $data['type'] = $type;
        }

        $content->update($data);
        return response()->json(['message' => 'Content updated', 'content' => $content], 200);
    }

    public function deleteContent($contentId)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $content = CourseContent::find($contentId);
        if (!$content)
            return response()->json(['error' => 'Content not found'], 404);

        if ($content->path && file_exists(public_path($content->path))) {
            @unlink(public_path($content->path));
        }
        $content->delete();

        return response()->json(['message' => 'Content deleted'], 200);
    }


}
