<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::where('user_id', auth('sanctum')->user()->id)->get();

        return response()->json([
            'status' => true,
            'events' => $events
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
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
            "start" => Carbon::create(2023, 02, 22, 10, 0, 0, NULL),
            "end" => Carbon::create(2023, 02, 22, 18, 0, 0, NULL),
        ];

        array_push($available_periods, $free_time);

        $processed = [];
        $failed = [];

        $count = 6;
        $i = 0;
        while($i<$count){
            $event = $m_events[$i];
            $i = $i + 1;

            foreach($available_periods as &$period){
                if($event->length < $period["start"]->diffInMinutes($period["end"])){
                    $event->start_date = clone $period["start"];
                    $period["start"]->addMinutes($event->length);
                    $event->end_date = clone $period["start"];

                    if($period["start"]->diffInMinutes($period["end"]) < $minimum){
                        #WAIT HOW DO I REMOVE PERIOD IF TOO SMALL IF IM IN PERIOD
                        #DOES PHP ALLOW ?
                    }

                    array_push($processed, $event);
                    break;
                }
                array_push($failed, $event);
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Events sorted successfully!",
            'ievents' => $i_events,
            'mevents' => $m_events,
            'processed' => $processed,
            'failed' => $failed,
            'minimum' => $minimum,
            'free_time' => $free_time
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event = Event::create(
            ['title'=>$request->title,
            'description'=>$request->description,
            'user_id'=>auth('sanctum')->user()->id,
            'length'=>$request->length,
            'reccurence'=>$request->reccurence,
            'movable'=>$request->movable,
            'start_date'=>NULL,
            'end_date'=>NULL
            ]);

        return response()->json([
            'status' => true,
            'message' => "Task Created successfully!",
            'task' => $event
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        //
    }
}
