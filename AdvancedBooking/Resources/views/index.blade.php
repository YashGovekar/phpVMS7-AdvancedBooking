@extends('advancedbooking::layouts.frontend')
@section('title', 'Advanced Booking')
@section('content')
  <div>
    <div class="row">
      <div class="col-md-12">
        <h3>Advanced Booking</h3>
        <hr>
      </div>
      <div id="vue-flights" class="col-md-6">
        <div class="form-group search-form">
          <div class="form-row">
            <div class="col-lg-6">
              <div class="form-group">
                <label>@lang('flights.flightnumber') :  </label>
                <input class="form-control" name="flight_number" type="text" v-model="flight_number">
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label>@lang('common.subfleet') : </label>
                <select class="form-control select2 w-100" id="sf_id">
                  <option value="" selected>Select All</option>
                  @foreach($subfleets as $s)
                    <option value="{{$s->id}}">{{$s->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="form-row mt-4">
            <div class="col-lg-6">
              <div class="form-group">
                <label>@lang('airports.departure') : </label>
                <select class="form-control select2 w-100" name="dep_icao" id="dep_icao">
                  @if($airports)
                    <option value="">Select All</option>
                  @endif
                  @forelse($airports as $airport)
                    <option value="{{$airport->id}}" data-lat="{{$airport->lat}}" data-lon="{{$airport->lon}}" data-name="{{$airport->name}}">
                      {{$airport->icao}} - {{$airport->name}}
                    </option>
                  @empty
                    <option>No Airports Yet</option>
                  @endforelse
                </select>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label>@lang('airports.arrival') :  </label>
                <select class="form-control select2 w-100" name="arr_icao" id="arr_icao">
                  @if($airports)
                    <option value="">Select All</option>
                  @endif
                  @forelse($airports as $airport)
                    <option value="{{$airport->id}}" data-lat="{{$airport->lat}}" data-lon="{{$airport->lon}}">
                      {{$airport->icao}} - {{$airport->name}}
                    </option>
                  @empty
                    <option>No Airports Yet</option>
                  @endforelse
                </select>
              </div>
            </div>
          </div>


          <div class="clear mt-1" style="margin-top: 10px;">
            <button class="btn btn-outline-primary" type="button" v-on:click="findBtn()">
              Find
            </button>
            <a href="{{ route('frontend.flights.index') }}">@lang('common.reset')</a>
          </div>
        </div>
        <table class="table table-bordered">
          <thead>
            <th>Flight</th>
            <th>Departure</th>
            <th>Arrival</th>
            <th>Time</th>
            <th>Booking</th>
          </thead>
          <tbody>
            <tr v-if="flights.length" v-for="flight in flights">
              <td>@{{flight.flight_number}}</td>
              <td>@{{flight.dpt_airport_id}}</td>
              <td>@{{flight.arr_airport_id}}</td>
              <td>@{{flight.dpt_time + ' - ' + flight.arr_time}}</td>
              <td>
                  <button class="btn btn-round btn-icon btn-icon-mini "
                          v-bind:class="{ 'btn-info': saved.includes(flight.id) }"
                          v-on:click="save_flight(flight.id)"
                          type="button"
                          title="@lang('flights.addremovebid')">
                    <i class="fas fa-map-marker"></i>
                  </button>
              </td>
            </tr>
            <tr v-else>
              <td class="text-center" colspan="5">No Flights Yet!</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="col-md-6">
        <div id="map" style="width: {{ $config['width'] }}; height: {{ $config['height'] }}">
        </div>
      </div>
  </div>
@endsection
@include('advancedbooking::layouts.scripts')
