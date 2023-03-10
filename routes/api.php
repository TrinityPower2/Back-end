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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

#Route::post('tasks', [TaskController::class], 'store') -> middleware('auth:sanctum');
#Route::get('tasks', [TaskController::class], 'indexUser') -> middleware('auth:sanctum');
#Route::apiResource('tasks', TaskController::class) -> middleware('auth:sanctum');

Route::post('events/create', [EventController::class, 'userCreate']) -> middleware('auth:sanctum');

Route::post('tasks/create', [TaskController::class, 'userCreate']) -> middleware('auth:sanctum');

Route::post('calendar/create', [CalendarController::class, 'userCreate']) -> middleware('auth:sanctum');

Route::post('todolist/create', [ToDoListController::class, 'userCreate']) -> middleware('auth:sanctum');

Route::post('timepref/create', [TimePreferencesController::class, 'userCreate']) -> middleware('auth:sanctum');

#Route::get('events/trigger', [EventController::class, 'algorithm']) -> middleware('auth:sanctum');
#Route::get('events/setup', [EventController::class, 'reccurent_setup']) -> middleware('auth:sanctum');
#Route::apiResource('events', EventController::class) -> middleware('auth:sanctum');

Route::post('auth/register', [AuthController::class, 'createUser']);
Route::post('auth/login', [AuthController::class, 'loginUser']);
Route::get('auth/logout', [AuthController::class, 'logoutUser']);

Route::get('ping', function () {
    return response()->json(['pong' => true]);
});
