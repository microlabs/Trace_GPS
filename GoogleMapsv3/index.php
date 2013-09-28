<!DOCTYPE html>
<html>
<head>
   <meta http-equiv="refresh" content="30;" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <title>Microlabs - Trace GPS &agrave; partir de trames GPGGA</title>

   <link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz' rel='stylesheet' type='text/css' />
   <link href="assets/css/bootstrap.css" rel="stylesheet">
   <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

   <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>


	<script type="text/javascript">

		function initialize() 
		{
			// Initialisation des variables
			var flightPlanCoordinates = new Array();
			var marker = new Array();
		        var distanceKm = 0;

			// Point de départ de la carte
			var myLatLng = new google.maps.LatLng(43.631851,3.861833);

			// Option de la carte
			var myOptions = 
			{
				zoom: 11,
				center: myLatLng,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				panControl: true,
				zoomControl: false,
				mapTypeControl: true,
				scaleControl: false,
				streetViewControl: false,
				overviewMapControl: true
			};

			var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

			var filePath = "trames.txt";

			xmlhttp = new XMLHttpRequest();
			xmlhttp.open("GET",filePath,false);
			xmlhttp.send(null);
			var fileContent = xmlhttp.responseText;
			var fileArray = fileContent.split('$');
			var trame, lon, lat,longitude, latitude, alt_old = 0,alt_new;

			// Premier point connu

			trame = fileArray[1].split(',');

                        // Si le type de trame est GPGGA

			if(trame[0] == "GPGGA")
			{

				lat = trame[2].split('.');
				lon = trame[4].split('.');
				latitude =  parseFloat(trame[2].substring(0,2)) +  parseFloat((trame[2].substring(2,8)/59.42));
				longitude = parseFloat(trame[4].substring(0,3)) +  parseFloat((trame[4].replace(".", "").substring(3,9)/596669));      

				alt_new =  trame[9];  

				flightPlanCoordinates[0] = new google.maps.LatLng(latitude,longitude);
				marker[0] = new google.maps.Marker({
					position: flightPlanCoordinates[0],
					title:"Latitude : " + latitude + " deg\nLongitude : " + longitude + " deg\nAltitude : " + alt_new + " m" + "\nDistance parcourue (A vol d'oiseau) : " + distance(latitude,longitude,latitude,longitude) ,
					map: map
				});

				var latitude1 = latitude;
				var longitude1 = longitude;
		

				// Points connus
				for(i = 2; i < fileArray.length; i++) 
				{
					trame = fileArray[i].split(',');
					lat = trame[2].split('.');
					lon = trame[4].split('.');
					latitude =  parseFloat(trame[2].substring(0,2)) +  parseFloat((trame[2].substring(2,8)/59.42));
					longitude = parseFloat(trame[4].substring(0,3)) +  parseFloat((trame[4].replace(".", "").substring(3,9)/596669));      

					alt_new =  trame[9];  

					flightPlanCoordinates[i-1] = new google.maps.LatLng(latitude,longitude);

				}
				
				// Dernier point connu
				if( fileArray.length > 1)
				{
			
					marker[i-2] = new google.maps.Marker({
						position: flightPlanCoordinates[i-2],
						title:"Latitude : " + latitude + " deg\nLongitude : " + longitude + " deg\nAltitude : " + alt_new + " m" + "\nDistance parcourue (A vol d'oiseau) : " + distance(latitude1,longitude1,latitude,longitude) ,
						map: map
					});
				}




				// Centrage sur le dernier point connu
				map.setCenter(new google.maps.LatLng(latitude,longitude));

				// Traçage de la ligne
				var flightPath = new google.maps.Polyline({
					path: flightPlanCoordinates,
					strokeColor: "#1924B1",
					strokeOpacity: 1.0,
					strokeWeight: 2
				});

				flightPath.setMap(map);
			}
		}


		// Calcul de la distance
		function distance(lat1,lon1,lat2,lon2) {
			var R = 6371; // km (change this constant to get miles)
			var dLat = (lat2-lat1) * Math.PI / 180;
			var dLon = (lon2-lon1) * Math.PI / 180; 
			var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
				Math.cos(lat1 * Math.PI / 180 ) * Math.cos(lat2 * Math.PI / 180 ) * 
				Math.sin(dLon/2) * Math.sin(dLon/2); 
			var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
			var d = R * c;
			if (d>1) return Math.round(d)+" km";
			else if (d<=1) return Math.round(d*1000)+" m";
			return d;
		}
	</script>
</head>
<body onload="initialize()">

	<!-- Enregistrement sous forme d'un fichier KML -->
 	<?php 
               $file = file('./trames.txt', FILE_USE_INCLUDE_PATH);
               $altitude_new =0;

               
		$myFile = "testFile.kml";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, '<?xml version="1.0" encoding="UTF-8"?>
				<kml xmlns="http://earth.google.com/kml/2.0">
				  <Document>
					<name>testFile.kml</name>
					<Style id="khStyle527">
						<IconStyle id="khIconStyle529">
						</IconStyle>
						<LabelStyle id="khLabelStyle531">
						</LabelStyle>
						<LineStyle id="khLineStyle532">
						<color>ff00ff55</color>
						</LineStyle>
						<PolyStyle id="khPolyStyle533">
						<color>85ffffff</color>
						<fill>0</fill>
					</PolyStyle>
					</Style>
				<Placemark>
				<name>Trace de Navigation - testFile.kml</name>
				<description>Trace enregistrée par Navigation</description>
				<styleUrl>#khStyle527</styleUrl>
				<Style>
				<PolyStyle>
				<color>4c00ff55</color>
				<fill>1</fill>
				</PolyStyle>
				</Style>
				<LineString>
				<extrude>1</extrude>
				<altitudeMode>absolute</altitudeMode>
				<coordinates>
				  
		');


                      
               for ($i = 0; $i < sizeof($file); $i++) 
               {
                         $trame = explode(",", $file[$i]);
                         $altitude_old = $altitude_new;
                         $altitude_new = $trame[9];
                         
                         if( ($altitude_new - $altitude_old ) >= 0) $image = "up.png";
                         else $image = "down.png";
               
                         $latitude = substr((substr($trame[2], 0, 2)+(substr($trame[2], 2, 8)/59.42)), 0, 8);
                         $longitude = substr(substr($trame[4], 0, 3)+(substr(str_replace(".", "", $trame[4]), 3, 9)/596669), 0, 9);


		         $str = $longitude.','.$latitude.','.$altitude_new;

			 fwrite($fh,$str);
			 fwrite($fh,"\n");
           
                }
        
		fwrite($fh, "\n </coordinates>\n
    				</LineString>\n
  				</Placemark>\n
				</Document>\n
				</kml>");


		fclose($fh); 





             ?>
	<!-- Affichage de la carte Google Maps v3 -->
  	<div id="map_canvas"></div>

	<!-- Affichage de la barre de navigation avec les informations GPS -->
 	<div class="navbar navbar-fixed-top">
      		<div class="navbar-inner">
        		<div class="container">
          			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            				<span class="icon-bar"></span>
            				<span class="icon-bar"></span>
            				<span class="icon-bar"></span>
         			</a>
          			<a class="brand" href="http://microlabs.fr/"><img src="entete.png" alt="logo" /></a>

          			<div class="nav-collapse">
					<ul class="nav">
						<li><a href="#">Dernier point connu :</a></li>
						<li><a href="#">Latitude = <?php echo $latitude;  ?></a></li>
						<li><a href="#">Longitude = <?php echo $longitude  ?></a></li>  
						<li><a href="#">Altitude : <?php echo $altitude_new."m" ;?></a></li>            
						<li><a href="#">Satellites GPS : <?php echo $trame[7];  ?></a></li>
						<li><a href="#">Derni&eacute;re Actualisation : <?php echo date("H:i:s"); ?></a></li>
					</ul>
          			</div><!--/.nav-collapse -->
        		</div><!--/.container -->
     		</div><!--/.navbar-inner -->
    	</div><!--/.navbar navbar-fixed-top -->


    <!-- Le javascript -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap-transition.js"></script>
    <script src="assets/js/bootstrap-alert.js"></script>
    <script src="assets/js/bootstrap-modal.js"></script>
    <script src="assets/js/bootstrap-dropdown.js"></script>
    <script src="assets/js/bootstrap-scrollspy.js"></script>
    <script src="assets/js/bootstrap-tab.js"></script>
    <script src="assets/js/bootstrap-tooltip.js"></script>
    <script src="assets/js/bootstrap-popover.js"></script>
    <script src="assets/js/bootstrap-button.js"></script>
    <script src="assets/js/bootstrap-collapse.js"></script>
    <script src="assets/js/bootstrap-carousel.js"></script>
    <script src="assets/js/bootstrap-typeahead.js"></script>

  </body>
</html>
