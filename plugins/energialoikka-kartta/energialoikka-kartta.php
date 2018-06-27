<?php
/**
Plugin Name: Energialoikka-kartta
Plugin URI: http://www.energialoikka.fi/
Description: Energialoikka.fi-palvelun karttatyökalu
Version: 0.1
Author: Matti Lindholm
Author URI: ?
License: ?
Copyright: ?
*/

add_action( 'wp_head', 'my_header_scripts' );
add_action( 'wp_enqueue_scripts', 'my_custom_scripts_load' );
add_shortcode('eloikka_kartta', 'eloikka_map_init');

function my_custom_scripts_load() {

	wp_enqueue_style( 'openlayers', '//openlayers.org/en/v4.3.4/css/ol.css' );
	wp_enqueue_script( 'ol', '//openlayers.org/en/v4.3.4/build/ol.js', array( 'jquery' ) );

	//wp_enqueue_style( 'layerswitchercontrol', '//viglino.github.io/ol3-ext/control/layerswitchercontrol.css' );
	//wp_enqueue_script( 'layerswitchercontrol', '//viglino.github.io/ol3-ext/control/layerswitchercontrol.js', array( 'ol' ) );

	wp_enqueue_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
	wp_enqueue_script( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array( 'jquery' ) );

	wp_enqueue_script( 'polyfill', '//cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL', array( 'ol' ) );

	// Hexvin source //
	wp_enqueue_script( 'ol3-ext-geom-utils', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/js/ol3-ext-utils/ol.geom.utils.js', array( 'ol', 'jquery' ) );
	wp_enqueue_script( 'ol3-ext-hexgrid', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/js/ol3-ext-utils/hexgrid.js', array( 'ol', 'jquery' ) );
	wp_enqueue_script( 'ol3-ext-hexbinsource', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/js/ol3-ext-layer/hexbinsource.js', array( 'ol', 'jquery' ) );

	// scrollbar
	wp_enqueue_script( 'jquery-scrollbar', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/js/jquery.scrollbar-gh-pages/jquery.scrollbar.min.js', array( 'jquery' ) );
	wp_enqueue_style( 'jquery-scrollbar', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/js/jquery.scrollbar-gh-pages/jquery.scrollbar.css' );

		
}

function eloikka_map_init($atts = [], $content = null, $tag = '') { ?>

	<div style="height: 500px;">
	<div id="elMapContainer">
		<div class="map" id="elMap" style="float: left;">
			<div style="display: none;"><!-- Popup --><div id="popup"></div></div>
		</div>
		<div class="elMapPadder"><div id="elMapPanel" class="scrollbar-inner"><div id="elMapPanelContent" class="elMapPanelContent"></div></div></div>    
	</div>
	</div>

	<script>

	//globals

	var zoom = <?php if (isset($atts['zoom'])) { echo $atts['zoom']; } else { echo 5; } ?>;
	var centerLon = <?php if (isset($atts['lon'])) { echo $atts['lon']; } else { echo 24.95; } ?>;
	var centerLat = <?php if (isset($atts['lat'])) { echo $atts['lat']; } else { echo 64.2; } ?>;

	var reductionMin = 0;
	var reductionMax = 0;
	var sizeFactor = 1;

	var select;
	var clickedCoords = [];
	var clickedHeating = '';
	var clickedMatches = 0;

	var postMunis = {};
	var postRegs = {};
	
	jQuery(document).ready(function($) {

		jQuery('.scrollbar-inner').scrollbar();

		//Get posts into features
		
		function elMapPostStyles(feature, resolution) {

			// Clustered or not
			clustered = (feature.get('features').length > 1);
			
			// default
			var dash = [0,0];
			var border = '#000';
			var fill = '#ccc';
			var size = 8;
			var label = '';

			var redSum = 0;
			var redCount = 0;
			for (var f in feature.get('features')) {
				var red = feature.get('features')[f].get('reduction');
				if (!!red) {
					redSum += red;
					redCount++;
				}
			}
	
			if (redCount > 0) {
				redAvg = (redSum/redCount);
				max = reductionMax;
				min = reductionMin;
				hue = 240 * (max - redAvg) / (max-min);
				rgb = hslToRgb(hue/360, 0.8, 0.5);
				fill =  '#' + rgb[0].toString(16) + rgb[1].toString(16) + rgb[2].toString(16);			}

			if (clustered) {
				dash = [2,5];
				//fill = '#ccc';
				size = size + 2;
				label = feature.get('features').length.toString(); //'+';
			}
		
			style = [new ol.style.Style({
				image: new ol.style.Circle({
					radius: size * sizeFactor,
					stroke: new ol.style.Stroke({
					color: border,
					width: 2*sizeFactor,
					lineDash: dash
					}),
					fill: new ol.style.Fill({
					color: fill,

					})
				}),
					text: new ol.style.Text({
					text: label,
					fill: new ol.style.Fill({
					color: '#fff'
					}),
					font: 'bold ' + ((size+1)*sizeFactor).toString() + 'px sans-serif, arial'
					})
			})];
			return style;
		}

		var regionsSrc = new ol.source.Vector({
       			format: new ol.format.GeoJSON(),
       			url: '/energialoikka/wp-content/plugins/energialoikka-kartta/kunnat_100k.geojson?ver=1',
			attributions: [new ol.Attribution({
				html: 'Kuntarajat: <a href="http://www.maanmittauslaitos.fi/avoindata">Maanmittauslaitos</a>'
			})]
		});

		var muniLr = new ol.layer.Vector({
      			title: 'Suomen kunnat',
      			source: regionsSrc,
			style: function(e) {	
				
				var style;
				var name = e.get('text').split(',')[0].replace('(2:', '');

				if (e.get('nationalLevel') != '4thOrder' || typeof postMunis[name] == 'undefined') {
					style= new ol.style.Style( { display: 'none' } );
				} else {
					if (postMunis[name].count == 0) {
						style= new ol.style.Style( { display: 'none' } );
					} else {
						style = new ol.style.Style({ 
							stroke: new ol.style.Stroke({ 
								color: 'rgba(0, 0, 255, 0.5)',
								width: 1,
								//lineDash: [5,5]
							}),
							fill: new ol.style.Fill({ 
								color: 'rgba(0, 0, 255, 0.1)'
							}),
							text: new ol.style.Text({
								text: '', //'4', //e.get('text').split(',')[0].replace('(2:', ''),
								font: 'bold 20px arial'
							})
						});
					}
				}
				return style;
			}
		});

		regLr = new ol.layer.Vector({
			title: 'Suomen maakunnat',
			source: regionsSrc,
			style: function(e) {
	
				var style;
				var name = e.get('text').split(',')[0].replace('(2:', '');

				if (e.get('nationalLevel')!='3rdOrder' || typeof postRegs[name] == 'undefined') {
					style= new ol.style.Style( { display: 'none' } );
				} else {
					style = new ol.style.Style({ 

						stroke: new ol.style.Stroke({ 
							color: 'rgba(0, 255, 0, 0.5)',
							width: 1
						}),
	
						fill: new ol.style.Fill({ 
							color: 'rgba(0, 255, 0, 0.1)'
						}) 
					});
				}
				return style;
			}
  		});  


		var postSource = new ol.source.Vector({});

		var postCluster = new ol.source.Cluster({ distance: (20*sizeFactor), source: postSource });

		var postLayer = new ol.layer.Vector({
			name: 'Loikkaesimerkit',
			source: postCluster,
			style: function(feature, resolution) {
				return elMapPostStyles(feature, resolution);
			}
		});

		$.ajax({
			type: 'GET',
			url: '//www.energialoikka.fi/wp-json/el/v1/posts',
			data: { 'per_page': 100 },
			dataType: 'json',
			success: function(req) {
				console.log(req);
			}
		});

		$.ajax({
			type: 'GET',
			//url: '//www.energialoikka.fi/wp-json/wp/v2/posts',
			url: '//www.energialoikka.fi/wp-json/el/v1/posts',
			data: { 'per_page': 100 },
			dataType: 'json',
			success: function(req) {

				for (var p in req) {

					//console.log(req[p]);

					var red = parseFloat(req[p].acf.emission_reduction);
					var lat = parseFloat(req[p].acf.location.lat);
					var lon = parseFloat(req[p].acf.location.lng);
					var precise = (!!lon && !!lat);
					var url = req[p].link;
					var title = req[p].title.rendered;
					var post = { title: title, url: url };

					if (precise) {

						var point = new ol.geom.Point(ol.proj.transform([lon, lat], 'EPSG:4326', 'EPSG:3857'));
					

						if (!!red) {
							reductionMin = Math.min(reductionMin, red);
							reductionMax = Math.max(reductionMax, red);
						}
					
						var feature = new ol.Feature({
							popupTitle: title,
							popupContent: '<a href="' + url + '">Linkki</a>',
							geometry: point,
							reduction: red
						});

						feature.set('fType', 'case');
					
						postSource.addFeature( feature );

					}

					// Add munis and count them to postMunis object
					for (var m in req[p].administrative_areas.municipality) {
						var muni = req[p].administrative_areas.municipality[m].name;
//console.log(muni); console.log(post);
						if (typeof postMunis[muni] == 'undefined') {
							postMunis[muni] = { 'count': 1, preciseCount: 0, 'reduction': red, posts: [] };
					
						} else {
							postMunis[muni].count++;
							postMunis[muni].reduction += red;
	
						}
						if (precise) { 
							postMunis[muni].preciseCount++; 
						}
						postMunis[muni].posts.push(post);
					}

					// Add regions and count them to postRegs object
					for (var r in req[p].administrative_areas.region) {
						if (typeof postRegs[req[p].administrative_areas.region[r].name] == 'undefined') {
							postRegs[req[p].administrative_areas.region[r].name] = { 'count': 1, 'reduction': red };
						} else {
							postRegs[req[p].administrative_areas.region[r].name].count++;
							postRegs[req[p].administrative_areas.region[r].name].reduction += red;
						}
						if (precise) { 
							postRegs[req[p].administrative_areas.region[r].name].preciseCount++; 
						}
					}

				}
//console.log(postMunis);
				map.updateSize();
			},
			error: function(err) {
				console.log(err);
			}
		});

		var gmlFormat = new ol.format.GML();

		var lang = 'fi';
		var mapSizeX = 1;

		var baseLr = new ol.layer.Tile({
    			source: new ol.source.TileWMS({
        			url: 'http://tiles.kartat.kapsi.fi/taustakartta?',
            			crossOrigin: 'anonymous',
            			params: {
            				'FORMAT': 'image/png'
            			},
            			serverType: 'mapserver',
            			attributions: [new ol.Attribution({
					html: 'Taustakartta: <a href="http://www.maanmittauslaitos.fi/avoindata">Maanmittauslaitos</a>'
				})]
        		}),
        		minResolution: 1,
			maxResolution: 1000
    		});

    		var aeroLr = new ol.layer.Tile({
    			source: new ol.source.TileWMS({
        			url: 'http://tiles.kartat.kapsi.fi/ortokuva?',
            			crossOrigin: 'anonymous',
            			params: {
            				'FORMAT': 'image/png'
            			},
            			serverType: 'mapserver',
            			attributions: [new ol.Attribution({
					html: 'Taustakartta: <a href="http://www.maanmittauslaitos.fi/avoindata">Maanmittauslaitos</a>'
				})]
        		}),
        		maxResolution: 1,
    		});

	var osmLr = new ol.layer.Tile({
	        source: new ol.source.OSM({ 
			'url': 'http://a.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png' 
		}),
	        minResolution: 1000
	});
/*
	var mLr = new ol.layer.Vector({
      		title: 'added Layer',
      		source: new ol.source.Vector({
         		format: new ol.format.GeoJSON(),
         		url: '/energialoikka/wp-content/plugins/energialoikka-kartta/kunnat.geojson'
      		})
  	}); 
*/
	var guideSrc = new ol.source.Vector({});

	var guideLr = new ol.layer.Vector({
		name: 'Ohjeet',
		source: guideSrc,
		style: [
			new ol.style.Style({
    			stroke: new ol.style.Stroke({
        			color: '#999999',
        			width: 2
    			}),
    			fill: new ol.style.Fill({
        			color: 'rgba(100, 100, 100, 0.1)'
    			})
		})]
	});

	 	var map = new ol.Map({
	        	target: 'elMap',
	        	controls: ol.control.defaults({
	          		attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
	            			collapsible: true
	          		})
	          	}),
	        	layers: [
				osmLr,
				baseLr,
				aeroLr,
				regLr,
				muniLr,
				postLayer,
				guideLr
	        	],
			units: 'm'
	      	});

		map.addControl(new ol.control.FullScreen({ source: 'elMapContainer' }));

	      	map.setView(new ol.View({
		        center: ol.proj.transform([centerLon, centerLat], 'EPSG:4326', 'EPSG:3857' ),
		        zoom: zoom
     		}));


     		var elBuildingStyleFunction = (function() {     		
        		//var styles = {};        		
        	        return function(e) {        	        
		        	//return styles[feature.getGeometry().getType()] || styles['default'];

		        	var props = e.getProperties();
				var near = false;
				var sameHeating = false;

				if (clickedCoords.length > 0 && clickedHeating != '') {
	
					var wgs84Sphere = new ol.Sphere(6378137);
					var distance = wgs84Sphere.haversineDistance(ol.proj.transform(clickedCoords, 'EPSG:3857', 'EPSG:4326'), ol.proj.transform(ol.extent.getCenter(e.getGeometry().getExtent()), 'EPSG:3857', 'EPSG:4326'));

					if (distance < 500) {
						near = true; 
					} else {
						near = false;
					}

					if (props.c_poltaine == clickedHeating) { 
						sameHeating = true;
					} else {
						sameHeating = false;
					}
				}

		        	var fillC = 'grey';

				if (near && sameHeating) {

					clickedMatches++;
					fillC = 'rgba(200, 50, 50, 0.9)';
					//jQuery('#popupMatchedBuildings').html(clickedMatches);

				} else {

					fillC = 'rgba(200, 200, 200, 0.1)';

				}

/*

		        	if (typeof props.c_poltaine != 'undefined') {
		        		//console.log(props.c_poltaine);
		        		//console.log(typeof props.c_poltaine);
		        		switch (props.c_poltaine) {
		        			case "1": fillC = 'red'; break;
		        			case "2": 
		        			case "3": fillC = 'black'; break;
		        			case "4": fillC = 'yellow'; break;
		        			case "5": fillC = 'blue'; break;
		        			case "6": fillC = 'blue'; break;
		        			case "7": fillC = 'orange'; break;
		        			case "8": fillC = 'blue'; break;
		        			case "9": fillC = 'green'; break;
		        			default: fillC = 'grey'; break;						        			
		        		}
		        		//console.log(fillC);	
					//return new ol.style.Style({ fill: new ol.style.Fill({ color: 'grey' }) });        	
		        		//console.log(props.c_poltaine);
		        	}
*/
		        	return new ol.style.Style({ fill: new ol.style.Fill({ color: fillC }) });
		        };
      		})();     
		
     		//Helsinki
		var helSrc = new ol.source.Vector({
  			loader: function(extent) {
    				jQuery.ajax('http://kartta.hel.fi/ws/geoserver/avoindata/wfs', {
        				type: 'GET', data: {
         					service: 'WFS',
          					version: '1.1.0',
          					request: 'GetFeature',
          					//typename: 'Rakennukset_kartalla',
          					//typename: 'Rakennukset_rekisteripisteet',
          					typename: 'Rakennukset_rekisterialueet',
          					srsname: 'EPSG:3857',
          					bbox: extent.join(',') + ',EPSG:3857'
        				}
      				}).done(function(response) {
      					//console.log(response);
        				helwfs
        				.getSource()
        				.addFeatures(new ol.format.WFS()
          				.readFeatures(response));
      				});
    			},
  			strategy: ol.loadingstrategy.bbox,
    			projection: 'EPSG:3857'
  		});

		var helwfs = new ol.layer.Vector({
  			source: helSrc,
  			style: elBuildingStyleFunction,
			maxResolution: 16
			
		});	
		map.addLayer(helwfs);

		var min = 1, max=10, maxi;
		var binSize = 50
		var hexStyle = function(f,res) {	

			return	[ new ol.style.Style({	
				image: new ol.style.RegularShape({	
					points: 6,
					radius: Math.round(binSize/res+0.5) * Math.min(1,f.get('features').length/max),
					fill: new ol.style.Fill({ color: [0,0,255] }),
					rotateWithView: true
				}),
				geometry: f.get('center')
			})];
			
			//return [ new ol.style.Style({ fill: new ol.style.Fill({color: [0,0,255,Math.min(1,f.get('features').length/max)] }) }) ];
		};


		var hexbin = new ol.source.HexBin({	
			source: helSrc,		// source of the bin
			size: binSize			// hexagon size (in map unit)
		});

		var key = helSrc.on('change', function() {
			if (helSrc.getState() === 'ready') {
    				//ol.Observable.unByKey(key);
    				console.log('source ready!'); // do something with the source
				console.log(helSrc.getFeatures().length);
  			}
		});


		var hexLr = new ol.layer.Vector({ source: hexbin, opacity:0.5, style: hexStyle });

/*
		features = hexbin.getFeatures();

		// Calculate min/ max value
		min = Infinity;
		max = 0;
		for (var i=0, f; f=features[i]; i++)
		{	var n = f.get('features').length;

			if (n<min) min = n;
			if (n>max) max = n;
		}
		var dl = (max-min);
		maxi = max;
		min = Math.max(1,Math.round(dl/4));
		max = Math.round(max - dl/3);
		
*/
		// Add layer
		map.addLayer(hexLr);

/*
		//Tampere
		var tamwfs = new ol.layer.Vector({
			source: new ol.source.Vector({
				loader: function(extent) {
					jQuery.ajax('http://opendata.navici.com/tampere/ows', {
						type: 'GET', data: {
							service: 'WFS',
							version: '1.1.0',
							request: 'GetFeature',
							typename: 'RAKENN_ST_FA_MVIEW_VIEW',
							srsname: 'EPSG:3857',
							bbox: extent.join(',') + ',EPSG:3857'
						}
					}).done(function(response) {
						//console.log(response);
						tamwfs
						.getSource()
						.addFeatures(new ol.format.WFS()
						.readFeatures(response));
					});
				},
				strategy: ol.loadingstrategy.bbox,
				projection: 'EPSG:3857'
			})
		});	
		//map.addLayer(tamwfs);
		//Lakes
		var esrijsonFormat = new ol.format.EsriJSON();
		var lakeSource = new ol.source.Vector({
			source: new ol.source.Vector({
				loader: function(extent, resolution, projection) {
					var url = '//paikkatieto.ymparisto.fi/arcgis/rest/services/INSPIRE/SYKE_Hydrografia/MapServer/8/query';
					url += '?f=json&returnGeometry=true&spatialRel=esriSpatialRelIntersects&geometry=' + encodeURIComponent('{"xmin":' + extent[0] + ',"ymin":' + extent[1] + ',"xmax":' + extent[2] + ',"ymax":' + extent[3] + ',"spatialReference":{"wkid":102100}}') + '&geometryType=esriGeometryEnvelope&inSR=102100&outFields=*&outSR=102100';
					//console.log(url);
					jQuery.ajax({
						url: url, 
						dataType: 'jsonp', 
						success: function(response) {
							//console.log(response);
						}
					});
				},
				strategy: ol.loadingstrategy.tile(ol.tilegrid.createXYZ({
					tileSize: 512
				}))
			})
		});					
		var lakelr = new ol.layer.Vector({
			source: lakeSource
      		});
		//map.addLayer(lakelr);
*/

    		// Popup showing the position the user clicked
		var popup = new ol.Overlay({
			element: document.getElementById('popup')
		});
		map.addOverlay(popup);

    		var select = new ol.interaction.Select({ 
			layers: [helwfs, postLayer, muniLr, regLr],
			toggleCondition: ol.events.condition.never
		});
		map.addInteraction(select);
		var selectedFeatures = select.getFeatures();

		selectedFeatures.on('remove', function(e) {
		      	guideSrc.clear();
			clickedHeating = '';
			clickedCoords = [];
			clickedMatches = 0;
			helwfs.getSource().changed();
			var element = popup.getElement();
	            	jQuery(element).popover('destroy');

		});

		selectedFeatures.on('add', function(e) {

			guideSrc.clear();
			var element = popup.getElement();
	            	jQuery(element).popover('destroy');

				if (typeof e.target.item(0).get('features') != 'undefined') {
	
					if (e.target.item(0).get('features').length > 1) {
								
		    				map.getView().animate({
	        	       				center: e.target.item(0).getGeometry().getCoordinates(),
			        	       		zoom: map.getView().getZoom()+2,
			               			duration: 1000
		        		       	});											
						selectedFeatures.clear();

					} else {

						var clickedF = e.target.item(0).get('features')[0];
	
						ext = e.target.item(0).getGeometry().getExtent();
				        	center = ol.extent.getCenter(ext);

				        	title = clickedF.get('popupTitle');
			        		content = clickedF.get('popupContent');

						jQuery('#elMapPanelContent').html('<h3>' + title + '</h3>' + content);
						//popup.setPosition(center);
						//jQuery(element).popover({
						//	'placement': 'top',
						//	'animation': false,
						//	'html': true,
						//	'title': title,
						//	'content': content
						//});
  						//jQuery(element).popover('show');

						

														
		    				map.getView().animate({
	        	       				center: e.target.item(0).getGeometry().getCoordinates(),
			        	       		zoom: Math.min(map.getView().getZoom()+1, 18),
			               			duration: 1000
		        		       	});

					}

				} else {

		        		var titleArr = new Array();
			        	var contentArr = new Array();

		            		var feature = e.target.item(0);
					var ee = feature.getGeometry().getExtent();	

					var rName = e.target.item(0).get('text').split(',')[0].replace('(2:', '');
					var rLevel = e.target.item(0).get('nationalLevel');

					//if muni

					if (rLevel == '4thOrder') {

						//titleArr.push(rName);

						content = 'Kunnasta l&ouml;ytyy kaikkiaan ' + postMunis[rName].count + ' loikkaesimerkki&auml;!';
						content += 'Loikista ' + postMunis[rName].preciseCount + ' sijainti on merkitty tarkkaan ja ne nakyvat pistein&auml; kartalla. ' + (postMunis[rName].count - postMunis[rName].preciseCount) + ' esimerkki&auml; koskevat koko kuntaa tai niist&auml; ei ole merkitty tarkempaa sijaintia.';

						//content += '<table class="elMapPanelTable">';
						for (var p in postMunis[rName].posts) {
							//content += '<tr class="elMapPanelRowAlt_' + (2 - p % 2).toString() + '"><td><a href="' + postMunis[rName].posts[p].url + '">' + postMunis[rName].posts[p].title + '</a></td></tr>';
							content += '<p class="elMapPanelRowAlt_' + (2 - p % 2).toString() + '"><a href="' + postMunis[rName].posts[p].url + '">' + postMunis[rName].posts[p].title + '</a></p>';

						}
						//content += '</table>';

		    				map.getView().fit( ee, { duration: 1000 } );

						jQuery('#elMapPanelContent').html('<h3>' + rName + '</h3>' + content);
						jQuery('.scrollbar-inner').scrollbar();



					} else if (rLevel == '3rdOrder') {
						titleArr.push(rName);
						contentArr.push('Maakunnasta l&ouml;ytyy kaikkiaan ' + postRegs[rName].count + ' loikkaesimerkki&auml;!');
						map.getView().fit( ee, { duration: 1000 } );

jQuery('#elMapPanelContent').html('<h3>' + titleArr[0] + '</h3>' + contentArr[0]);


					} else {

			            		var lammitys = elCombineHeatings(feature.get('c_lammtapa'), feature.get('c_poltaine'), lang);
	
						clickedCoords = ol.extent.getCenter(ee);
						clickedHeating = feature.get('c_poltaine');
						clickedMatches = 0;
						//map.updateSize();
						helwfs.getSource().changed();

						var point = new ol.geom.Point(ol.proj.transform(clickedCoords, 'EPSG:4326', 'EPSG:3857'));					
						var feature = new ol.Feature({
							geometry: point
						});			

						var center = ol.proj.transform(clickedCoords, 'EPSG:3857', 'EPSG:4326');
						var circle = ol.geom.Polygon.circular(
							new ol.Sphere(6378137),
        						center,
        						500,
        						64
						).transform('EPSG:4326', 'EPSG:3857');

						circleFeature = new ol.Feature(circle)
						guideSrc.addFeature(circleFeature);
	
			            		function elCombineHeatings(heating, fuel, lang) {
			  
			            			var key = heating + fuel; 
	
							var labels = {
								'11': { 'fi': 'Kaukolämpö' },
								'12': { 'fi': 'Öljylämmitys' },
								'13': { 'fi': 'Öljylämmitys' },
								'14': { 'fi': 'Varaava sähkö' },
								'15': { 'fi': 'Kaasu, hiili tai turve' },
								'16': { 'fi': 'Kaasu, hiili tai turve' },
								'17': { 'fi': 'Puukattila' },
								'18': { 'fi': 'Kaasu, hiili tai turve' },
								'19': { 'fi': 'Maa- tai aurinkolämpö' },
								'110': { 'fi': 'Muu' },
								
								'34': { 'fi': 'Suora sähkölämmitys' },
								
								'47': { 'fi': 'Kaakeliuuni tms.' },
							
								'5': { 'fi': 'Ei kiinteää lämmitystä' }
							};
							lang = 'fi';
						    	if (typeof labels[key] != 'undefined') {
						    		return labels[key][lang];		            			
						    	} else {
						    		return heating + ' + ' + fuel;
						    	}
		            			}
		            		
			            		function elCombineBuildingTypes(typenr, lang) {
		            		
			            			var mainTypes = { '011': 'a', '012': 'a', '013': 'a', '021': 'b', '039': 'c' };
						
							var labels =  { 
								'a' : { 'fi': 'Pientalo' },
								'b' : { 'fi': 'Rivitalo' },
								'c' : { 'fi': 'Kerrostalo' }
							}

							if (typeof mainTypes[typenr] != 'undefined') {
						    		return labels[mainTypes[typenr]][lang];
						    	} else {
						    		return typenr;
						    	}
			            		}
		            		
			            		if (e.target.a[0].R.ratu_vastaavuus_koodi == "1") {           		
			            			titleArr.push(elCombineBuildingTypes(e.target.a[0].R.c_kayttark, lang));
			            			contentArr.push(e.target.a[0].R.tyyppi);
		            		
		        	    			contentArr.push('Rakennusvuosi:&nbsp;' + e.target.a[0].R.c_valmpvm.split('-')[0]);
		            				//contentArr.push('Asuntoja:&nbsp;' + Math.round(e.target.a[0].R.i_huoneistojen_lkm));
		            				//contentArr.push('Pinta-ala:&nbsp;' + Math.round(e.target.a[0].R.d_ashuoala) + '&nbsp;m&sup2;');
			            			contentArr.push('Lämmitys:&nbsp;' + lammitys);
			            			//contentArr.push('Lämmönjako:&nbsp;' + e.target.a[0].R.c_lammtapa);
			            			//contentArr.push('Lämmitysmuoto:&nbsp;' + e.target.a[0].R.c_poltaine);
							contentArr.push('<span id="popupMatchedBuildings"></span>');
		        	    		} else {
		            				titleArr.push('Ei tietoja');
		            				contentArr.push('Rakennuksesta ei ole tarkempia tietoja.');
			            		}
						//titleArr.push(feature.get('popupTitle'));
						//contentArr.push(feature.get('popupContent'));

					}
		         	            	
	 		        	content = contentArr.join('<br />');		        	
			        	//var cc = ol.extent.getCenter(ee);		    
					//jQuery(element).popover('destroy');
					//popup.setPosition(cc);
					//jQuery(element).popover({
					//	'placement': 'top',
					//	'animation': false,
					//	'html': true,
					//	'title': titleArr[0],
					//	'content': content
					//});
  					//jQuery(element).popover('show');

					//jQuery('#elMapPanel').html('<h3>' + titleArr[0] + '</h3>' + content);

	    			}


			});
	
		});

		function hslToRgb(h, s, l){
		/**
		 * Converts an HSL color value to RGB. Conversion formula
		 * adapted from //en.wikipedia.org/wiki/HSL_color_space.
		 * Assumes h, s, and l are contained in the set [0, 1] and
		 * returns r, g, and b in the set [0, 255].
		 *
		 * @param   Number  h       The hue
		 * @param   Number  s       The saturation
		 * @param   Number  l       The lightness
		 * @return  Array           The RGB representation
 		*/
		    var r, g, b;

		    if(s == 0){
		        r = g = b = l; // achromatic
		    }else{
		        function hue2rgb(p, q, t){
		            if(t < 0) t += 1;
		            if(t > 1) t -= 1;
		            if(t < 1/6) return p + (q - p) * 6 * t;
		            if(t < 1/2) return q;
		            if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
		            return p;
		        }

		        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
		        var p = 2 * l - q;
		        r = hue2rgb(p, q, h + 1/3);
		        g = hue2rgb(p, q, h);
		        b = hue2rgb(p, q, h - 1/3);
		    }

		    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
		}

</script>

<?php }

function my_header_scripts() { ?>

    <style>

	html { font-size: medium !important; }
	blockquote { margin-bottom: 1.5em !important; border: none !important; }
	blockquote:before { content: '"'; font-size: 5em; float: left; }

	#elMapContainer { height: 100%; width: 100%; border: 1px solid #ccc; }
    	.map { height: 100%; width: 100%; }
      	.map:-moz-full-screen { height: 100%; }
      	.map:-webkit-full-screen { height: 100%; }
      	.map:-ms-fullscreen { height: 100%; }
      	.map:fullscreen { height: 100%; }

	.scroll-wrapper { height: 100% !important; }

	#elMap { width: 75%; }
	#elMapPanel { float: right; height: 100%; width: 25%; border-left: 1px solid #ccc; }
	.elMapPadder { height: 100%; padding: 3px 2px; } 
	.elMapPanelContent { padding: 10px; font-size: 11px; }
	.elMapPanelContent h3 { font-size: 15px; font-weight: bold; }
	
	.scroll-scrolly_visible .elMapPanelContent { padding-right: 15px; }
	.scrollbar-inner > .scroll-element, .scrollbar-inner > .scroll-element div { z-index: 5 !important; }

	.elMapPanelTable {  border-collapse: separate; border-radius: 10px; }
	.elMapPanelTable td { padding: 5px; }
	.elMapPanelTable tr:first-child td:first-child { border-top-left-radius: 5px; }
	.elMapPanelTable tr:first-child td:last-child { border-top-right-radius: 5px; }
	.elMapPanelTable tr:last-child td:first-child { border-bottom-left-radius: 5px; }
	.elMapPanelTable tr:last-child td:last-child { border-bottom-right-radius: 5px; }
	.elMapPanelRowAlt_1 { background: #eee; }
	.elMapPanelRowAlt_2 { background: #e0e0e0; }

      	.ol-rotate { top: 3em; }

    </style>

<?php } ?>
