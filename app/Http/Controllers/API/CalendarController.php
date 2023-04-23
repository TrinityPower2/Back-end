<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use APP\Models\Event;
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
                'to_notify'=>false,
                'color'=>$request->color,
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
        $calendars = DB::table('calendars')
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

        if($request->name_calendar != null)
            $calendar->name_calendar = $request->name_calendar;
        if($request->to_notify != null)
            $calendar->to_notify = $request->to_notify;
        if($request->color != null){
                $calendar->color = $request->color;
                //We also update the color of the events
                $events = DB::table('events')->where('id_calendar', $id_calendar)->update(['color' => $request->color]);
        }

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

        # We delete all the events of the calendar
        $events = DB::table('events')->where('id_calendar', $id_calendar)->delete();

        # We delete the entry in the calendar_belong_to table
        # This may look convoluted but this is due to entries having no primary key
        Calendar_belong_to::where('id_calendar', $id_calendar)->where('id_users', auth('sanctum')->user()->id)->delete();

        $calendar->delete();

        return response()->json([
            'status' => true,
            'message' => "Calendar deleted successfully!",
        ], 200);
    }

}
