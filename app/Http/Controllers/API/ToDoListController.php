<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\To_do_list;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ToDoListController extends Controller
{
    /**
     * Create a new todolist as a user
     */

    public function userCreate(Request $request)
    {
        $event = To_do_list::create(
            [
                'name_todo'=>$request->name_todo,
                'id_users'=>auth('sanctum')->user()->id
            ]);

        return response()->json([
            'status' => true,
            'message' => "Todolist Created successfully!",
            'list' => $event
        ], 200);
    }

    /**
     * Fetch all task from a todolist belonging to the user
     */

    public function userFetch(Request $request)
    {
        $list = To_do_list::where('id_todo', $request->id_todo)->first();
        if($list == null){
            return response()->json([
                'status' => false,
                'message' => "This todolist does not exist !",
            ], 401);
        }

        if($list->id_users != auth('sanctum')->user()->id){
            return response()->json([
                'status' => false,
                'message' => "This todolist does not belong to you !",
            ], 401);
        }

        $tasks = DB::table('tasks')->where('id_todo', $request->id_todo)->get();

        return response()->json([
            'status' => true,
            'message' => "Todolist fetched successfully!",
            'list' => $list,
            'tasks' => $tasks
        ], 200);
    }

}
