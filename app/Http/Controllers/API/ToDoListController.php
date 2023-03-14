<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\To_do_list;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ToDoListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(To_do_list $to_do_list): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(To_do_list $to_do_list): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, To_do_list $to_do_list): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(To_do_list $to_do_list): RedirectResponse
    {
        //
    }
}
