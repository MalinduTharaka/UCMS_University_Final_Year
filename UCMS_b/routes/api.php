<?php

use App\Http\Controllers\AssignController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ResultController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());

    // Courses
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses/store', [CourseController::class, 'create']);
    Route::put('/courses/update/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/delete/{id}', [CourseController::class, 'delete']);
    Route::get('/courses/content/{id}', [CourseController::class, 'getContent']);
    Route::post('/add-content/{id}', [CourseController::class, 'addContent']);
    Route::put('/update-content/{id}', [CourseController::class, 'updateContent']);
    Route::delete('/delete-content/{id}', [CourseController::class, 'deleteContent']);

    // Results
    Route::get('/results', [ResultController::class, 'index']);
    Route::get('/results/options', [ResultController::class, 'options']);
    Route::post('/results/store', [ResultController::class, 'store']);
    Route::get('/results/show/{id}', [ResultController::class, 'show']);
    Route::put('/results/update/{id}', [ResultController::class, 'update']);
    Route::delete('/results/delete/{id}', [ResultController::class, 'destroy']);

    // Assigns
    Route::get('/assigns', [AssignController::class, 'index']);
    Route::get('/assigns/options', [AssignController::class, 'options']);
    Route::post('/assigns/store', [AssignController::class, 'create']);
    Route::put('/assigns/update/{id}', [AssignController::class, 'update']);
    Route::delete('/assigns/delete/{id}', [AssignController::class, 'delete']);


    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Public auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);