
@section('scripts')
<script src="https://unpkg.com/vue@next"></script>
<script>

  const VueApp = {
    el: '#vue-flights',
    data() {
      return {
        airports: @json($airports),
        saved: @json($saved),
        map: phpvms.map.render_base_map({
          center: ['19.08870', '72.86790'],
          zoom: '{{ $zoom }}',
        }),
        routesLayer: new L.featureGroup,
        flight_number: null,
        subfleet_id: null,
        dep: null,
        arr: null,
        arrLat: null,
        arrLon: null,
        depLat: null,
        depLon: null,
        depName: '',
        arrName: '',
        points: [],
        apiUrl: '/api/advancedbooking',
        flights: []
      }
    },
    methods: {
      findBtn() {
        this.flights = [];
        axios.post(this.apiUrl + '/findRoutes', {
          subfleet_id: this.subfleet_id,
          flight_number: this.flight_number,
          dep: this.dep,
          arr: this.arr
        }).then(res => {
          this.flights = res.data;
        })
      },
      getDepartures() {
        axios.post(this.apiUrl + '/findRoutes', {
          dep: this.dep,
          arr: this.arr
        }).then(
          res => {
            let data = res.data;
            this.flights = data;
            let $vm = this;
            $.each(data, function (i, apt)
            {
              let dep_apt = apt.dpt_airport_id;
              let index = $vm.airports.findIndex(el => el.icao === dep_apt);

              $vm.points.push(L.latLng($vm.airports[index].lat, $vm.airports[index].lon));

              let latlngs = [
                [$vm.airports[index].lat, $vm.airports[index].lon],
                [$vm.arrLat, $vm.arrLon]
              ];

              let selPointsLayer = L.geodesic([latlngs], {
                weight: 1,
                opacity: 1,
                color: '#CC2031',
                steps: 10
              });

              $vm.routesLayer.addLayer(selPointsLayer);

            });
            $vm.routesLayer.addTo($vm.map);
            if($vm.points.length > 1)
            {
              $vm.map.fitBounds(L.geodesic([$vm.points]).getBounds());
            }
          }
        );
        this.routesLayer.addTo(this.map);
      },
      getArrivals() {
        axios.post(this.apiUrl + '/findRoutes', {
          dep: this.dep,
          arr: this.arr
        }).then(
          res => {
            let data = res.data;
            this.flights = data;
            let $vm = this;
            $.each(data, function (i, apt)
            {
              let arr_apt = apt.arr_airport_id;
              let index = $vm.airports.findIndex(el => el.icao === arr_apt);

              $vm.points.push(L.latLng($vm.airports[index].lat, $vm.airports[index].lon));

              let latlngs = [
                [$vm.airports[index].lat, $vm.airports[index].lon],
                [$vm.depLat, $vm.depLon]
              ];


              let selPointsLayer = L.geodesic([latlngs], {
                weight: 1,
                opacity: 1,
                color: '#CC2031',
                steps: 10
              });

              $vm.routesLayer.addLayer(selPointsLayer);

              $vm.routesLayer.addTo($vm.map);
            });

            if($vm.points.length > 1)
            {
              $vm.map.fitBounds( L.geodesic([$vm.points]).getBounds());
            }
          }
        );
        this.routesLayer.addTo(this.map);
      },
      findRoutes() {
        if(this.arr === '0' || this.arr === null)
        {
          this.getArrivals();
          return;
        }
        if(this.dep === '0' || this.dep === null) {
          this.getDepartures();
          return;
        }

        let dep_index = this.airports.findIndex(el => el.icao === this.dep);
        let arr_index = this.airports.findIndex(el => el.icao === this.arr);

        this.points.push(L.latLng(this.airports[dep_index].lat, this.airports[dep_index].lon));
        this.points.push(L.latLng(this.airports[arr_index].lat, this.airports[arr_index].lon));

        let latlngs = [
          [this.airports[dep_index].lat, this.airports[dep_index].lon],
          [this.airports[arr_index].lat, this.airports[arr_index].lon]
        ];

        let selPointsLayer = L.geodesic([latlngs], {
          weight: 1,
          opacity: 1,
          color: '#CC2031',
          steps: 10
        });

        this.routesLayer.addLayer(selPointsLayer);

        axios.post(this.apiUrl + '/findRoutes', {
          dep: this.dep,
          arr: this.arr
        }).then(res => {
          this.flights = res.data;
          if(this.points.length > 1)
          {
            this.map.fitBounds( L.geodesic([this.points]).getBounds());
          }
        });
      },
      async save_flight(flight_id) {
        if(this.saved.includes(flight_id))
        {
          await phpvms.bids.removeBid(flight_id);
          this.saved.splice(this.saved.indexOf(flight_id), 1);
          alert('Bid Removed!');
        } else {
          await phpvms.bids.addBid(flight_id);
          this.saved.push(flight_id);
          alert('Flight Booked!');
        }

      }
    },
    mounted() {
      window.axios.defaults.headers.common['X-API-KEY'] = document.head.querySelector('meta[name="api-key"]').getAttribute('content');
      window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').getAttribute('content');

      this.airports.map(el => {
        L.circleMarker([el.lat, el.lon], {
          color: Number(el.hub) === 1 ? '#CC2031' : 'green',
          fillColor: Number(el.hub) === 1 ? '#CC2031' : 'green',
          fillOpacity: 1,
          radius: Number(el.hub) === 1 ? 5 : 4
        }).bindTooltip(el.icao + ' - ' + el.name).addTo(this.map);

        this.points.push([el.lat, el.lon]);
      });


      if(this.points.length > 1)
      {
        this.map.fitBounds( L.geodesic([this.points]).getBounds());
      }
      let $vm = this;

      $('#sf_id').on('change', function (e){
        $vm.subfleet_id = $(this).val();
      });

      let depSel = $('#dep_icao');
      let arrSel = $('#arr_icao');


      this.routesLayer.clearLayers();

      $(depSel).on('change', function () {
        depSel = $(this);

        let depInfo = depSel.children("option:selected");

        $vm.dep = depInfo.val();
        if($vm.dep !== '0') {
          $vm.depName = depInfo.data('name');
          $vm.depLat = depInfo.data('lat');
          $vm.depLon = depInfo.data('lon');

          $vm.routesLayer.clearLayers();

          $vm.points.push(L.latLng($vm.depLat, $vm.depLon));

        }
        $vm.findRoutes();

      })

      $(arrSel).on('change', function () {
        $vm.routesLayer.clearLayers();
        arrSel = $(this);

        let arrInfo = arrSel.children("option:selected");

        $vm.arr = arrInfo.val();

        if($vm.arr !== '0')
        {
          $vm.arrName = arrInfo.data('name');
          $vm.arrLat = arrInfo.data('lat');
          $vm.arrLon = arrInfo.data('lon');

        }

        $vm.findRoutes();


      })
    }
  };

  Vue.createApp(VueApp).mount('#vue-flights')
</script>

@endsection
