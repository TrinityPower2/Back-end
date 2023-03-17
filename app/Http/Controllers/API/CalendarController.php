<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\Calendar_belong_to;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Create a new calendar as a user
     */

    public function userCreate(Request $request)
    {
        $event = Calendar::create(
            [
                'name_calendar'=>$request->name_calendar,
                'to_notify'=>false
            ]);

        $index = Calendar_belong_to::create(
            [
                'id_users'=>auth('sanctum')->user()->id,
                'id_calendar'=>$event->id_calendar,
            ]);

        return response()->json([
            'status' => true,
            'message' => "Calendar Created successfully!",
            'task' => $event,
            'ref' => $index
        ], 200);
    }


    /**
     * Fetch all events of a calendar belonging to the user
     */
    public function userFetch(Request $request, $id_calendar)
    {
        $calendar = Calendar::where('id_calendar', $id_calendar)->first();
        if($calendar == null){
            return response()->json([
                'status' => false,
                'message' => "This calendar does not exist !",
            ], 401);
        }

        $index = Calendar_belong_to::where('id_calendar', $id_calendar)->where('id_users', auth('sanctum')->user()->id)->first();
        if($index == null){
            return response()->json([
                'status' => false,
                'message' => "This calendar does not belong to you !",
            ], 401);
        }

        $events = DB::table('events')->where('id_calendar', $id_calendar)->get();

        return response()->json([
            'status' => true,
            'message' => "Calendar fetched successfully!",
            'calendar' => $calendar,
            'events' => $events
        ], 200);
    }

    /**
     * Fetch all events of every calendars belonging to the user
     * ## May probably be improved somehow...
     */
    public function userFetchAll(Request $request)
    {
        $calendars = DB::table('events')
            ->join('calendars', 'events.id_calendar', '=', 'calendars.id_calendar')
            ->join('calendar_belong_tos', 'calendars.id_calendar', '=', 'calendar_belong_tos.id_calendar')
            ->where('calendar_belong_tos.id_users', auth('sanctum')->user()->id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => "Calendars fetched successfully!",
            'calendars' => $calendars
        ], 200);
    }

    /**
     * Update a calendar as a user
     */
    public function userEdit(Request $request, $id_calendar)
    {
        $calendar = Calendar::where('id_calendar', $id_calendar)->first();
        if($calendar == null){
            return response()->json([
                'status' => false,
                'message' => "This calendar does not exist !",
            ], 404);
        }

        $index = Calendar_belong_to::where('id_calendar', $id_calendar)->where('id_users', auth('sanctum')->user()->id)->first();
        if($index == null){
            return response()->json([
                'status' => false,
                'message' => "This calendar does not belong to you !",
            ], 401);
        }

        $calendar->name_calendar = $request->name_calendar;
        $calendar->to_notify = $request->to_notify;
        $calendar->save();

        return response()->json([
            'status' => true,
            'message' => "Calendar updated successfully!",
            'calendar' => $calendar
        ], 200);
    }

    /**
     * Delete a calendar as a user
     */
    public function userDelete(Request $request, $id_calendar)
    {
        $calendar = Calendar::where('id_calendar', $id_calendar)->first();
        if($calendar == null){
            return response()->json([
                'status' => false,
                'message' => "This calendar does not exist !",
            ], 404);
        }

        $index = Calendar_belong_to::where('id_calendar', $id_calendar)->where('id_users', auth('sanctum')->user()->id)->first();
        if($index == null){
            return response()->json([
                'status' => false,
                'message' => "This calendar does not belong to you !",
            ], 401);
        }

        $calendar->delete();
        $index->delete();

        return response()->json([
            'status' => true,
            'message' => "Calendar deleted successfully!",
        ], 200);
    }

}
