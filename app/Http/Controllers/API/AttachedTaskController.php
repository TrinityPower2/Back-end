<?php

namespace App\Http\Controllers\API;

use App\Models\AttachedTask;
use App\Models\AttachedToDoList;
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
        // We get the id of the attached list
        // We get the id of the event contained in the attached list

        $attachedlist = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $request->id_todo)->first();
        $event = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $attachedlist->id_event)->first();
        $calendar = DB::table('calendar_belong_tos')->where('id_calendar', '=', $event->id_calendar)->first();
        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $calendar->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();

        //$verification1 = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $request->id_todo)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        //$verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $request->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();

        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }


        $task = AttachedTask::create(
            [
                'name_task'=>$request->name_task,
                'date_day'=> new Carbon($request->date_day),
                'description'=>$request->description,
                'id_todo'=>$attachedlist->id_attached_todo,
                'priority_level'=>$request->priority_level,
                'is_done'=> false
            ]);

        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'list' => $task
        ], 200);
    }

    /**
     * Fetch an attached task belonging to the user
     */

    public function userFetch(Request $request, $id_task)
    {
        $task = AttachedTask::where('id_att_task', $id_task)->first();
        if ($task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }

        $attachedlist = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $task->id_todo)->first();
        $event = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $attachedlist->id_event)->first();
        $calendar = DB::table('calendars')->where('id_calendar', '=', $event->id_calendar)->first();
        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $calendar->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => "Task fetched successfully!",
            'list' => $task
        ], 200);
    }

    /**
     * Fetch all attached tasks belonging to the user
     */
    /*
    public function userFetchAll(Request $request){
        $tasks = DB::table('attached_tasks')->join('to_do_lists', 'tasks.id_todo', '=', 'to_do_lists.id_todo')->where('to_do_lists.id_users', '=', auth('sanctum')->user()->id)->get();
        return response()->json([
            'status' => true,
            'message' => "Tasks fetched successfully!",
            'list' => $tasks
        ], 200);
    }
    */


    /**
     * Edit an attached task as a user.
     */

    public function userEdit(Request $request, $id_task)
    {
        $task = AttachedTask::where('id_att_task', $id_task)->first();
        if ($task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }

        $attachedlist = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $task->id_todo)->first();
        $event = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $attachedlist->id_event)->first();
        $calendar = DB::table('calendars')->where('id_calendar', '=', $event->id_calendar)->first();
        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $calendar->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }

        if($request->name_task != null)
            $task->name_task = $request->name_task;
        if($request->date_day != null)
            $task->date_day = new Carbon($request->date_day);
        if($request->description != null)
            $task->description = $request->description;
        if($request->id_todo != null)
            $task->id_todo = $request->id_todo;
        if($request->priority_level != null)
            $task->priority_level = $request->priority_level;
        if($request->is_done != null)
            $task->is_done = $request->is_done;

        $task->save();

        return response()->json([
            'status' => true,
            'message' => "Task Edited successfully!",
            'list' => $task
        ], 200);
    }

    /**
     * Delete an attached task as a user.
     */

    public function userDelete(Request $request, $id_task)
    {
        $task = AttachedTask::where('id_att_task', $id_task)->first();
        if ($task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }

        $attachedlist = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $task->id_todo)->first();
        $event = DB::table('attached_to_do_lists')->where('id_attached_todo', '=', $attachedlist->id_event)->first();
        $calendar = DB::table('calendars')->where('id_calendar', '=', $event->id_calendar)->first();
        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $calendar->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();
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
