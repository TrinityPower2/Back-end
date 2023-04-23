<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Calendar;
use App\Models\Calendar_belong_to;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;

use ICal\ICal;

class IcsImportController extends Controller
{

    private function storeFile(Request $request)
    {
        $path = Storage::putFile('public/icsFiles', $request->file('icsFile'));
        return $path;
    }

    public function parseIcs(Request $request)
    {

        $path = $this->storeFile($request);
        //echo $path."\n\n";

            # Warning ! new ICAL absolutely NOT VERIFY what you give him
            # if he doesn't find the file, he will say it has 0 events and call it a day

            $ical = new ICal('../storage/app/'.$path, array(
                'defaultSpan'                 => 2,     // Default value
                'defaultTimeZone'             => 'UTC',
                'defaultWeekStart'            => 'MO',  // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter'             => null,  // Default value
                'filterDaysBefore'            => null ,  // Default value
                'httpUserAgent'               => null,  // Default value
                'skipRecurrence'              => false, // Default value
            ));
            //We delete the file after parsing it to save space
            Storage::delete($path);

            //We get the events from the ics file that are in the next 100 years from now
            $selected_events = $ical->eventsFromRange(Carbon::now(), Carbon::now()->addYears(100));

            //We create the calendar that will contain the events
            $calendar = Calendar::create(
                [
                    'name_calendar'=>$request->name_calendar,
                    'to_notify'=>false,
                    'color'=>$request->color_calendar,
                ]);
            //We create the link between the user and the calendar
            $index = Calendar_belong_to::create(
                [
                    'id_users'=>auth('sanctum')->user()->id,
                    'id_calendar'=>$calendar->id_calendar,
                ]);

            //We will now create an event entry for each selected_event in the ics file


            $events = [];

            foreach ($selected_events as $temp_event) {

                //If the event has no duration, we set it to endTime-startTime
                if($temp_event->duration==null){
                    $temp_event->duration = Carbon::parse($temp_event->dtstart)->diffInMinutes(Carbon::parse($temp_event->dtend));
                }

                //We create the event
                $event = Event::create(
                    [
                        'id_calendar' => $calendar->id_calendar,
                        'name_event' => $temp_event->summary,
                        'description' => $temp_event->description,
                        'start_date' => Carbon::parse($temp_event->dtstart),
                        'length' => $temp_event->duration, //TO CHANGE, SOMETIMES CAN BE NULL
                        'movable' => false,
                        'priority_level' => 10, //Is that redundant with movable ? Mayhaps, let's keep it for now
                        'to_repeat' => 0, //Doesn't appear in the ics file ?
                        'color' => $request->color_calendar,
                        //'location_event' => $event->location,
                    ]);

                array_push($events, $event);
            }

        return response()->json([
            'message' => 'File parsed successfully',
            'path' => $path,
            'total_event_count' => $ical->eventCount,
            'selected_events_count' => count($events),
            'selected_events' => $selected_events,
            'created_calendar' => $calendar,
            'created_events' => $events,
            'now' => Carbon::now(),
        ], 200);
    }
}
