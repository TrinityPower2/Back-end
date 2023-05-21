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
        if(!in_array($request->name_timepref,["sleeptime","lunchtime","dinnertime","prefered_period"])){
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
                'id_users' => auth('sanctum')->user()->id,
                'miscellaneous' => $request->miscellaneous
            ]);

        return response()->json([
            'status' => true,
            'message' => "Preference Created successfully!",
            'list' => $event
        ], 200);
    }


    /**
     * Fetch a preference from its name as a user.
     */

    public function userFetch(Request $request, $name_timepref)
    {
        $preference = DB::table('time_preferences')->where('name_timepref', '=', $name_timepref)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($preference == null){
            return response()->json([
                'status' => false,
                'message' => "Preference not found !",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Preference Fetched successfully!",
            'list' => $preference
        ], 200);
    }

    /**
     * Fetch all preferences of a user
     */
    public function userFetchAll(Request $request)
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

    public function userEdit(Request $request, $name_timepref)
    {
        $preference = Time_Preferences::where('name_timepref', '=', $name_timepref)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($preference == null){
            return response()->json([
                'status' => false,
                'message' => "This time preference have not been created !",
            ], 404);
        }

        # We doesn't change the name of the preference
        # That would be the same as deleting it.

        if($request->start_time != null)
            $preference->start_time = new Carbon($request->start_time);
        if($request->length != null)
            $preference->length = $request->length;
        if($request->miscellaneous != null)
            $preference->miscellaneous = $request->miscellaneous;

        $preference->save();

        return response()->json([
            'status' => true,
            'message' => "Preference Edited successfully!",
            'list' => $preference
        ], 200);
    }
}
