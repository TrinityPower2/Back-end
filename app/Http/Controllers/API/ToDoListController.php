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

    private function listCheck4xx(Request $request, $id_todo)
    {
        $list = To_do_list::where('id_todo', $id_todo)->first();
        if ($list == null) {
            return response()->json([
                'status' => false,
                'message' => "List not found!",
            ], 404);
        }
        if($list->id_users != auth('sanctum')->user()->id){
            return response()->json([
                'status' => false,
                'message' => "This todolist does not belong to you !",
            ], 401);
        }

        return $list;
    }

    /**
     * Create a new todolist as a user
     */

    public function userCreate(Request $request)
    {

        # We check that the names has not been used in another todolist of the user
        $list = To_do_list::where('name_todo', $request->name_todo)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($list != null) {
            return response()->json([
                'status' => false,
                'message' => "This name is already used in another todolist of yours!",
            ], 409);
        }

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

    public function userFetch(Request $request, $id_todo)
    {
        $list = $this->listCheck4xx($request, $id_todo);
        // If $list is a response, then it's an error and we return it
        if(get_class($list) == "Illuminate\Http\JsonResponse") {
            return $list;
        }

        $tasks = DB::table('tasks')->where('id_todo', $id_todo)->get();

        return response()->json([
            'status' => true,
            'message' => "Todolist fetched successfully!",
            'list' => $list,
            'tasks' => $tasks
        ], 200);
    }

    /**
     * Fetch all tasks of every todolist belonging to the user
     */
    public function userFetchAll()
    {
        $calendars = DB::table('to_do_lists')
            ->where('to_do_lists.id_users', auth('sanctum')->user()->id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => "Calendars fetched successfully!",
            'calendars' => $calendars
        ], 200);
    }

    /**
     * Update a todolist belonging to the user
     */

    public function userEdit(Request $request, $id_todo)
    {
        $list = $this->listCheck4xx($request, $id_todo);
        // If $list is a response, then it's an error and we return it
        if(get_class($list) == "Illuminate\Http\JsonResponse") {
            return $list;
        }

        if($request->name_todo != null)
            $list->name_todo = $request->name_todo;

        $list->save();

        return response()->json([
            'status' => true,
            'message' => "Todolist updated successfully!",
            'list' => $list
        ], 200);
    }

    /**
     * Delete a todolist belonging to the user
     */

    public function userDelete(Request $request, $id_todo)
    {
        $list = $this->listCheck4xx($request, $id_todo);
        // If $list is a response, then it's an error and we return it
        if(get_class($list) == "Illuminate\Http\JsonResponse") {
            return $list;
        }

        # We delete all the tasks of the todolist
        DB::table('tasks')->where('id_todo', $id_todo)->delete();

        $list->delete();

        return response()->json([
            'status' => true,
            'message' => "Todolist deleted successfully!",
        ], 200);
    }

}
