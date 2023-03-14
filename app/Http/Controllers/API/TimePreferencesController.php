<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Time_preferences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Carbon\Carbon;

class TimePreferencesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        //
    }

    public function userCreate(Request $request)
    {   
        $verification = DB::table('time_preferences')->where('name_timepref', '=', $request->name_timepref)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "This time preference is already created !",
            ], 401);
        }

        if(!in_array($request->name_timepref,["start_day","end_day","start_weekend","end_weekend"])){
            return response()->json([
                'status' => false,
                'message' => "This time preference is not valid !",
            ], 401);
        }
        
        $event = Time_preferences::create(
            [
                'name_timepref' => $request->name_timepref,
                'start_time' => $request->start_time,
                'length' => new Carbon($request->length),
                'id_users' => auth('sanctum')->user()->id
            ]);

        return response()->json([
            'status' => true,
            'message' => "Preference Created successfully!",
            'list' => $event
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
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
    public function show(Time_preferences $time_preferences): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Time_preferences $time_preferences): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Time_preferences $time_preferences): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Time_preferences $time_preferences): RedirectResponse
    {
        //
    }
}
