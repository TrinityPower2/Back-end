<?php

namespace App\Http\Controllers\Api;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\AttachedToDoList;
use App\Models\AttachedTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class EventController extends Controller
{

    /**
     * Create a new event as a user
     */

    public function userCreate(Request $request)
    {

        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $request->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this calendar!",
            ], 401);
        }

        $calendar = Calendar::where('id_calendar', $request->id_calendar)->first();

        $event = Event::create(
            [
                'name_event' => $request->name_event,
                'description' => $request->description,
                'start_date' => new Carbon($request->start_date),
                'length' => $request->length,
                'movable' => $request->movable,
                'priority_level' => $request->priority_level,
                'id_calendar' => $request->id_calendar,
                'to_repeat' => $request->to_repeat,
                'color' => $calendar->color, //The color of the calendar
            ]);

        //Create the attached to do list
        $todolist = AttachedToDoList::create(
            [
                'id_event' => $event->id_event,
                'name_todo' => $request->name_event.' To Do List',
            ]);

        return response()->json([
            'status' => true,
            'message' => "Event Created successfully!",
            'list' => $event
        ], 200);
    }

    /**
     * Get an event as a user
     */

    public function userFetch(Request $request, $id_event)
    {
        $event = Event::where('id_event', $id_event)->first();
        if($event == null){
            return response()->json([
                'status' => false,
                'message' => "This event does not exist !",
            ], 404);
        }

        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $event->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this calendar.",
            ], 401);
        }

        //Get the attached to do list
        $todolist = AttachedToDoList::where('id_event', $event->id_event)->first();
        //Get the attached tasks
        $tasks = AttachedTask::where('id_todo', $todolist->id_att_todo)->get();

        return response()->json([
            'status' => true,
            'message' => "Event fetched successfully!",
            'list' => $event,
            'tasks' => $tasks
        ], 200);
    }


    /**
     * Get all events as a user
     */

    public function userFetchAll(Request $request)
    {
        $events = DB::table('events')
        ->join('calendars', 'events.id_calendar', '=', 'calendars.id_calendar')
        ->join('calendar_belong_tos', 'calendars.id_calendar', '=', 'calendar_belong_tos.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->get();

        return response()->json([
            'status' => true,
            'message' => "Events fetched successfully!",
            'list' => $events
        ], 200);
    }


    /**
     * Edit an event as a user
     */

    public function userEdit(Request $request , $id_event)
    {
        $event = Event::where('id_event', $id_event)->first();
        if($event == null){
            return response()->json([
                'status' => false,
                'message' => "This event does not exist !",
            ], 404);
        }

        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $event->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this calendar.",
            ], 401);
        }

        if($request->name_event != null)
            $event->name_event = $request->name_event;
        if($request->description != null)
            $event->description = $request->description;
        if($request->start_date != null)
            $event->start_date = new Carbon($request->start_date);
        if($request->length != null)
            $event->length = $request->length;
        if($request->movable != null)
            $event->movable = $request->movable;
        if($request->priority_level != null)
            $event->priority_level = $request->priority_level;
        if($request->id_calendar != null)
            $event->id_calendar = $request->id_calendar;
        if($request->to_repeat != null)
            $event->to_repeat = $request->to_repeat;

        $event->save();

        return response()->json([
            'status' => true,
            'message' => "Event Edited successfully!",
            'list' => $event
        ], 200);
    }

    /**
     * Delete an event as a user
     */

    public function userDelete(Request $request, $id_event)
    {
        $event = Event::where('id_event', $id_event)->first();
        if($event == null){
            return response()->json([
                'status' => false,
                'message' => "This event does not exist !",
            ], 404);
        }

        $verification = DB::table('calendar_belong_tos')->where('id_calendar', '=', $event->id_calendar)->where('id_users', '=', auth('sanctum')->user()->id)->first();
        if($verification == null){
            return response()->json([
                'status' => false,
                'message' => "You don't have access to this calendar.",
            ], 401);
        }

        //Delete the attached tasks

        $todolist = AttachedToDoList::where('id_event', $event->id_event)->first();
        $tasks = AttachedTask::where('id_todo', $todolist->id_att_todo)->get();
        foreach($tasks as $task){
            $task->delete();
        }

        //Delete the attached to do list
        $todolist->delete();

        $event->delete();

        return response()->json([
            'status' => true,
            'message' => "Event Deleted successfully!",
        ], 200);
    }



    # LEGACY ALGORITHM. TO BE REPLACED AND PUT IN ANOTHER CONTROLLER

    /*
    public function algorithm(Request $request)
    {
        $i_events = DB::table('events')
        ->where('user_id', '=', auth('sanctum')->user()->id)
        ->where('movable', '=', '0')
        ->orderBy('start_date', 'asc')
        ->get();

        $m_events = DB::table('events')
        ->where('user_id', '=', auth('sanctum')->user()->id)
        ->where('movable', '=', '1')
        ->orderBy('length', 'desc')
        ->get()->toArray();

        $minimum = $m_events[sizeof($m_events)-1]->length;

        #$current = Carbon::now();

        $available_periods = [];

        $free_time = [
            #"start" => Carbon::create(2023, 02, 22, 10, 0, 0, NULL),
            "start" => Carbon::now(),
            "length" => Carbon::now()->diffInMinutes($i_events[0]->start_date)
        ];
        array_push($available_periods, $free_time);

        for ($i=0; $i < sizeof($i_events)-1; $i++){

            $iev_start_date = clone new Carbon($i_events[$i]->start_date);
            $iev_end_date = clone $iev_start_date->addMinutes($i_events[$i]->length);

            $niev_start_date = clone new Carbon($i_events[$i+1]->start_date);

            array_push($available_periods, ["start" => clone $iev_end_date,"length" => $iev_end_date->diffInMinutes($niev_start_date)]);
        }

        usort($available_periods, function($a, $b) {return $a['length'] - $b['length'];});

        $available_copy = $available_periods;

        $processed = [];
        $failed = [];

        for ($i=0; $i < sizeof($m_events); $i++){
            $event = $m_events[$i];
            $fail = true;

            foreach($available_periods as &$period){
                if($event->length < $period["length"]){
                    $event->start_date = clone $period["start"];
                    $period["start"]->addMinutes($event->length);
                    $period["length"] = $period["length"] - $event->length;
                    $event->end_date = NULL; #Useless/redundant

                    usort($available_periods, function($a, $b) {return $a['length'] - $b['length'];});
                    #Thats somewhat of a problem actually, it will hava a tendency to put things in the smallest space, even if it is smaller

                    array_push($processed, $event);
                    $fail = false;

                    break;
                }
            }

            if($fail == true){ #If no place was found, well, let's put in in a box and alert the user later
                array_push($failed, $event);
            }

        }

        foreach ($processed as $event) {
            $temp_event = Event::find($event->id);
            $temp_event->start_date = $event->start_date;
            $temp_event->end_date = $event->end_date;
            $temp_event->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Events sorted successfully!",
            'ievents' => $i_events,
            'mevents' => $m_events,
            'processed' => $processed,
            'failed' => $failed,
            'minimum' => $minimum,
            'original_available' => $available_copy,
            'final_available' => $available_periods,
            'debug' => "yo"
        ], 200);
    }

    public function reccurent_setup()
    {
        //TEMP VALUES WHILE THE MODEL HAS NOT BEEN MADE

        //Maybe find a way to manage conflicts...

        #UBS = User breakfast start
        $ubs = Carbon::createFromTime(8, 0, 0, NULL);
        $user_breakfast_length = 60;

        $uls = Carbon::createFromTime(12, 30, 0, NULL);
        $user_lunch_length = 60;

        $uds = Carbon::createFromTime(19, 30, 0, NULL);
        $user_dinner_length = 60;

        $uss = Carbon::createFromTime(22, 30, 0, NULL);
        $user_sleep_length = 60*8;

        Event::where('title', 'BREAKFAST')->delete(); //CLEANING PREVIOUS SETUP
        Event::where('title', 'LUNCH')->delete();
        Event::where('title', 'DINNER')->delete();
        Event::where('title', 'SLEEP')->delete();

        $day = Carbon::now();

        $range = 7; # Number of days we are planning on (3s loading for a week, 12s loading for a month)

        for ($i=0; $i < $range; $i++) {

            $event = Event::create(
                ['title'=>"BREAKFAST",
                'description'=>"Generated by Time2Do",
                'user_id'=>auth('sanctum')->user()->id,
                'length'=>$user_breakfast_length,
                'reccurence'=>"1",
                'movable'=> 0,
                'start_date'=> Carbon::create($day->year,$day->month,$day->day,$ubs->hour,$ubs->minute, $ubs->second, NULL),
                'end_date'=>NULL
                ]);

            $event = Event::create(
                ['title'=>"LUNCH",
                'description'=>"Generated by Time2Do",
                'user_id'=>auth('sanctum')->user()->id,
                'length'=>$user_lunch_length,
                'reccurence'=>"1",
                'movable'=> 0,
                'start_date'=> Carbon::create($day->year,$day->month,$day->day,$uls->hour,$uls->minute, $uls->second, NULL),
                'end_date'=>NULL
                ]);

            $event = Event::create(
                ['title'=>"DINNER",
                'description'=>"Generated by Time2Do",
                'user_id'=>auth('sanctum')->user()->id,
                'length'=>$user_dinner_length,
                'reccurence'=>"1",
                'movable'=> 0,
                'start_date'=> Carbon::create($day->year,$day->month,$day->day,$uds->hour,$uds->minute, $uds->second, NULL),
                'end_date'=>NULL
                ]);

            $event = Event::create(
                ['title'=>"SLEEP",
                'description'=>"Generated by Time2Do",
                'user_id'=>auth('sanctum')->user()->id,
                'length'=>$user_sleep_length,
                'reccurence'=>"1",
                'movable'=> 0,
                'start_date'=> Carbon::create($day->year,$day->month,$day->day,$uss->hour,$uss->minute, $uss->second, NULL),
                'end_date'=>NULL
                ]);

            $day->addDay();
        }

        return response()->json([
            'status' => true,
            'message' => "Setup done successfully!",
            'task' => DB::table('events')
            ->where('user_id', '=', auth('sanctum')->user()->id)
            ->where('movable', '=', '0')
            ->get()->toArray(),
        ], 200);
    }

    */
}
