<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventsPerWeekMail;
use App\Models\Calendar_belong_to;
use Carbon\Carbon;

class EventsOfTheWeek extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:events-of-the-week';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send schedules evnts of the week';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id_users = DB::table('users')->pluck('id')->all();
        foreach($id_users as $id_user){
            $mailData = [
                'name' => DB::table('users')->where('id',$id_user)->pluck('name')[0],
                'email' => DB::table('users')->where('id',$id_user)->pluck('email')[0]
            ];
            $id_calendars = Calendar_belong_to::where('id_users',$id_user)->pluck('id_calendar')->all();
            #if id_calendars is empty, we don't send the mail
            # get the number of elements in the array
            $count = count($id_calendars);
            if($count==0){
                continue;
            }
            #dd($id_calendars);
            foreach($id_calendars as $id_calendar){
                if(DB::table('calendars')->where('id_calendar', $id_calendar)->get(['to_notify']) == true){
                    $calendar = $this->calendarCheck4xx($id_calendar,$id_user);
                    //if $calendar is a response, then it is an error, and we return it
                    if (get_class($calendar) == "Illuminate\Http\JsonResponse") {
                        return $calendar;
                    }

                    $events = DB::table('events')->where('id_calendar', $id_calendar)->orderBy('start_date','asc')->get();

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

                }
            }
            $eventsNextWeek = $eventsPerWeek[0];
            #dd($eventsNextWeek[0]->name_event);
            $eventsNextWeekOrdered = array();
            for($i = 0; $i < 7; $i++){
                $eventsNextWeekOrdered[$i] = array();
            }
            foreach($eventsNextWeek as $event){
                $date = date_create($event->start_date); // We get the date of the event
                $diff = date_diff($date, $base); // We get difference between the event and the base date (Monday of this week)
                $diff = $diff->format("%a"); // We get the number of days between the event and the base date (Monday of this week)
                array_push($eventsNextWeekOrdered[$diff], $event);
            }

            foreach($eventsNextWeekOrdered as $events){
                foreach($events as $event){
                    $event->start_date = Carbon::parse($event->start_date)->format('H:i:s');
                }
            }

            Mail::to($mailData['email'])->send(new EventsPerWeekMail($mailData, $eventsNextWeekOrdered));
        }
    }

    private function calendarCheck4xx($id_calendar,$id_user)
    {
        $calendar = DB::table('calendars')->where('id_calendar', $id_calendar)->first();
        if ($calendar == null) {
            return response()->json([
                'status' => false,
                'message' => "Calendar not found!",
            ], 404);
        }
        $index = Calendar_belong_to::where('id_calendar', $id_calendar)->where('id_users',$id_user)->first();
        if ($index == null) {
            return response()->json([
                'status' => false,
                'message' => "The calendar doesn't belong to you!",
            ], 401);
        }

        return $calendar;
    }
}
