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

use Carbon\Carbon;

class IcsImportController extends Controller
{
    public function storeFile(Request $request)
    {

    }
}
