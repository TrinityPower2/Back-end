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
                        'start_date' => Carbon::create($temp_event["start_date"]),
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
        $N = 7;


        # We fetch the user's time preferences
        $time_preferences = DB::table('time_preferences')
        ->where('id_users', '=', auth('sanctum')->user()->id)
        ->orderBy('start_time', 'asc')
        ->get();

        # We set default values for the time preferences sleeptime , lunchtime and dinnertime
        # We are in UTC so remove 2 hours

        $lunchtime_start = Carbon::create("12:00:00")->setTimezone('Europe/Paris');
        $lunchtime_length = 60;

        $dinnertime_start = Carbon::create("19:30:00")->setTimezone('Europe/Paris');
        $dinnertime_length = 60;

        $sleeptime_start = Carbon::create("22:00:00")->setTimezone('Europe/Paris');
        $sleeptime_length = 600;

        $prefered_period = "morning";

        # We navigate through the time preferences to find the sleeptime, lunchtime and dinnertime

        foreach($time_preferences as $time_preference){
            if($time_preference->name_timepref=="lunchtime"){
                $lunchtime_start = Carbon::create($time_preference->start_time)->setTimezone('Europe/Paris');
                $lunchtime_length = $time_preference->length;
            }
            else if($time_preference->name_timepref=="dinnertime"){
                $dinnertime_start = Carbon::create($time_preference->start_time)->setTimezone('Europe/Paris');
                $dinnertime_length = $time_preference->length;
            }
            else if($time_preference->name_timepref=="sleeptime"){
                $sleeptime_start = Carbon::create($time_preference->start_time)->setTimezone('Europe/Paris');
                $sleeptime_length = $time_preference->length;
                #If the sleeptime_start is after 23:00, we reset it to 23:00
                /*
                echo $sleeptime_start;
                echo "////";
                */
                /*
                if($sleeptime_start->hour>=23||$sleeptime_start->hour<6){
                    $sleeptime_start = Carbon::create("23:00:00")->setTimezone('Europe/Paris');
                }
                #If the sleeptime_start + length is before 6:00, we reset it to 6:00
                if($sleeptime_start->addMinutes($sleeptime_length)->hour<6||$sleeptime_start->addMinutes($sleeptime_length)->hour>=23){
                    $sleeptime_length = $sleeptime_length + diffInMinutes($sleeptime_start, Carbon::create("06:00:00")->setTimezone('Europe/Paris'));
                }
                */
            }
            else if($time_preference->name_timepref=="prefered_period"){
                $prefered_period = $time_preference->miscellaneous;
            }
        }
        /*
        echo $lunchtime_start;
        echo $lunchtime_length;
        echo "-----";
        echo $dinnertime_start;
        echo $dinnertime_length;
        echo "-----";
        echo $sleeptime_start;
        echo $sleeptime_length;
        */

        # We introduce the sleeptime, lunchtime and dinnertime into the $i_events ifor the next $N days

        $today = Carbon::now('Europe/Paris');
        $temp_lunchtime = Carbon::create($today->year, $today->month, $today->day, $lunchtime_start->hour, $lunchtime_start->minute, $lunchtime_start->second);
        $temp_dinnertime = Carbon::create($today->year, $today->month, $today->day, $dinnertime_start->hour, $dinnertime_start->minute, $dinnertime_start->second);
        $temp_sleeptime = Carbon::create($today->year, $today->month, $today->day, $sleeptime_start->hour, $sleeptime_start->minute, $sleeptime_start->second);

        $minimum_start_time = Carbon::now('Europe/Paris');

        if($today->greaterThan($temp_lunchtime)){
            $minimum_start_time = clone $temp_lunchtime->addMinutes($lunchtime_length);
        }
        else if($today->greaterThan($temp_dinnertime)){
            $minimum_start_time = clone $temp_dinnertime->addMinutes($dinnertime_length);
        }
        else if($today->greaterThan($temp_sleeptime)){
            $minimum_start_time = clone $temp_sleeptime->addMinutes($sleeptime_length);
        }

        # We check if now is after the minimum_start_time
        if(Carbon::now('Europe/Paris')->greaterThan($minimum_start_time)){
            $minimum_start_time = Carbon::now('Europe/Paris');
        }

        # We fetch every event of the user that is not movable
        # We also onlu want the events that are in the following 8 days
        $i_events = DB::table('events')
        ->join('calendars', 'events.id_calendar', '=', 'calendars.id_calendar')
        ->join('calendar_belong_tos', 'calendars.id_calendar', '=', 'calendar_belong_tos.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->where('events.movable', '=', false)
        ->where('events.start_date', '>=', $minimum_start_time)
        ->where('events.start_date', '<=', Carbon::now()->addDays($N))
        ->orderBy('events.start_date', 'asc')
        ->get();


        # We add the lunchtime, dinnertime and sleeptime to the $i_events for today, depending on the current time

        # We verify that the current time is before the time of lunchtime or we do not add lunchtime for today
        if($today->lt($temp_lunchtime)){
            $i_events = $i_events->concat([
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
                ]
                ]);
        }

        # We verify that the current time is before the time of dinnertime or we do not add dinnertime for today
        if($today->lt($temp_dinnertime)){
            $i_events = $i_events->concat([
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
        }

        # We verify that the current time is before the time of sleeptime or we do not add sleeptime for today
        if($today->lt($temp_sleeptime)){
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
                ]
                ]);
        }


        # We add the lunchtime, dinnertime and sleeptime to the $i_events for the next $N days

        for ($i=1; $i < $N ; $i++) {

            $temp_lunchtime->addDays(1);
            $temp_dinnertime->addDays(1);
            $temp_sleeptime->addDays(1);

            $i_events = $i_events->concat([

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
                ],
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
                ]
            ]);

        }

        # We turn the i_events into an array
        $i_events = $i_events->toArray();

        usort($i_events, function($a, $b) {return strtotime($a->start_date) - strtotime($b->start_date);});

        # We fetch every event of the user that is movable
        $m_events = DB::table('calendar_belong_tos')
        ->join('calendars', 'calendar_belong_tos.id_calendar', '=', 'calendars.id_calendar')
        ->join('events', 'calendars.id_calendar', '=', 'events.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->where('events.movable', '=', true)
        ->orderBy('events.priority_level', 'desc')
        ->get();

        # We set the $minimum to be the smallest length of the events in $m_events
        $minimum = DB::table('calendar_belong_tos')
        ->join('calendars', 'calendar_belong_tos.id_calendar', '=', 'calendars.id_calendar')
        ->join('events', 'calendars.id_calendar', '=', 'events.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->where('events.movable', '=', true)
        ->min('events.length');

        # The array that will contain the available periods (free times)
        $available_periods = [];

        # We set the first free time to be from now until the start of the first immovable event
        # We just check that the current time is after the end of the latest daily event
        if($today->greaterThan($minimum_start_time)){
            $minimum_start_time = $today;
        }

        $free_time = [
            "start" => Carbon::now(),
            "length" => Carbon::now()->diffInMinutes($i_events[0]->start_date)
        ];


        if($free_time["length"] >= $minimum)
            array_push($available_periods, $free_time);

        for ($i=0; $i < sizeof($i_events)-1; $i++){

            # IEV = immovable event
            $iev_start_date = clone new Carbon($i_events[$i]->start_date);
            $iev_end_date = Carbon::create($iev_start_date)->addMinutes($i_events[$i]->length); #Will serve as the end of the clump of immovable events

            # NIEV = next immovable event
            $j = 1;
            $niev_start_date = clone new Carbon($i_events[$i+$j]->start_date);
            $niev_end_date = Carbon::create($niev_start_date)->addMinutes($i_events[$i+$j]->length);
            #echo($niev_start_date);

            # We check that the next immovable event begins after the end of the current clump of immovable event
            # if not, we will add the event to the clump ('if' to check who ends last) and check the next one ($j++)
            while($iev_end_date->greaterThan($niev_start_date) && $i+$j < sizeof($i_events)-1){
                # We check who ends first, the current immovable event or the next immovable event, we keep the one that ends last
                # This sets when the clump of immovable events ends
                if($niev_end_date->greaterThan($iev_end_date)){
                    $iev_end_date = clone $niev_end_date;
                }

                $j++;
                #echo(" - j incremented - ");
                $niev_start_date = clone new Carbon($i_events[$i+$j]->start_date);
                $niev_end_date = Carbon::create($niev_start_date)->addMinutes($i_events[$i+$j]->length);
            }

            if($i+$j == sizeof($i_events)-1){
                break;
            }

            # We check if the length of the free time we're creating is not smaller than the minimum
            if($iev_end_date->diffInMinutes($niev_start_date) >= $minimum){
                array_push($available_periods, ["start" => clone $iev_end_date,"length" => $iev_end_date->diffInMinutes($niev_start_date)]);
                #echo " || pushed - ";
                #echo $iev_end_date;
                #echo " - ";
                #echo $niev_start_date;
                #echo " - ";
                #echo $iev_end_date->diffInMinutes($niev_start_date);
            }
            $i = $i + $j - 1;
        }

        # If the time between the last event and the now + $N days is bigger than the minimum, we add it to the available periods
        $last_ievent = clone $i_events[sizeof($i_events)-1];
        $last_ievent_end_date = Carbon::create($last_ievent->start_date)->addMinutes($last_ievent->length);
        if($last_ievent_end_date->diffInMinutes(Carbon::now()->addDays($N)) >= $minimum){
            array_push($available_periods, ["start" => clone $last_ievent_end_date,"length" => $last_ievent_end_date->diffInMinutes(Carbon::now()->addDays($N))]);
        }

        $available_periods = $this->contextAwareFreeperiodSort($available_periods, $prefered_period);

        $available_copy = array_merge(array(), $available_periods);

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
                        $available_periods = $this->contextAwareFreeperiodSort($available_periods, $prefered_period);
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
            # $temp_event->movable = false; Welp, that will be done in second phase, if the user wants to validate the changes
            $temp_event->save();
        }

        $processed = DB::table('calendar_belong_tos')
        ->join('calendars', 'calendar_belong_tos.id_calendar', '=', 'calendars.id_calendar')
        ->join('events', 'calendars.id_calendar', '=', 'events.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->where('events.movable', '=', true)
        ->orderBy('events.priority_level', 'desc')
        ->get();


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
            'debug' => $minimum
        ], 200);
    }

    # Functions to divide free periods into morning and afternoon free periods then resort them by length
    public function contextAwareFreeperiodSort($available_periods, $prefered_period){

        # We have to split the free times between the the ones that start on the morning
        # and the ones that start on the afternoon

        $morning_periods = [];
        $afternoon_periods = [];

        foreach($available_periods as &$period){
            if($period["start"]->hour < 12){
                array_push($morning_periods, $period);
            }
            else{
                array_push($afternoon_periods, $period);
            }
        }

        # We sort the available periods in morning and afternoon by length
        usort($morning_periods, function($a, $b) {return $a['length'] - $b['length'];});
        usort($afternoon_periods, function($a, $b) {return $a['length'] - $b['length'];});

        # We then set the available periods to be the morning periods followed by the afternoon periods
        # if $prefered_period is set to "morning"
        # Otherwise we set the available periods to be the afternoon periods followed by the morning periods

        if($prefered_period == "morning"){
            $available_periods = array_merge($morning_periods, $afternoon_periods);
        }
        else{
            $available_periods = array_merge($afternoon_periods, $morning_periods);
        }

        return $available_periods;
    }

    # User confirms the changes : every movable event of the user is set to not movable and the new events are saved
    public function confirmChanges(Request $request){

        # We select the events of the user that are movable
        $events = Event::join('calendars', 'events.id_calendar', '=', 'calendars.id_calendar')
        ->join('calendar_belong_tos', 'calendars.id_calendar', '=', 'calendar_belong_tos.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->where('events.movable', '=', true)
        ->get()
        ->toArray();

        $saved = [];
        $deleted = [];

        # For each event, if the date is not null, we set it to not movable and we save it, else we delete it
        foreach ($events as $event) {
            if($event["start_date"] != null){

                $temp_event = Event::find($event["id_event"]);
                $temp_event->movable = false;
                $temp_event->save();
                # We add a copy of the event to the saved array
                array_push($saved, $temp_event);
            }
            else{
                # We add a copy of the event to the deleted array
                array_push($deleted, $event);
                $temp_event = Event::find($event["id_event"]);
                # We delete the tasks of the todoslist attached to the event
                $todoslist = AttachedToDoList::where('id_event', '=', $event["id_event"])->first();
                $tasks = AttachedTask::where('id_todo', '=', $todoslist->id_att_todo)->delete();
                #We delete the todoslist
                $todoslist->delete();
                #We delete the event
                $temp_event->delete();
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Changes confirmed successfully!",
            'saved' => $saved,
            'deleted' => $deleted,
        ], 200);
    }

    # User cancels the changes : every movable event of the user is deleted
    public function cancelChanges(Request $request){

        # We select the events of the user that are movable
        $events = Event::join('calendars', 'events.id_calendar', '=', 'calendars.id_calendar')
        ->join('calendar_belong_tos', 'calendars.id_calendar', '=', 'calendar_belong_tos.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->where('events.movable', '=', true)
        ->get()
        ->toArray();

        # We create a list copy of the events;
        $events_copy = $events;

        # For each event, we delete it
        foreach ($events as $event) {

            #We first remove the attached tasks belonging to the todolist attached to the event
            $list = AttachedTodolist::where('id_event', '=', $event["id_event"])->first();
            $tasks = AttachedTask::where('id_todo', '=', $list->id_att_todo)->delete();
            #Then we delete the todolist
            $list->delete();
            #Finally we delete the event
            $temp_event = Event::find($event["id_event"]);
            $temp_event->delete();
        }

        # For every event in the list copy, we set the start date to null
        foreach ($events_copy as $event) {
            $event["start_date"] = null;
        }

        return response()->json([
            'status' => true,
            'message' => "Changes canceled successfully!",
            'events' => $events_copy
        ], 200);

    }
}
