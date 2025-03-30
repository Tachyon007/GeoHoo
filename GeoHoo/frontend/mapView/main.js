
//OL map
map = new ol.Map({
    target: 'map',
    layers: [
      new ol.layer.Tile({
        source: new ol.source.OSM(),
      }),
    ],
    view: new ol.View({
      center: ol.proj.fromLonLat([0, 0]), // Convert lon/lat to map projection
      zoom: 2,
    }),
  });


//post radius
let center = ol.proj.fromLonLat([long, lat]); // Replace with actual coordinates
let radius = CIRCLE_SIZE; //20 meters
let circle = new ol.geom.Circle(center, radius);
let circleFeature = new ol.Feature(circle);
circleFeature.setId('post-radius');
vectorSource = new ol.source.Vector({
    features: [],//[circleFeature]
});  
let vectorLayer = new ol.layer.Vector({
    source: vectorSource
});
map.addLayer(vectorLayer);
  

function zoomToLocation(lon, lat, zoomLevel) {
    map.getView().animate({
        center: ol.proj.fromLonLat([lon, lat]), // Convert lon/lat to map projection
        zoom: zoomLevel, 
        duration: 1000 // Animation duration in milliseconds
    });

    getNearPosts();
}
function getLocation(){
    navigator.geolocation.getCurrentPosition((position) => {
        lat = position.coords.latitude;
        long = position.coords.longitude;
        zoomToLocation(long, lat, 18);

        //delay needed sometimes
        updateMarkerPosition();
        setTimeout(()=>{
            updateMarkerPosition(); // Initial position
            getNearPosts();
        }, 1500)

    });
}

//every 5 seconds get locations
/*getLocation();
setInterval(()=>{
    getLocation();
},5000)*/
let inititalZoomDone = false;
const geoWatcher = navigator.geolocation.watchPosition(
    (position) => {
      const { latitude, longitude } = position.coords;
      long = longitude;
      lat = latitude;

      if(!inititalZoomDone){
        inititalZoomDone = true;
        zoomToLocation(long, lat, 18);
      }

       //update user image and post radius
       updateMarkerPosition();
       setTimeout(()=>{
           updateMarkerPosition(); // Initial position
       }, 1500)

       renderNearbyPostFeatures();

       console.log("GEO UPDATE");
    },
    (error) => {
      console.error('Error getting position: ', error);
    },
    {
      enableHighAccuracy: true, // Optional: Request higher accuracy
      maximumAge: 10000,        // Optional: Cache the position for a max of 10 seconds
      timeout: 5000,            // Optional: Timeout after 5 seconds
    }
  );
  


let userIcon = document.getElementById("userIcon");
userIcon.style.zIndex = "10";

function updateMarkerPosition() {
    if(!isMapVisible){
        return;
    }


    const pos = ol.proj.fromLonLat([long, lat]);
    const pixel = map.getPixelFromCoordinate(pos);


    //circle.getGeometry().setCenter(pos);
    //vectorSource.getFeatureById('post-radius').getGeometry().setCenter(pos)
    userIcon.style.left = `${pixel[0]}px`;
    userIcon.style.top = `${pixel[1]}px`;
}

  // Update marker position on map view change
map.getView().on('change:center', updateMarkerPosition);
map.getView().on('change:resolution', updateMarkerPosition);



/////////////////
//OTHER LOGIC

