<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use App\Models\To_do_list;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    public function index()
    {
        $tasks = Task::all();

        return response()->json([
            'status' => true,
            'tasks' => $tasks
        ]);
    }*/

    public function index()
    {
        $tasks = Task::where('user_id', auth('sanctum')->user()->id)->get();

        return response()->json([
            'status' => true,
            'tasks' => $tasks
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function userCreate(Request $request)
    {
        
        $verification = To_do_list::where('id_todo', $request->id_todo)::where('id_users', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this list!",
            ], 401);
        } 
        
        
        $event = Task::create(
            [
                'name_task'=>$request->name_task,
                'date_day'=>$request->date_day,
                'description'=>$request->description,
                'id_todo'=>$request->id_todo,
            ]);

        return response()->json([
            'status' => true,
            'message' => "Todolist Created successfully!",
            'list' => $event
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /*
    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->all());

        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'task' => $task
        ], 200);
    }*/

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create(['title'=>$request->title,'description'=>$request->description,'user_id'=>auth('sanctum')->user()->id]);

        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'task' => $task
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTaskRequest $request, Task $task)
    {
        $task->update($request->all());

        return response()->json([
            'status' => true,
            'message' => "Task Updated successfully!",
            'task' => $task
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'status' => true,
            'message' => "Task Deleted successfully!",
        ], 200);
    }
}
