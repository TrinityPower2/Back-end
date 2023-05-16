<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\To_do_list;
use App\Models\AttachedToDoList;
use App\Models\Task;
use App\Models\AttachedTask;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

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

    # Edit a todolist by name, and replace the linked tasks by the new ones sent in the request in a json array
    public function userMassEdit(Request $request, $name_todo)
    {
        $list = To_do_list::where('name_todo', $name_todo)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($list == null) {
            return response()->json([
                'status' => false,
                'message' => "List not found!",
            ], 404);
        }

        if($request->name_todo != null)
            $list->name_todo = $request->name_todo;

        $list->save();

        # We delete all the tasks of the todolist
        DB::table('tasks')->where('id_todo', $list->id_todo)->delete();

        # We add the new tasks
        foreach($request->task as $task){
            $newTask = Task::create(
                [
                    'id_todo'=>$list->id_todo,
                    'name_task'=>$task['name_task'],
                    'description'=>"",
                    'is_done'=>$task['is_done'],
                    'priority_level'=>$task['priority_level'],
                ]);
        }

        # get the new list of tasks

        $tasks = DB::table('tasks')->where('id_todo', $list->id_todo)->get();

        return response()->json([
            'status' => true,
            'message' => "Todolist updated successfully!",
            'list' => $list,
            'tasks' => $tasks
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

    # Delete a todolist by name
    public function userDeleteFromName(Request $request, $name_todo)
    {
        $todo = To_do_list::where('name_todo', $name_todo)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($todo == null) {
            return response()->json([
                'status' => false,
                'message' => "List not found!",
            ], 404);
        }

        # We delete all the tasks of the todolist
        DB::table('tasks')->where('id_todo', $todo->id_todo)->delete();

        $todo->delete();

        return response()->json([
            'status' => true,
            'message' => "Todolist deleted successfully!",
        ], 200);
    }

    # Update all the priorties of the tasks of a todolist selecting the todolist by name
    public function userEditPriorityFromName(Request $request, $name_todo)
    {
        $todo = To_do_list::where('name_todo', $name_todo)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($todo == null) {
            return response()->json([
                'status' => false,
                'message' => "List not found!",
            ], 404);
        }

        $tasks = Task::where('id_todo', $todo->id_todo)->get();

        foreach ($tasks as $task) {
            $task->priority_level = $request->priority_level;
            $task->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Priority updated successfully!",
        ], 200);
    }

    # convert a todolist to an event, add it to the calendar and delete the todolist
    # add the todolist's tasks to the event's tasks

    public function userConvertToEvent(Request $request, $name_todo)
    {
        $list = To_do_list::where('name_todo', $name_todo)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($list == null) {
            return response()->json([
                'status' => false,
                'message' => "List not found!",
            ], 404);
        }

        # We verify that the todolist doesn't already have a buddy
        if ($list->id_buddy != null) {
            return response()->json([
                'status' => false,
                'message' => "This todolist already has a buddy!",
            ], 400);
        }

        $calendar = DB::table('calendars')->where('id_calendar', $request->id_calendar)->first();
        if ($calendar == null) {
            return response()->json([
                'status' => false,
                'message' => "Calendar not found!",
            ], 404);
        }

        $event = Event::create(
            [
                'name_event'=>$list->name_todo,
                'description'=> "Created from the todolist ".$list->name_todo,
                'start_date'=>Carbon::create($request->start_date),
                'length'=>$request->length,
                'movable'=>false,
                'id_calendar'=>$request->id_calendar,
                'priority_level'=>$request->priority_level,
                'to_repeat'=>false,
                'color'=>$calendar->color
            ]);

        # We create the attached list of the event

        $attachedList = AttachedToDoList::create(
            [
                'id_event'=>$event->id_event,
                'name_todo'=>$list->name_todo." Todo list",
                'id_buddy'=>$list->id_todo
            ]);

        # We add the tasks of the todolist to the attached list

        $tasks = DB::table('tasks')->where('id_todo', $list->id_todo)->get();

        foreach($tasks as $task){
            $newTask = AttachedTask::create(
                [
                    'name_task'=>$task->name_task,
                    'description'=>$task->description,
                    'id_todo'=>$attachedList->id_att_todo,
                    'priority_level'=>$task->priority_level,
                    'is_done'=> $task->is_done,
                    'id_buddy'=>$task->id_task
                ]);

            $task->id_buddy = $newTask->id_att_task;
            $task->save();

        }

        # We refetch the attached tasks

        $attachedTasks = DB::table('attached_tasks')->where('id_todo', $attachedList->id_att_todo)->get();

        # We set the id_buddy of the original todolist to the id of the attached list

        $list->id_buddy = $attachedList->id_att_todo;
        $list->save();

        # We delete the tasks (DISABLED)
        # DB::table('tasks')->where('id_todo', $list->id_todo)->delete();

        # We delete the todolist (DISABLED)
        #$list->delete();

        return response()->json([
            'status' => true,
            'message' => "Todolist converted to event successfully!",
            'todo' => $list,
            'event' => $event,
            'attachedTasks' => $attachedTasks
        ], 200);

    }
}
