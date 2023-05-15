<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use App\Models\AttachedTask;
use App\Models\AttachedToDoList;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class AttachedTaskController extends Controller
{

    private function checkBelongingByList($id_att_todo,$id_user)
    {
        $attachedlist = DB::table('attached_to_do_lists')->where('id_att_todo', '=', $id_att_todo)->first();
        $event = DB::table('events')->where('id_event', '=', $attachedlist->id_event)->first();
        $calendar = DB::table('calendars')->where('id_calendar', '=', $event->id_calendar)->first();
        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $calendar->id_calendar)->where('id_users', '=', $id_user)->first();
        return $verification;
    }

    private function checkBelongingByEvent($id_event,$id_user)
    {
        $event = DB::table('events')->where('id_event', '=', $id_event)->first();
        $calendar = DB::table('calendars')->where('id_calendar', '=', $event->id_calendar)->first();
        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $calendar->id_calendar)->where('id_users', '=', $id_user)->first();
        return $verification;
    }


    /**
     * Create a new task as a user.
     */

    public function userCreate(Request $request)
    {
        // We get the id of the attached list
        $verification = $this->checkBelongingByEvent($request->id_event, auth('sanctum')->user()->id);

        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }

        //Get the attached list of this event
        $attachedlist = DB::table('attached_to_do_lists')->where('id_event', '=', $request->id_event)->first();

        $att_task = AttachedTask::create(
            [
                'name_task'=>$request->name_task,
                'description'=>$request->description,
                'id_todo'=>$attachedlist->id_att_todo,
                'priority_level'=>$request->priority_level,
                'is_done'=> false
            ]);

        # We check if the attached_todo has an id_buddy
        if($attachedlist->id_buddy != null){
            # We create a new task in the buddy todolist
            $task = Task::create(
                [
                    'name_task'=>$request->name_task,
                    'description'=>$request->description,
                    'id_todo'=>$attachedlist->id_buddy,
                    'priority_level'=>$request->priority_level,
                    'is_done'=> false,
                    'id_buddy'=>$att_task->id_att_task,
                ]);
            # We set the id_buddy of the attached task
            $att_task->id_buddy = $task->id_task;
            $att_task->save();
        }


        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'att_task' => $att_task,
            'task' => $task
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

        $verification = $this->checkBelongingByList($task->id_todo, auth('sanctum')->user()->id);

        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => "Task fetched successfully!",
            'task' => $task
        ], 200);
    }


    public function userFetchAll(Request $request){

        //We have to get all the attached lists of the user
        $attachedlists = DB::table('attached_to_do_lists')->get();
        $tasks = array();
        foreach($attachedlists as $attachedlist){
            $verification = $this->checkBelongingByList($attachedlist->id_att_todo, auth('sanctum')->user()->id);
            if($verification != null){
                $tasks = array_merge($tasks, DB::table('attached_tasks')->where('id_todo', '=', $attachedlist->id_att_todo)->get()->toArray());
            }
        }


        return response()->json([
            'status' => true,
            'message' => "Tasks fetched successfully!",
            'tasks' => $tasks
        ], 200);
    }


    /**
     * Edit an attached task as a user.
     */

    public function userEdit(Request $request, $id_task)
    {
        $att_task = AttachedTask::where('id_att_task', $id_task)->first();
        if ($att_task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }

        $verification = $this->checkBelongingByList($att_task->id_todo, auth('sanctum')->user()->id);
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }

        $task = null;

        # We check if the task has an id_buddy
        if($att_task->id_buddy != null){
            # If there is a buddy, we need to edit the attached task in the buddy's todolist
            $task = Task::where('id_task', $att_task->id_buddy)->first();

            if($request->name_task != null)
                $task->name_task = $request->name_task;
            if($request->description != null)
                $task->description = $request->description;
            if($request->priority_level != null)
                $task->priority_level = $request->priority_level;
            if($request->is_done !== null)
                $task->is_done = $request->is_done;

            $task->save();
        }

        if($request->name_task != null)
            $att_task->name_task = $request->name_task;
        if($request->description != null)
            $att_task->description = $request->description;
        if($request->priority_level != null)
            $att_task->priority_level = $request->priority_level;
        if($request->is_done !== null)
            $att_task->is_done = $request->is_done;

        $att_task->save();

        return response()->json([
            'status' => true,
            'message' => "Task Edited successfully!",
            'att_task' => $att_task,
            'task' => $task
        ], 200);
    }

    /**
     * Delete an attached task as a user.
     */

    public function userDelete(Request $request, $id_task)
    {
        $att_task = AttachedTask::where('id_att_task', $id_task)->first();
        if ($att_task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }

        $verification = $this->checkBelongingByList($att_task->id_todo, auth('sanctum')->user()->id);

        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        }

        # We check if the task has an id_buddy
        if($att_task->id_buddy != null){
            # If there is a buddy, we need to delete the attached task in the buddy's todolist
            $task = Task::where('id_task', $att_task->id_buddy)->first();
            $task->delete();
        }

        $att_task->delete();

        return response()->json([
            'status' => true,
            'message' => "Task Deleted successfully!",
        ], 200);
    }


}
