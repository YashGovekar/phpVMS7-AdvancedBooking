<?php

namespace Modules\AdvancedBooking\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\Flight;
use App\Models\Subfleet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * class ApiController
 * @package Modules\AdvancedBooking\Http\Controllers\Api
 */
class ApiController extends Controller
{
    /**
     * Just send out a message
     *
     * @param Request $request
     *
     * @return mixed
     */

    public function findRoutes(Request $request)
    {

        $where = [
            'active'  => true,
            'visible' => true,
        ];

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (setting('pilots.restrict_to_company')) {
            $where['airline_id'] = $user->airline_id;
        }

        if($request->filled('flight_number'))
        {
            $where['flight_number'] = $request->input('flight_number');
        }

        // default restrictions on the flights shown. Handle search differently
        if($request->filled('dep')){
            if (setting('pilots.only_flights_from_current')) {
                if($user->curr_airport_id === $request->input('dep'))
                {
                    $where['dpt_airport_id'] = $user->curr_airport_id;
                } else {
                    return [];
                }
            } else {
                $where['dpt_airport_id'] = $request->input('dep');
            }
        }

        if($request->filled('arr'))
        {
            $where['arr_airport_id'] = $request->input('arr');
        }

        $flights = Flight::where($where)->get();

        if($request->filled('subfleet_id'))
        {
            foreach($flights as $i => $flight)
            {
                $sf = DB::table('flight_subfleet')->where([
                    'flight_id' => $flight->id,
                    'subfleet_id' => $request->input('subfleet_id')
                ]);
                if(!$sf->exists())
                {
                    unset($flights[$i]);
                }
            }
        }

        return $flights;
    }


}
