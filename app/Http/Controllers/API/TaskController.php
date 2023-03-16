<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use App\Models\To_do_list;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Create a new task as a user.
     */

    public function userCreate(Request $request)
    {

        $verification = DB::table('to_do_lists')->where('id_todo', '=', $request->id_todo)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }


        $event = Task::create(
            [
                'name_task'=>$request->name_task,
                'date_day'=> new Carbon($request->date_day),
                'description'=>$request->description,
                'id_todo'=>$request->id_todo,
            ]);

        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'list' => $event
        ], 200);
    }

    /**
     * Edit a task as a user.
     */

    public function userEdit(Request $request)
    {
        $task = Task::where('id_task', $request->id_task)->first();
        if ($task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }

        $verification = DB::table('to_do_lists')->where('id_todo', '=', $task->id_todo)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }

        $task->name_task = $request->name_task;
        $task->date_day = new Carbon($request->date_day);
        $task->description = $request->description;
        $task->id_todo = $request->id_todo;

        $task->save();

        return response()->json([
            'status' => true,
            'message' => "Task Edited successfully!",
            'list' => $event
        ], 200);
    }

    /**
     * Delete a task as a user.
     */

    public function userDelete(Request $request)
    {
        $task = Task::where('id_task', $request->id_task)->first();
        if ($task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }

        $verification = DB::table('to_do_lists')->where('id_todo', '=', $task->id_todo)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }

        $task->delete();

        return response()->json([
            'status' => true,
            'message' => "Task Deleted successfully!",
        ], 200);
    }


}
