<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>PBB ONLINE</title>
   <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD3uYQUF7tnICuG1awhmtlXp3aohwbXkR0&callback=initMap&libraries=&v=weekly" defer></script>
   <style type="text/css">
      html,
      body {
         height: 100%;
         width: 100%;
      }

      #map {
         height: 100%;
         width: 100%;
      }
   </style>
   <script>
      // Initialize and add the map
      function initMap() {
         // The location of Uluru
         const uluru = {
            lat: -5.34579,
            lng: 105.00842
         };
         // The map, centered at Uluru
         const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 18,
            center: uluru,
            mapTypeId: 'hybrid'
         });
         // The marker, positioned at Uluru
         const marker = new google.maps.Marker({
            position: uluru,
            map: map,
         });
      }
   </script>
</head>

<body>
   <div id="map">&nbsp;</div>
</body>

</html>