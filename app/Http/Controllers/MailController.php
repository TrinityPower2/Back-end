<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Mail\HelpRequestMail;
use App\Mail\RegisteredMail;
use App\Mail\EventsPerWeekMail;
use App\Models\Calendar_belong_to;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\CalendarController;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Models\User;


class MailController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        $mailData = [
            'title' => 'Mail from ItSolutionStuff.com',
            'body' => 'This is for testing email using smtp.'
        ];

        Mail::to('pauline.david1911@gmail.com')->send(new ResetPasswordMail($mailData));

        dd("Email is sent successfully.");
    }

    public function reset_password(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            return response()->json([
                'status' => false,
                'message' => "User not found!",
            ], 404);
        }
        $user->setVisible(['remember_token']);

        $token = Str::random(60);
        $user->remember_token = $token;
        $user->save();

        $mailData = [
            'title' => 'Reset your password',
            'phrase1' => 'This is for testing email using smtp.',
        ];

        $userEmail = $request->input('email');

        Mail::to($userEmail)->send(new ResetPasswordMail($mailData, $token));

        return response()->json(['message' => 'Email sent successfully'], 200);
    }

    public function registered(Request $request)
    {
        $mailData = [
            'name' => $request->name
        ];

        $userEmail = $request->email;

        Mail::to($userEmail)->send(new RegisteredMail($mailData));

        return response()->json(['message' => 'Email sent successfully'], 200);
    }

    public function help_request2(Request $request)
    {
        if ($request->report_photo !== null && $request->report_photo->isValid()){
            $path = Storage::putfile('public/reports', $request->file('report_photo'));
        }else{
            $path=null;
        }
        $mailData = [
            'title' => 'Help request',
            'name' => $request->name,
            'email' => $request->email,
            'summary' => $request->summary,
            'details' => $request->details,
            'link' => $request->link,
            'path' => $path
        ];
        Mail::to('pauline.david1911@gmail.com')->send(new HelpRequestMail($mailData,$path));
        if($path!== null){
            Storage ::delete($path); # pour supprimer le fichier une fois envoyé
        }

        return response()->json(['message' => 'Email sent successfully'], 200);
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
        $index = Calendar_belong_to::where('id_calendar', $id_calendar)->where('id_users', $id_user)->first();
        if ($index == null) {
            return response()->json([
                'status' => false,
                'message' => "The calendar doesn't belong to you!",
            ], 401);
        }

        return $calendar;
    }

    public function EventsOfTheWeek(Request $request){
        $id_user = $request->id_user;
        $mailData = [
            'name' => DB::table('users')->where('id',$id_user)->pluck('name')[0],
            'email' => DB::table('users')->where('id',$id_user)->pluck('email')[0]
        ];
        $id_calendars = Calendar_belong_to::where('id_users',$id_user)->pluck('id_calendar')->all();
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

        return response()->json(['message' => 'Email sent successfully'], 200);
    }
}
