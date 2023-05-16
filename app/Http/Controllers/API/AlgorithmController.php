<?php

namespace App\Http\Controllers\Api;

use App\Models\Calendar;
use App\Models\Calendar_belong_to;
use App\Models\Event;
use App\Models\AttachedToDoList;
use App\Models\AttachedTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class AlgorithmController extends Controller
{

    # function that receives the request from the frontend
    # It will create the event detailed in the request then call the algorithm

    public function interfaceAlgorithm(Request $request)
    {

            #We check that the calendar exists and belongs to the user
            $calendar = Calendar_belong_to::where('id_calendar', $request->id_calendar)->where('id_users', auth('sanctum')->user()->id)->first();
            if ($calendar == null) {
                return response()->json([
                    'status' => false,
                    'message' => "Calendar not found!",
                ], 404);
            }

            # We add the events to the database
            foreach($request->events as $temp_event){
                if(empty($temp_event["start_date"])){
                $event = Event::create(
                    [
                        'id_calendar' => $calendar->id_calendar,
                        'name_event' => $temp_event["name"],
                        'description' => $temp_event["description"],
                        'start_date' => null,
                        'length' => $temp_event["length"], //TO CHANGE, SOMETIMES CAN BE NULL
                        'movable' => true,
                        'priority_level' => $temp_event["priority_level"],
                        'to_repeat' => 0,
                        'color' => $temp_event["color"],
                    ]);
                }
                else{
                $event = Event::create(
                    [
                        'id_calendar' => $calendar->id_calendar,
                        'name_event' => $temp_event["name"],
                        'description' => $temp_event["description"],
                        'start_date' => $temp_event["start_date"],
                        'length' => $temp_event["length"], //TO CHANGE, SOMETIMES CAN BE NULL
                        'movable' => false,
                        'priority_level' => $temp_event["priority_level"],
                        'to_repeat' => 0,
                        'color' => $temp_event["color"],
                    ]);
                }

                //Create the attached to do list
                $todolist = AttachedToDoList::create(
                    [
                        'id_event' => $event->id_event,
                        'name_todo' => $event->name_event.' To Do List',
                    ]);

                //Create the attached tasks
                foreach($temp_event["tasks"] as $temp_task){
                    $task = AttachedTask::create(
                        [
                            'id_todo' => $todolist->id_att_todo,
                            'name_task' => $temp_task["name"],
                            'description' => $temp_task["description"],
                            'priority_level' => $temp_task["priority_level"],
                            'is_done' => $temp_task["is_done"],
                        ]);
                }
            }

            # We call the algorithm
            return $this->runAlgorithm($request);
    }


    public function runAlgorithm(Request $request)
    {

        // Number of days to planify
        $N = 8;


        # We fetch the user's time preferences
        $time_preferences = DB::table('time_preferences')
        ->where('id_users', '=', auth('sanctum')->user()->id)
        ->orderBy('start_time', 'asc')
        ->get();

        # We set default values for the time preferences sleeptime , lunchtime and dinnertime
        # We are in UTC so remove 2 hours

        $lunchtime_start = Carbon::create("10:00:00");
        $lunchtime_length = 60;

        $dinnertime_start = Carbon::create("17:30:00");
        $dinnertime_length = 60;

        $sleeptime_start = Carbon::create("20:00:00");
        $sleeptime_length = 600;

        # We navigate through the time preferences to find the sleeptime, lunchtime and dinnertime

        foreach($time_preferences as $time_preference){
            if($time_preference->name_timepref=="lunchtime"){
                $lunchtime_start = Carbon::create($time_preference->start_time);
                $lunchtime_length = $time_preference->length;
            }
            else if($time_preference->name_timepref=="dinnertime"){
                $dinnertime_start = Carbon::create($time_preference->start_time);
                $dinnertime_length = $time_preference->length;
            }
            else if($time_preference->name_timepref=="sleeptime"){
                $sleeptime_start = Carbon::create($time_preference->start_time);
                $sleeptime_length = $time_preference->length;
            }
        }

        # We fetch every event of the user that is not movable
        # We also onlu want the events that are in the following 8 days
        $i_events = DB::table('events')
        ->join('calendars', 'events.id_calendar', '=', 'calendars.id_calendar')
        ->join('calendar_belong_tos', 'calendars.id_calendar', '=', 'calendar_belong_tos.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->where('events.movable', '=', false)
        ->where('events.start_date', '>=', Carbon::now())
        ->where('events.start_date', '<=', Carbon::now()->addDays($N))
        ->orderBy('events.start_date', 'asc')
        ->get();

        # We introduce the sleeptime, lunchtime and dinnertime into the $i_events ifor the next $N days

        $today = Carbon::now();
        $temp_lunchtime = Carbon::create($today->year, $today->month, $today->day, $lunchtime_start->hour, $lunchtime_start->minute, $lunchtime_start->second);
        $temp_dinnertime = Carbon::create($today->year, $today->month, $today->day, $dinnertime_start->hour, $dinnertime_start->minute, $dinnertime_start->second);
        $temp_sleeptime = Carbon::create($today->year, $today->month, $today->day, $sleeptime_start->hour, $sleeptime_start->minute, $sleeptime_start->second);

        for ($i=0; $i < $N ; $i++) {
            $i_events = $i_events->concat([
                (object) [
                    'id_event' => -1,
                    'id_calendar' => -1,
                    'name' => 'sleeptime',
                    'description' => 'sleeptime',
                    'start_date' => clone $temp_sleeptime,
                    'length' => $sleeptime_length,
                    'priority_level' => 0,
                    'movable' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                (object) [
                    'id_event' => -1,
                    'id_calendar' => -1,
                    'name' => 'lunchtime',
                    'description' => 'lunchtime',
                    'start_date' => clone $temp_lunchtime,
                    'length' => $lunchtime_length,
                    'priority_level' => 0,
                    'movable' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                (object) [
                    'id_event' => -1,
                    'id_calendar' => -1,
                    'name' => 'dinnertime',
                    'description' => 'dinnertime',
                    'start_date' => clone $temp_dinnertime,
                    'length' => $dinnertime_length,
                    'priority_level' => 0,
                    'movable' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            ]);

            $temp_lunchtime->addDays(1);
            $temp_dinnertime->addDays(1);
            $temp_sleeptime->addDays(1);

        }

        # We turn the i_events into an array
        $i_events = $i_events->toArray();

        usort($i_events, function($a, $b) {return strtotime($a->start_date) - strtotime($b->start_date);});

        # We fetch every event of the user that is movable
        $m_events = DB::table('events')
        ->join('calendars', 'events.id_calendar', '=', 'calendars.id_calendar')
        ->join('calendar_belong_tos', 'calendars.id_calendar', '=', 'calendar_belong_tos.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->where('events.movable', '=', true)
        ->orderBy('events.priority_level', 'desc')
        ->get();

        $minimum = $m_events[sizeof($m_events)-1]->length;

        $available_periods = [];

        $free_time = [
            "start" => Carbon::now(),
            "length" => Carbon::now()->diffInMinutes($i_events[0]->start_date)
        ];

        array_push($available_periods, $free_time);

        for ($i=0; $i < sizeof($i_events)-1; $i++){

            $iev_start_date = clone new Carbon($i_events[$i]->start_date);
            $iev_end_date = clone $iev_start_date->addMinutes($i_events[$i]->length);

            $niev_start_date = clone new Carbon($i_events[$i+1]->start_date);

            # We check if the length of the free time we're creating is not smaller than the minimum
            if($iev_end_date->diffInMinutes($niev_start_date) >= $minimum){
                array_push($available_periods, ["start" => clone $iev_end_date,"length" => $iev_end_date->diffInMinutes($niev_start_date)]);
            }
        }

        # If the time between the last event and the now + $N days is bigger than the minimum, we add it to the available periods
        $last_ievent = clone $i_events[sizeof($i_events)-1];
        $last_ievent_end_date = Carbon::create($last_ievent->start_date)->addMinutes($last_ievent->length);
        if($last_ievent_end_date->diffInMinutes(Carbon::now()->addDays($N)) >= $minimum){
            array_push($available_periods, ["start" => clone $last_ievent_end_date,"length" => $last_ievent_end_date->diffInMinutes(Carbon::now()->addDays($N))]);
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

                    # Check if the new period length is smaller than the minimum
                    if($period["length"] < $minimum){
                        unset($available_periods[array_search($period, $available_periods)]);
                    }
                    else{
                        usort($available_periods, function($a, $b) {return $a['length'] - $b['length'];});
                    }

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
            $temp_event = Event::find($event->id_event);
            $temp_event->start_date = $event->start_date;
            $temp_event->length = $event->length;
            $temp_event->movable = false;
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

    /*
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
