<?php

namespace App\Http\Controllers\Api;

use App\Models\Calendar;
use App\Models\Calendar_belong_to;
use App\Models\Event;
use App\Models\AttachedToDoList;
use App\Models\AttachedTask;
use App\Models\Task;
use App\Models\To_do_list;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class EventController extends Controller
{

    private function eventCheck4xx(Request $request, $id_event)
    {
        $event = Event::where('id_event', $id_event)->first();
        if ($event == null) {
            return response()->json([
                'status' => false,
                'message' => "Event not found!",
            ], 404);
        }
        $index = Calendar_belong_to::where('id_calendar', $event->id_calendar)->where('id_users', auth('sanctum')->user()->id)->first();
        if ($index == null) {
            return response()->json([
                'status' => false,
                'message' => "The event doesn't belong to you!",
            ], 401);
        }

        return $event;
    }

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
                'color' => $request->color, //The color of the calendar
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
        $event = $this->eventCheck4xx($request, $id_event);
        //If $event is a response, then it is an error and we return it
        if (get_class($event) == "Illuminate\Http\JsonResponse") {
            return $event;
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
        /*
        $events = DB::table('events')
        ->join('calendars', 'events.id_calendar', '=', 'calendars.id_calendar')
        ->join('calendar_belong_tos', 'calendars.id_calendar', '=', 'calendar_belong_tos.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->get();
        */

        $events = DB::table('calendar_belong_tos')
        ->join('calendars', 'calendar_belong_tos.id_calendar', '=', 'calendars.id_calendar')
        ->join('events', 'calendars.id_calendar', '=', 'events.id_calendar')
        ->where('calendar_belong_tos.id_users', '=', auth('sanctum')->user()->id)
        ->get();

        # For each event, we verify that color is not null, if so, we replace it by the color of the calendar
        foreach ($events as $event) {
            if($event->color == null){
                $calendar = Calendar::where('id_calendar', $event->id_calendar)->first();
                $event->color = $calendar->color;
            }
        }

        # For each event, we check if is to be repeated or not:
        # If to_repeat = 0, we do nothing
        # If to_repeat = 1, to be repeated every year
        # If to_repeat = 2, to be repeated every month
        # If to_repeat = 3, to be repeated every week
        # If to_repeat = 4, to be repeated every 3 day
        # If to_repeat = 5, to be repeated every 2 day
        # If to_repeat = 6, to be repeated every Log::useDailyFiles('path', days, 'level');

        # If it is to be repeated, we add the event every year, month, week, day, 3 days, 2 days, or day
        # from the day of the event to N years from now

        $N = 5;
        $created = [];

        foreach ($events as $event) {
            if($event->to_repeat != 0){
                switch($event->to_repeat){
                    case 1:
                        # We add an event every year from the day of the event to N years from now
                        $date = new Carbon($event->start_date);
                        for($i = 0; $i < $N; $i++){
                            $date = $date->addYear();
                            array_push($created,
                                (object) [
                                    'id_event' => $event->id_event,
                                    'id_calendar' => $event->id_calendar,
                                    'name_event' => $event->name_event,
                                    'description' => $event->description,
                                    'start_date' => clone $date,
                                    'length' => $event->length,
                                    'priority_level' => $event->priority_level,
                                    'movable' => false,
                                    'to_repeat' => 0,
                                    'color' => $event->color,
                                ]
                                );
                        }
                        break;
                    case 2:
                        # We add an event every month from the day of the event to N years from now
                        $date = new Carbon($event->start_date);
                        for($i = 0; $i < $N*12; $i++){
                            $date = $date->addMonth();
                            array_push($created,
                                (object) [
                                    'id_event' => $event->id_event,
                                    'id_calendar' => $event->id_calendar,
                                    'name_event' => $event->name_event,
                                    'description' => $event->description,
                                    'start_date' => clone $date,
                                    'length' => $event->length,
                                    'priority_level' => $event->priority_level,
                                    'movable' => false,
                                    'to_repeat' => 0,
                                    'color' => $event->color,
                                ]
                                );
                        }
                        break;
                    case 3:
                        # We add an event every week from the day of the event to N years from now
                        $date = new Carbon($event->start_date);
                        for($i = 0; $i < $N*52; $i++){
                            $date = $date->addWeek();
                            array_push($created,
                                (object) [
                                    'id_event' => $event->id_event,
                                    'id_calendar' => $event->id_calendar,
                                    'name_event' => $event->name_event,
                                    'description' => $event->description,
                                    'start_date' => clone $date,
                                    'length' => $event->length,
                                    'priority_level' => $event->priority_level,
                                    'movable' => false,
                                    'to_repeat' => 0,
                                    'color' => $event->color,
                                ]
                                );
                        }
                        break;
                    case 4:
                        # We add an event every 3 days from the day of the event to N years from now
                        $date = new Carbon($event->start_date);
                        for($i = 0; $i < $N*121; $i++){
                            $date = $date->addDays(3);
                            array_push($created,
                                (object) [
                                    'id_event' => $event->id_event,
                                    'id_calendar' => $event->id_calendar,
                                    'name_event' => $event->name_event,
                                    'description' => $event->description,
                                    'start_date' => clone $date,
                                    'length' => $event->length,
                                    'priority_level' => $event->priority_level,
                                    'movable' => false,
                                    'to_repeat' => 0,
                                    'color' => $event->color,
                                ]
                                );
                        }
                        break;
                    case 5:
                        # We add an event every 2 days from the day of the event to N years from now
                        $date = new Carbon($event->start_date);
                        for($i = 0; $i < $N*182; $i++){
                            $date = $date->addDays(2);
                            array_push($created,
                                (object) [
                                    'id_event' => $event->id_event,
                                    'id_calendar' => $event->id_calendar,
                                    'name_event' => $event->name_event,
                                    'description' => $event->description,
                                    'start_date' => clone $date,
                                    'length' => $event->length,
                                    'priority_level' => $event->priority_level,
                                    'movable' => false,
                                    'to_repeat' => 0,
                                    'color' => $event->color,
                                ]
                                );
                        }
                        break;
                    case 6:
                        # We add an event every day from the day of the event to N years from now
                        $date = new Carbon($event->start_date);
                        for($i = 0; $i < $N*365; $i++){
                            $date = $date->addDay();
                            array_push($created,
                                (object) [
                                    'id_event' => $event->id_event,
                                    'id_calendar' => $event->id_calendar,
                                    'name_event' => $event->name_event,
                                    'description' => $event->description,
                                    'start_date' => clone $date,
                                    'length' => $event->length,
                                    'priority_level' => $event->priority_level,
                                    'movable' => false,
                                    'to_repeat' => 0,
                                    'color' => $event->color,
                                ]
                                );
                        }
                        break;
                    default:
                        break;
                }
            }

        }

        $events = $events->toArray();
        //We concatenate the two arrays
        $events = array_merge($events, $created);

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
        $event = $this->eventCheck4xx($request, $id_event);
        //If $event is a response, then it is an error and we return it
        if (get_class($event) == "Illuminate\Http\JsonResponse") {
            return $event;
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
        if($request->color != null)
            $event->color = $request->color;

        $event->save();

        # If event color is null, replace by calendar color
        if($event->color == null){
            $calendar = Calendar::where('id_calendar', $event->id_calendar)->first();
            $event->color = $calendar->color;
        }

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
        $event = $this->eventCheck4xx($request, $id_event);
        //If $event is a response, then it is an error and we return it
        if (get_class($event) == "Illuminate\Http\JsonResponse") {
            return $event;
        }

        //Delete the attached tasks

        $att_todolist = AttachedToDoList::where('id_event', $event->id_event)->first();
        $att_tasks = AttachedTask::where('id_todo', $att_todolist->id_att_todo)->get();
        foreach($att_tasks as $task){
            $task->delete();
        }

        #We check if the attached to do list has an id_buddy, if so, we reset the id_buddy of the buddy todolist and its tasks to null
        if($att_todolist->id_buddy != null){
            $todolist = To_Do_List::where('id_todo', $att_todolist->id_buddy)->first();
            $todolist->id_buddy = null;
            $todolist->save();

            $tasks = Task::where('id_todo', $todolist->id_todo)->get();
            foreach($tasks as $task){
                $task->id_buddy = null;
                $task->save();
            }
        }

        //Delete the attached to do list
        $att_todolist->delete();

        $event->delete();

        return response()->json([
            'status' => true,
            'message' => "Event Deleted successfully!",
        ], 200);
    }

}

