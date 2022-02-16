function initMap(locations = allLocations) {
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
      mapTypeId: fwsMaptype, 
      mapTypeControl: false,
    };
    var map = new google.maps.Map(document.getElementById('locations-near-you-map'), mapOptions);
    map.setTilt(45);

    var markers = new Array();
    var infoWindowContent = new Array();
    
    locations.forEach(function(location) {   
      infoWindowContent.push(['<div class="infoWindow"><h3>' + location.name + 
                              '</h3><p>' + location.address + '<br />' + location.zip + '</p><p>Email: ' + 
                              location.email + '</p></div>']);
    });	    


    var infoWindow = new google.maps.InfoWindow(), marker, i, position;
    // Place the markers on the map
    for (i = 0; i < locations.length; i++) {
      var position = new google.maps.LatLng(locations[i]['lat'], locations[i]['lng']);
      bounds.extend(position);

      if(clsIcon !== "")
      {
        var myicon = {
          position:  new google.maps.LatLng(locations[i]['lat'], locations[i]['lng']),
          url: clsIcon,
          scaledSize: new google.maps.Size(50, 50),
          origin: new google.maps.Point(0,0),
          anchor: new google.maps.Point(0, 0)
        };

        marker = new google.maps.Marker({
          position:  new google.maps.LatLng(locations[i]['lat'], locations[i]['lng']),
          icon: myicon,
          map: map,
          title: locations[i]['name'],
          myid: i
        });
      }
      else
      {
        marker = new google.maps.Marker({
          position:  new google.maps.LatLng(locations[i]['lat'], locations[i]['lng']),
          map: map,
          title: locations[i]['name'],
          myid: i
        });
      }
      
      // Add an infoWindow to each marker, and create a closure so that the current
      // marker is always associated with the correct click event listener
      
      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {
          infoWindow.setContent(infoWindowContent[i][0]);
          infoWindow.open(map, marker);
        }
      })(marker, i));

      // Only use the bounds to zoom the map if there is more than 1 location shown
      if(locations.length > 1) {
        map.fitBounds(bounds);
      } else {
        var center = new google.maps.LatLng(locations[0].lat, locations[0].lng);
        map.setCenter(center);
        map.setZoom(15);
      }

      // Add marker to markers array
      markers.push(marker);

    }
    
    jQuery('.marker-link').on('click', function () {
      google.maps.event.trigger(markers[jQuery(this).attr('data-markerid')], 'click');
    });
  
}



function filterLocations() {
  jQuery(function($){
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    var userAddressparam = urlParams.get('userAddress');
    var maxRadius = urlParams.get('maxRadius');

    var userLatLng;
    var geocoder = new google.maps.Geocoder();
    if(userAddressparam)
    {
      var userAddress = userAddressparam.replace(/[^a-z0-9\s]/gi, '');
    }
    else
    {
      var userAddress = '';
    }
    var maxRadius = parseInt(maxRadius, 10);
    
    if (userAddress && maxRadius) {
      userLatLng = getLatLngViaHttpRequest(userAddress);
    } 

    function getLatLngViaHttpRequest(address) {
      // Set up a request to the Geocoding API
      // Supported address format is City, City + State, just a street address, or any combo
      var addressStripped = address.split(' ').join('+');
      var key = fwsAPI;
      var request = 'https://maps.googleapis.com/maps/api/geocode/json?address=' + addressStripped + '&key=' + key;
      
      // Call the Geocoding API using jQuery GET, passing in the request and a callback function 
      // which takes one argument "data" containing the response
      $.get( request, function( data ) {
        var searchResultsAlert = document.getElementById('location-search-alert');

        // Abort if there is no response for the address data
        if (data.status === "ZERO_RESULTS") {
          searchResultsAlert.innerHTML = "Sorry, '" + address + "' seems to be an invalid address.";
          return;
        }

        var userLatLng = new google.maps.LatLng(data.results[0].geometry.location.lat, data.results[0].geometry.location.lng);
        var filteredLocations = allLocations.filter(isWithinRadius);
        
        if (filteredLocations.length > 0) {
          initMap(filteredLocations);
          createListOfLocations(filteredLocations);
          
          searchResultsAlert.innerHTML = 'Locations near ' + userAddress + ':';
        } else {
          console.log("nothing found!");
          document.getElementById('fws-wrapper').innerHTML = '';
          searchResultsAlert.innerHTML = 'Sorry, no locations were found near ' + userAddress + '.';
        }

        function isWithinRadius(location) {
          var locationLatLng = new google.maps.LatLng(location.lat, location.lng);
          var distanceBetween = google.maps.geometry.spherical.computeDistanceBetween(locationLatLng, userLatLng);
          return convertMetersToMiles(distanceBetween) <= maxRadius;
        }
      });  
    }
  });

}

function convertMetersToMiles(meters) {
  return (meters * 0.000621371);
}

function createListOfLocations(locations) {
  var bounds = new google.maps.LatLngBounds();
  var mapOptions = {
    mapTypeId: fwsMaptype, 
    mapTypeControl: false,
  };
  
  var newmarkers = new Array();
  var infoWindowContentsearch = new Array();
  var map = new google.maps.Map(document.getElementById('locations-near-you-map'), mapOptions);
  map.setTilt(45);
  
  var locationsList = document.getElementById('locations-near-you');
  // Clear any existing locations from the previous search first
  locationsList.innerHTML = '';
  var i = 0;
  var infoWindowsearch = new google.maps.InfoWindow();
  locations.forEach( function(location) {
    var specificLocation = document.createElement('div');
    var locationInfo = '<div class="fws-list-item"><a data-markerid="'+ i +'" href="#1" class="marker-link">	<h4>' + location.name + '</h4><p>' + location.address + '</p></a></div>';
    specificLocation.setAttribute("class", 'location-near-you-box');
    specificLocation.innerHTML = locationInfo;
    locationsList.appendChild(specificLocation);

    infoWindowContentsearch.push(['<div class="infoWindow"><h3>' + location.name + 
                            '</h3><p>' + location.address + '<br />' + location.zip + '</p><p>Email ' + 
                            location.email + '</p></div>']);

    if(clsIcon !== "")
    {
    var myiconsearch = {
      url: clsIcon,
      scaledSize: new google.maps.Size(50, 50),
      origin: new google.maps.Point(0,0),
      anchor: new google.maps.Point(0, 0)
   };

    markersearch = new google.maps.Marker({
      position:  new google.maps.LatLng(locations[0].lat, locations[0].lng),
      icon: myiconsearch,
      map: map,
      title: location.name,
      myid: location.myid
    });
    }
    else
    {
      markersearch = new google.maps.Marker({
        position:  new google.maps.LatLng(locations[0].lat, locations[0].lng),
        map: map,
        title: location.name,
        myid: location.myid
      });

    }

    google.maps.event.addListener(markersearch, 'click', (function(markersearch, i) {
      return function() {
        infoWindowsearch.setContent(infoWindowContentsearch[i][0]);
        infoWindowsearch.open(map, markersearch);
      }
    })(markersearch, i));

     if(locations.length > 1) {
      map.fitBounds(bounds);
    } else {
      var center = new google.maps.LatLng(locations[0].lat, locations[0].lng);
      map.setCenter(center);
      map.setZoom(15);
    }

    newmarkers.push(markersearch);

    i++; });
    
    jQuery('.marker-link').on('click', function () {
      google.maps.event.trigger(newmarkers[jQuery(this).attr('data-markerid')], 'click');
     });

}

jQuery(function($){
  initMap();
var queryString = window.location.search;
var urlParams = new URLSearchParams(queryString);
var userAddressparam = urlParams.get('userAddress');
if(userAddressparam !== "" && userAddressparam !== null)
{
  filterLocations();
}
});