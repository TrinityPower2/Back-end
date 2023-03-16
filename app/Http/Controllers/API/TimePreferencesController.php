<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Time_preferences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class TimePreferencesController extends Controller
{
    /**
     * Create a new preference as a user.
     */
    public function userCreate(Request $request)
    {
        if(!in_array($request->name_timepref,["sleep","breakfast","lunch","dinner","autocal_setting"])){
            return response()->json([
                'status' => false,
                'message' => "This time preference is not valid !",
            ], 401);
        }

        $verification = DB::table('time_preferences')->where('name_timepref', '=', $request->name_timepref)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification != null){
            return response()->json([
                'status' => false,
                'message' => "This time preference is already created !",
            ], 401);
        }

        $event = Time_preferences::create(
            [
                'name_timepref' => $request->name_timepref,
                'start_time' => new Carbon($request->start_time),
                'length' => $request->length,
                'id_users' => auth('sanctum')->user()->id
            ]);

        return response()->json([
            'status' => true,
            'message' => "Preference Created successfully!",
            'list' => $event
        ], 200);
    }

    /**
     * Fetch all preferences of a user
     */
    public function userFetch(Request $request)
    {
        $list = DB::table('time_preferences')->where('id_users', '=', auth('sanctum')->user()->id)->get();

        return response()->json([
            'status' => true,
            'message' => "Preferences Fetched successfully!",
            'list' => $list
        ], 200);
    }


    /**
     * Edit a preference as a user.
     */

    public function userEdit(Request $request)
    {
        $preference = Time_preferences::where('id_timepref', $request->id_timepref)->first();
        if ($preference == null) {
            return response()->json([
                'status' => false,
                'message' => "Preference not found!",
            ], 404);
        }

        $verification = DB::table('time_preferences')->where('name_timepref', '=', $request->name_timepref)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification != null){
            return response()->json([
                'status' => false,
                'message' => "This time preference is already created !",
            ], 401);
        }

        $preference->name_timepref = $request->name_timepref;
        $preference->start_time = new Carbon($request->start_time);
        $preference->length = $request->length;

        $preference->save();

        return response()->json([
            'status' => true,
            'message' => "Preference Edited successfully!",
            'list' => $preference
        ], 200);
    }
}
