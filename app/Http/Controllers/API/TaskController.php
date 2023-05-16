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

        # we check that the name has not been used in another task of the list
        $name_check = Task::where('name_task', $request->name_task)->where('id_todo', $request->id_todo)->first();
        if ($name_check != null) {
            return response()->json([
                'status' => false,
                'message' => "This name is already used in this list!",
            ], 409);
        }

        if($request->is_done == null){
            $is_done = false;
        } else {
            $is_done = $request->is_done;
        }

        $task = Task::create(
            [
                'name_task'=>$request->name_task,
                'description'=>$request->description,
                'id_todo'=>$request->id_todo,
                'priority_level'=>$request->priority_level,
                'is_done'=> $is_done,
            ]);

        $attached_task = null;

        # We check if the todolist has an id_buddy
        $list = To_do_list::where('id_todo', $request->id_todo)->first();
        if($list->id_buddy != null){
            # If there is a buddy, we need to create an attached task in the buddy's todolist
            $attached_task = AttachedTask::create(
                [
                    'name_task'=>$request->name_task,
                    'description'=>$request->description,
                    'id_todo'=>$list->id_buddy,
                    'priority_level'=>$request->priority_level,
                    'is_done'=> $is_done,
                    'id_buddy'=>$task->id_task,
                ]);

            # We need to set the id_buddy of the task to the id of the attached task
            $task->id_buddy = $attached_task->id_att_task;
            $task->save();
        }


        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'task' => $task,
            'attached_task' => $attached_task
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

        #we check that the name has not been used in another task of the list
        $name_check = Task::where('name_task', $request->name_task)->where('id_todo', $list->id_todo)->first();
        if ($name_check != null) {
            return response()->json([
                'status' => false,
                'message' => "This name is already used in this list!",
            ], 409);
        }

        if($request->is_done == null){
            $is_done = false;
        } else {
            $is_done = $request->is_done;
        }

        $task = Task::create(
            [
                'name_task'=>$request->name_task,
                'description'=>$request->description,
                'id_todo'=>$list->id_todo,
                'priority_level'=>$request->priority_level,
                'is_done'=> $is_done,
            ]);

        $attached_task = null;

        # We check if the todolist has an id_buddy
        if($list->id_buddy != null){
            # If there is a buddy, we need to create an attached task in the buddy's todolist
            $attached_task = AttachedTask::create(
                [
                    'name_task'=>$request->name_task,
                    'description'=>$request->description,
                    'id_todo'=>$list->id_buddy,
                    'priority_level'=>$request->priority_level,
                    'is_done'=> $is_done,
                    'id_buddy'=>$task->id_task,
                ]);

            # We need to set the id_buddy of the task to the id of the attached task
            $task->id_buddy = $attached_task->id_task;
            $task->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'task' => $task,
            'attached_task' => $attached_task
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

        $attached_task = null;

        # We check if the task has an id_buddy
        if($task->id_buddy != null){
            # If there is a buddy, we need to edit the attached task in the buddy's todolist
            $attached_task = AttachedTask::where('id_att_task', $task->id_buddy)->first();

            if($request->name_task != null)
                $attached_task->name_task = $request->name_task;
            if($request->description != null)
                $attached_task->description = $request->description;
            if($request->priority_level != null)
                $attached_task->priority_level = $request->priority_level;
            if($request->is_done !== null)
                $attached_task->is_done = $request->is_done;

            $attached_task->save();
        }

        if($request->name_task != null)
            $task->name_task = $request->name_task;
        if($request->description != null)
            $task->description = $request->description;
        if($request->id_todo != null){
            $verification = DB::table('to_do_lists')->where('id_todo', '=', $request->id_todo)->where('id_users', '=', auth('sanctum')->user()->id)->first();
            if($verification == null){
                return response()->json([
                    'status' => false,
                    'message' => "You don't have access to this list!",
                ], 401);
            }
            $task->id_todo = $request->id_todo;
        }
        if($request->priority_level != null)
            $task->priority_level = $request->priority_level;
        if($request->is_done !== null)
            $task->is_done = $request->is_done;

        $task->save();

        return response()->json([
            'status' => true,
            'message' => "Task Edited successfully!",
            'list' => $task,
            'attached_task' => $attached_task
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

        # We check if the task has an id_buddy
        if($task->id_buddy != null){
            # If there is a buddy, we need to delete the attached task in the buddy's todolist
            $attached_task = AttachedTask::where('id_att_task', $task->id_buddy)->first();
            $attached_task->delete();
        }

        $task->delete();

        return response()->json([
            'status' => true,
            'message' => "Task Deleted successfully!",
        ], 200);
    }

    # Delete a task from the name of the to_do_list and the name of the task
    public function userDeleteFromNames(Request $request, $name_task, $name_todo)
    {
        $list = To_do_list::where('name_todo', $name_todo)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($list == null) {
            return response()->json([
                'status' => false,
                'message' => "List not found!",
            ], 404);
        }

        $task = Task::where('name_task', $name_task)->where('id_todo', $list->id_todo)->first();
        if ($task == null) {
            return response()->json([
                'status' => false,
                'message' => "Task not found!",
            ], 404);
        }

        # We check if the task has an id_buddy
        if($task->id_buddy != null){
            # If there is a buddy, we need to delete the attached task in the buddy's todolist
            $attached_task = AttachedTask::where('id_att_task', $task->id_buddy)->first();
            $attached_task->delete();
        }

        $task->delete();

        return response()->json([
            'status' => true,
            'message' => "Task Deleted successfully!",
        ], 200);
    }


    /**
     * Convert a task to an attached task
     * UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED
     */

    public function userAttachToEvent(Request $request, $id_task){
        $task = $this->taskCheck4xx($request, $id_task);
        // If $task is a response, then it's an error and we return it
        if (get_class($task) == "Illuminate\Http\JsonResponse") {
            return $task;
        }

        if ($task->id_buddy != null) {
            return response()->json([
                'status' => false,
                'message' => "This task already has a buddy!"
            ], 401);
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
                'id_buddy'=>$task->id_task
            ]);

        #$task->delete();

        $task->id_buddy = $attached_task->id_att_task;
        $task->save();


        return response()->json([
            'status' => true,
            'message' => "Task converted successfully!",
            'attached_task' => $attached_task,
            'task' => $task
        ], 200);
    }

    /**
     * Convert a task to an event
     * UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED UNUSED
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


