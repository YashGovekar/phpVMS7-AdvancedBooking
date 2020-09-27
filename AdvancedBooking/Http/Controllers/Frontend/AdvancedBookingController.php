<?php


namespace Modules\AdvancedBooking\Http\Controllers\Frontend;


use App\Models\Airport;
use App\Models\Subfleet;
use Illuminate\Http\Request;
use App\Repositories\AirlineRepository;
use App\Repositories\AirportRepository;
use App\Repositories\UserRepository;
use App\Repositories\SubfleetRepository;
use Illuminate\Support\Facades\Auth;

class AdvancedBookingController
{

    private $airlineRepo;
    private $airportRepo;
    private $userRepo;
    private $subfleetRepo;

    public function __construct(
        AirlineRepository $airlineRepo,
        AirportRepository $airportRepo,
        UserRepository $userRepo,
        SubfleetRepository $subfleetRepo
    ){
        $this->airlineRepo = $airlineRepo;
        $this->airportRepo = $airportRepo;
        $this->userRepo = $userRepo;
        $this->subfleetRepo = $subfleetRepo;
    }

    public function index(Request $request)
    {
        $user = $this->userRepo
            ->with(['bids', 'bids.flight'])
            ->find(Auth::user()->id);

        $flights = collect();
        $saved_flights = [];
        foreach ($user->bids as $bid) {
            $flights->add($bid->flight);
            $saved_flights[] = $bid->flight->id;
        }

        $config = [
            'height' => '500px',
            'width'  => '100%',
        ];
        $center_coords = setting('acars.center_coords', '0,0');
        $center_coords = array_map(function ($c) {
            return (float) trim($c);
        }, explode(',', $center_coords));

        $airports = Airport::select(
            ['id', 'icao', 'name', 'location', 'country', 'lat', 'lon', 'fuel_jeta_cost', 'hub']
        )->get();
        $subfleets = Subfleet::all();

        return view('advancedbooking::index', [
            'airlines'      => $this->airlineRepo->selectBoxList(true),
            'airports'      => $airports,
            'flights'       => $flights,
            'saved'         => $saved_flights,
            'subfleets'     => $subfleets,
            'simbrief'      => !empty(setting('simbrief.api_key')),
            'simbrief_bids' => setting('simbrief.only_bids'),
            'config' => $config,
            'center' => $center_coords,
            'zoom' => setting('acars.default_zoom', 5),
        ]);
    }

}
