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
        $url = Storage::url($path);
        echo $path."\n";
        //echo $url."\n";
        $path = str_replace("/", "\\", $path);
        echo $path."\n";

        $perfect_path = storage_path('app\\'.$path);
        echo $perfect_path."\n";

        //var_dump(scandir(storage_path('app\public\icsFiles')));

        try {
            # Warning ! new ICAL absolutely NOT VERIFY what you give him
            $ical = new ICal($perfect_path, array(
                'defaultSpan'                 => 2,     // Default value
                'defaultTimeZone'             => 'UTC',
                'defaultWeekStart'            => 'MO',  // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter'             => null,  // Default value
                'filterDaysBefore'            => null,  // Default value
                'httpUserAgent'               => null,  // Default value
                'skipRecurrence'              => false, // Default value
            ));
            // $ical->initFile('ICal.ics');
            // $ical->initUrl('https://raw.githubusercontent.com/u01jmg3/ics-parser/master/examples/ICal.ics', $username = null, $password = null, $userAgent = null);

            //echo($ical->todoCount . "\n");
            //var_dump($ical->cal);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error: ' . $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ],500);
        }
    }
}
