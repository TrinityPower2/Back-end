<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ToDoListController;
use App\Http\Controllers\Api\TimePreferencesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('events', [EventController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('events/{id_event}', [EventController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('events', [EventController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('events/{id_event}', [EventController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::delete('events/{id_event}', [EventController::class, 'userDelete']) -> middleware('auth:sanctum');

Route::post('calendar/create', [CalendarController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('calendar/{id_calendar}', [CalendarController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('calendar', [CalendarController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('calendar/{id_calendar}', [CalendarController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::delete('calendar/{id_calendar}', [CalendarController::class, 'userDelete']) -> middleware('auth:sanctum');


Route::post('tasks', [TaskController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('tasks/{id_task}', [TaskController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('tasks', [TaskController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('tasks/{id_task}', [TaskController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::delete('tasks/{id_task}', [TaskController::class, 'userDelete']) -> middleware('auth:sanctum');

Route::post('todolist/create', [ToDoListController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('todolist/{id_todo}', [ToDoListController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('todolist/', [ToDoListController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('todolist/{id_todo}', [ToDoListController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::delete('todolist/{id_todo}', [ToDoListController::class, 'userDelete']) -> middleware('auth:sanctum');


Route::post('timepref', [TimePreferencesController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('timepref/{name_timepref}', [TimePreferencesController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('timepref', [TimePreferencesController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('timepref', [TimePreferencesController::class, 'userEdit']) -> middleware('auth:sanctum');


Route::post('auth/register', [AuthController::class, 'createUser']);
Route::post('auth/login', [AuthController::class, 'loginUser']);
Route::get('auth/logout', [AuthController::class, 'logoutUser']);
Route::get('auth/user', [AuthController::class, 'user']) -> middleware('auth:sanctum');



Route::get('ping', function () {
    return response()->json(['pong' => true]);
});
