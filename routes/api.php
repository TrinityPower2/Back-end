<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\AttachedTaskController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ToDoListController;
use App\Http\Controllers\Api\TimePreferencesController;
use App\Http\Controllers\Api\IcsImportController;
use App\Http\Controllers\Api\AlgorithmController;
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

# Attached Tasks
Route::post('atasks', [AttachedTaskController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('atasks/{id_task}', [AttachedTaskController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('atasks', [AttachedTaskController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('atasks/{id_task}', [AttachedTaskController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::delete('atasks/{id_task}', [AttachedTaskController::class, 'userDelete']) -> middleware('auth:sanctum');

# Events
Route::post('events', [EventController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('events/{id_event}', [EventController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('events', [EventController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('events/{id_event}', [EventController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::delete('events/{id_event}', [EventController::class, 'userDelete']) -> middleware('auth:sanctum');

# Calendars
Route::post('calendar', [CalendarController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('calendar/{id_calendar}', [CalendarController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('calendar/perday/{id_calendar}', [CalendarController::class, 'userFetchPerDay']) -> middleware('auth:sanctum');
Route::get('calendar/perweek/{id_calendar}', [CalendarController::class, 'userFetchPerWeek']) -> middleware('auth:sanctum');
Route::get('calendar/day/all', [CalendarController::class, 'userFetchDay']) -> middleware('auth:sanctum'); # Get all calendars for a day
Route::get('calendar', [CalendarController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('calendar/{id_calendar}', [CalendarController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::delete('calendar/{id_calendar}', [CalendarController::class, 'userDelete']) -> middleware('auth:sanctum');

# Tasks
Route::post('tasks', [TaskController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::post('tasks/fromname', [TaskController::class, 'userCreateFromName']) -> middleware('auth:sanctum');
Route::get('tasks/{id_task}', [TaskController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('tasks', [TaskController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('tasks/{id_task}', [TaskController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::delete('tasks/{id_task}', [TaskController::class, 'userDelete']) -> middleware('auth:sanctum');
Route::delete('tasks/fromname/{name_task}/{name_todo}', [TaskController::class, 'userDeleteFromNames']) -> middleware('auth:sanctum');

# TodoLists
Route::post('todolist', [ToDoListController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('todolist/{id_todo}', [ToDoListController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('todolist/', [ToDoListController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('todolist/{id_todo}', [ToDoListController::class, 'userEdit']) -> middleware('auth:sanctum');
Route::patch('todolist/mass/{id_todo}', [ToDoListController::class, 'userMassEdit']) -> middleware('auth:sanctum');
Route::patch('todolist/priority/fromname/{name_todo}', [ToDoListController::class, 'userEditPriorityfromName']) -> middleware('auth:sanctum');
Route::delete('todolist/{id_todo}', [ToDoListController::class, 'userDelete']) -> middleware('auth:sanctum');
Route::delete('todolist/fromname/{name_todo}', [ToDoListController::class, 'userDeleteFromName']) -> middleware('auth:sanctum');
# Convert ToDo to Event
Route::post('convertToDoToEvent/{name_todo}', [ToDoListController::class, 'userConvertToEvent']) -> middleware('auth:sanctum');

# TimePreferences
Route::post('timepref', [TimePreferencesController::class, 'userCreate']) -> middleware('auth:sanctum');
Route::get('timepref/{name_timepref}', [TimePreferencesController::class, 'userFetch']) -> middleware('auth:sanctum');
Route::get('timepref', [TimePreferencesController::class, 'userFetchAll']) -> middleware('auth:sanctum');
Route::patch('timepref/{name_timepref}', [TimePreferencesController::class, 'userEdit']) -> middleware('auth:sanctum');

# User : Authentification
Route::post('auth/register', [AuthController::class, 'createUser']);
Route::post('auth/login', [AuthController::class, 'loginUser']);
Route::get('auth/logout', [AuthController::class, 'logoutUser']);

# User : Edit Profile and Passwords
Route::post('auth/recovery', [AuthController::class, 'recoveryUser']);
Route::patch('auth/profile', [AuthController::class, 'userEditProfile']) -> middleware('auth:sanctum');
Route::patch('auth/password', [AuthController::class, 'editUserPassword']) -> middleware('auth:sanctum');

# User : Fetch Profile
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


# Import events from Ics file
Route::post('icsimport', [IcsImportController::class, 'parseIcs']) -> middleware('auth:sanctum');


# Algorithm
Route::post('algorithm', [AlgorithmController::class, 'runAlgorithm']) -> middleware('auth:sanctum');
Route::post('algorithm_loaded', [AlgorithmController::class, 'interfaceAlgorithm']) -> middleware('auth:sanctum');
Route::get('algorithm_confirm', [AlgorithmController::class, 'confirmChanges']) -> middleware('auth:sanctum');
Route::get('algorithm_cancel', [AlgorithmController::class, 'cancelChanges']) -> middleware('auth:sanctum');

# Debug : Ping server
Route::get('ping', function () {
    return response()->json(['pong' => true]);
});


# Remove routes

#Route::post('attachTaskToEvent/{id_task}', [TaskController::class, 'userAttachToEvent']) -> middleware('auth:sanctum');
#Route::post('convertTaskToEvent/{id_task}', [TaskController::class, 'userConvertToEvent']) -> middleware('auth:sanctum');
