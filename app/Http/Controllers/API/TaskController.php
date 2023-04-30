<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use App\Models\AttachedTask;
use App\Models\Event;
use App\Models\To_do_list;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class TaskController extends Controller
{
    private function taskCheck4xx(Request $request, $id_task)
    {
        $task = Task::where('id_task', $id_task)->first();
        if ($task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }
        $list = To_do_list::where('id_todo', $task->id_todo)->first();
        if ($list == null) {
            return response()->json([
                'status' => false,
                'message' => "List not found!",
            ], 404);
        }
        $index = DB::table('to_do_lists')->where('id_todo', '=', $list->id_todo)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if ($index == null) {
            return response()->json([
                'status' => false,
                'message' => "The list doesn't belong to you!",
            ], 401);
        }

        return $task;
    }


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


        $task = Task::create(
            [
                'name_task'=>$request->name_task,
                'description'=>$request->description,
                'id_todo'=>$request->id_todo,
                'priority_level'=>$request->priority_level,
                'is_done'=> false
            ]);

        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'list' => $task
        ], 200);
    }

    # Create a task from the name of the to_do_list
    public function userCreateFromName(Request $request)
    {
        $list = To_do_list::where('name_todo', $request->name_todo)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($list == null) {
            return response()->json([
                'status' => false,
                'message' => "List not found!",
            ], 404);
        }

        $task = Task::create(
            [
                'name_task'=>$request->name_task,
                'description'=>$request->description,
                'id_todo'=>$list->id_todo,
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
     * Fetch a belonging to the user
     */

    public function userFetch(Request $request, $id_task)
    {
        $task = $this->taskCheck4xx($request, $id_task);
        // If $task is a response, then it's an error and we return it
        if (get_class($task) == "Illuminate\Http\JsonResponse") {
            return $task;
        }

        return response()->json([
            'status' => true,
            'message' => "Task fetched successfully!",
            'list' => $task
        ], 200);
    }

    /**
     * Fetch all tasks  belonging to the user
     */

    public function userFetchAll(Request $request)
    {
        $tasks = DB::table('tasks')->join('to_do_lists', 'tasks.id_todo', '=', 'to_do_lists.id_todo')->where('to_do_lists.id_users', '=', auth('sanctum')->user()->id)->get();
        return response()->json([
            'status' => true,
            'message' => "Tasks fetched successfully!",
            'list' => $tasks
        ], 200);
    }


    /**
     * Edit a task as a user.
     */

    public function userEdit(Request $request, $id_task)
    {
        $task = $this->taskCheck4xx($request, $id_task);
        // If $task is a response, then it's an error and we return it
        if (get_class($task) == "Illuminate\Http\JsonResponse") {
            return $task;
        }

        if($request->name_task != null)
            $task->name_task = $request->name_task;
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
     * Delete a task as a user.
     */

    public function userDelete(Request $request, $id_task)
    {
        $task = $this->taskCheck4xx($request, $id_task);
        // If $task is a response, then it's an error and we return it
        if (get_class($task) == "Illuminate\Http\JsonResponse") {
            return $task;
        }

        $task->delete();

        return response()->json([
            'status' => true,
            'message' => "Task Deleted successfully!",
        ], 200);
    }

    /**
     * Convert a task to an attached task
     */

    public function userAttachToEvent(Request $request, $id_task){
        $task = $this->taskCheck4xx($request, $id_task);
        // If $task is a response, then it's an error and we return it
        if (get_class($task) == "Illuminate\Http\JsonResponse") {
            return $task;
        }

        $event = DB::table('events')->where('id_event', '=', $request->id_event)->first();
        if($event == null){
            return response()->json([
                'status' => false,
                'message' => "Event not found!",
            ], 404);
        }

        $attached_task = AttachedTask::create(
            [
                'name_task'=>$task->name_task,
                'description'=>$task->description,
                'id_todo'=>$event->id_event,
                'priority_level'=>$task->priority_level,
                'is_done'=> false,
            ]);

        $task->delete();

        return response()->json([
            'status' => true,
            'message' => "Task converted successfully!",
            'attached_task' => $attached_task
        ], 200);
    }

    /**
     * Convert a task to an event
     */
    public function userConvertToEvent(Request $request, $id_task){
        $task = $this->taskCheck4xx($request, $id_task);
        // If $task is a response, then it's an error and we return it
        if (get_class($task) == "Illuminate\Http\JsonResponse") {
            return $task;
        }

        $calendar = DB::table('calendars')->where('id_calendar', '=', $request->id_calendar)->first();

        $event = Event::create(
            [
                'name_event'=>$task->name_task,
                'description'=>$task->description,
                'start_date'=> Carbon::create($request->start_date),
                'length'=> $request->length,
                'movable'=> false,
                'id_calendar'=>$request->id_calendar,
                'priority_level'=>$task->priority_level,
                'to_repeat'=> false,
                'color'=> $calendar->color,
            ]);

        $task->delete();

        return response()->json([
            'status' => true,
            'message' => "Task converted successfully!",
            'event' => $event
        ], 200);
    }

}


