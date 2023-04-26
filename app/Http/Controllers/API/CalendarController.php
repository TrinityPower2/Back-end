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

    private function calendarCheck4xx(Request $request, $id_calendar)
    {
        $calendar = Calendar::where('id_calendar', $id_calendar)->first();
        if ($calendar == null) {
            return response()->json([
                'status' => false,
                'message' => "Calendar not found!",
            ], 404);
        }
        $index = Calendar_belong_to::where('id_calendar', $id_calendar)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($index == null) {
            return response()->json([
                'status' => false,
                'message' => "The calendar doesn't belong to you!",
            ], 401);
        }

        return $calendar;
    }

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
        $calendar = $this->calendarCheck4xx($request, $id_calendar);
        //if $calendar is a response, then it is an error, and we return it
        if (get_class($calendar) == "Illuminate\Http\JsonResponse") {
            return $calendar;
        }

        $events = DB::table('events')->where('id_calendar', $id_calendar)->get();

        return response()->json([
            'status' => true,
            'message' => "Calendar fetched successfully!",
            'calendar' => $calendar,
            'events' => $events
        ], 200);
    }

    public function userFetchPerDay(Request $request, $id_calendar)
    {
        $calendar = $this->calendarCheck4xx($request, $id_calendar);
        //if $calendar is a response, then it is an error, and we return it
        if (get_class($calendar) == "Illuminate\Http\JsonResponse") {
            return $calendar;
        }

        $events = DB::table('events')->where('id_calendar', $id_calendar)->get();

        //We now have to create an array for each of the following 30 days, then we have to navigate through the events, and put events of the same day in the same array
        $number_of_days = 30;
        $eventsPerDay = array();
        for($i = 0; $i < $number_of_days; $i++){
            $eventsPerDay[$i] = array();
        }

        //Now if an event is before today or after the 30th from now on, it is discarded
        //If it is today, it is added to the first array of $eventsPerDay , and so on...

        foreach($events as $event){
            $date = date_create($event->start_date);
            $today = date_create(date("Y-m-d"));
            $diff = date_diff($date, $today);
            $diff = $diff->format("%a"); // We get the number of days between the event and today
            if($diff < $number_of_days && $diff >= 0){
                array_push($eventsPerDay[$diff], $event);
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Calendar fetched successfully!",
            'calendar' => $calendar,
            'events' => $eventsPerDay
        ], 200);
    }

    public function userFetchPerWeek(Request $request, $id_calendar)
    {
        $calendar = $this->calendarCheck4xx($request, $id_calendar);
        //if $calendar is a response, then it is an error, and we return it
        if (get_class($calendar) == "Illuminate\Http\JsonResponse") {
            return $calendar;
        }

        $events = DB::table('events')->where('id_calendar', $id_calendar)->get();

        //We now have to create an array for each of the following 5 weeks, then we have to navigate through the events, and put events of the same week in the same array

        $number_of_weeks = 5;
        $eventsPerWeek = array();
        for($i = 0; $i < $number_of_weeks; $i++){
            $eventsPerWeek[$i] = array();
        }

        //Now if an event is before this week or after the 5th from now on, it is discarded
        //If it is this week, it is added to the first array of $eventsPerWeek , and so on...
        //We have to preperly take into account the fact that the week starts on monday, and not Now()
        //We therefore have to substract the number of days since monday to the current date

        $today = date_create(date("Y-m-d")); // We get the current date
        $day = date_format($today, "N"); // We get the day of the week (1 to 7) (1 being monday)
        $base = date_sub($today, date_interval_create_from_date_string($day-1 . " days")); // We get the number of days since monday, and substract it to the current date

        foreach($events as $event){
            $date = date_create($event->start_date); // We get the date of the event
            $diff = date_diff($date, $base); // We get difference between the event and the base date (Monday of this week)
            $diff = $diff->format("%a"); // We get the number of days between the event and the base date (Monday of this week)
            if($diff < (7 * $number_of_weeks) && $diff >= 0){
                array_push($eventsPerWeek[floor($diff/7)], $event);
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Calendar fetched successfully!",
            'calendar' => $calendar,
            'events' => $eventsPerWeek
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
        $calendar = $this->calendarCheck4xx($request, $id_calendar);
        //if $calendar is a response, then it is an error, and we return it
        if (get_class($calendar) == "Illuminate\Http\JsonResponse") {
            return $calendar;
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
        $calendar = $this->calendarCheck4xx($request, $id_calendar);
        //if $calendar is a response, then it is an error, and we return it
        if (get_class($calendar) == "Illuminate\Http\JsonResponse") {
            return $calendar;
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
