<?php
$lon = isset($_GET['lon'])? $_GET['lon']: "-6.987316";
$lat = isset($_GET['lat'])? $_GET['lat']: "106.551050";

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Peta Kab. Sukabumi</title>
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
        <meta charset="utf-8">
        <style>
            html, body, #map-canvas {
                margin: 0;
                padding: 20px;
                height: 400px;
            }
            #marker-position {
                font-size: 12px;
                padding: 2px;
            }
        </style>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWyJzLTNuUU3UilZWgq325Pj5Qsc8dUlU&v=3.exp&sensor=false&libraries=geometry,visualization"></script>
        <script>
            var layerDesa;
            var layerZNT;
            var infowindow;
            var marker;
            var geocoder;
            var map;
            
            var center = new google.maps.LatLng(<?php echo "{$lon},{$lat}"?>);
            var x = 0;

            function initialize() {
                geocoder = new google.maps.Geocoder();
                var mapOptions = {
                    mapTypeControl: true,
                    scaleControl: true,
                    zoomControl: true,
                    panControl: true,
                    streetViewControl: false,
                    zoom: <?php echo isset($_GET['lon'])? "18" : "14"?>,
                    minZoom: 1,
                    center: center,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    draggableCursor: 'crosshair',
                    draggingCursor: 'move'
                };
                map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
                
                    marker = new google.maps.Marker({
                        map: map,
                        draggable: <?php echo (isset($_GET['lon']))? "false" : "true"?>,
                        animation: google.maps.Animation.DROP,
                        position: map.getCenter()
                    });
                    
               <?php if(!isset($_GET['lon'])){?>
                    google.maps.event.addListener(map, 'click', function(e) {
                        x = 1;
                        marker.setPosition(e.latLng);
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                        stop();

                        p = map.getCenter();
                        if (map.getZoom() == 1 && (p.ob < -25 || p.ob > 25)) {
                            return false;
                        }
                    });
                    google.maps.event.addListener(marker, 'position_changed', update);
                    google.maps.event.addListener(marker, 'dragend', function(e) {
                        p = map.getCenter();
                        if (map.getZoom() == 1 && (p.ob < -25 || p.ob > 25)) {
                            return false;
                        }
                        map.panTo(e.latLng);
                    });
                    google.maps.event.addListener(map, 'center_changed', function() {
                        p = map.getCenter();
                        if (map.getZoom() == 1 && (p.ob < -25 || p.ob > 25)) {
                            window.setTimeout(function() {
                                map.panTo(center);
                            }, 1000);
                        }
                    });

                    update();  
                <?php
                }
                ?>

            }

            function stop() {
                time = 10;
                setTimeout(stop, time);
                if (x == 0)
                    return false;
                if (x > time * 10) {
                    marker.setAnimation(null);
                    x = 0;
                } else
                    x++;
            }

            function update() {
                var p = marker.getPosition();
                var lb = p.lat().toFixed(7);//p.lb.toFixed(7);
                var mb = p.lng().toFixed(7);//p.mb.toFixed(7);
                document.getElementById('marker-position').innerHTML = 'Longitude : ' + lb + ', Latitude : ' + mb;
            }
            function codeAddress() {
                var address = document.getElementById('address').value;
                geocoder.geocode({'address': address}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        map.setCenter(results[0].geometry.location);
                        marker.setPosition(results[0].geometry.location);
                    } else {
                        alert('Geocode was not successful for the following reason: ' + status);
                    }
                });
            }
            function home() {
                map.panTo(center)
            }
            function getLatlng() {
                var p = marker.getPosition();
                var pb = p.pb.toFixed(7);
                var ob = p.ob.toFixed(7);
                alert('Longitude : ' + pb + ', Latitude : ' + ob)
            }
            google.maps.event.addDomListener(window, 'load', initialize);

        </script>
    </head>
    <body>
        <div id="panel">
            <!--<input type="button" value="Simpan Longitude dan Latitude" onclick="getLatlng()">-->
            <input type="button" value="Center" onclick="home()">
        </div>
        <div id="map-canvas"></div>
        <div id="marker-position"></div>
    </body>
</html>