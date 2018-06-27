(function($) {
	
	function initialize_field( $el ) {

		//globals
		//var mapMode = $el.find('.input-precision').val();
		var mapMode = jQuery('#acf-accuracy input:checked').val();
		var allRegionsArr = [];
		var lastMuni = '';
		var lastReg = '';
		var lastLng = '';
		var lastLat = '';
		var curLat;
		var curLon;
		var centerLat;
		var centerLon;
		var centerZoom;

		var muniLr;
		var regLr;
		var finLr;
		var vector;
		var filter;

		var selectedFeatures;

		jQuery(document).ready(function($) {

			var geolocation = new ol.Geolocation({
			        tracking: true
			});
			
     			geolocation.on('change:position', function() {
		        	var p = geolocation.getPosition();
        			view.setCenter([parseFloat(p[0]), parseFloat(p[1])]);
      			});

			var iconStyle = new ol.style.Style({
  				image: new ol.style.Icon(/** @type {olx.style.IconOptions} */ ({
    					anchor: [0.5, 1],
    					anchorXUnits: 'fraction',
    					anchorYUnits: 'fraction',
   	 				opacity: 0.75,
    					src: '/wp-content/plugins/energialoikka-widgets/marker_1.png'
  				}))
			});

			var source = new ol.source.Vector({wrapX: false});

			centerLat = parseFloat($el.find('div[data-lat]').first().attr('data-lat'));
			centerLon = parseFloat($el.find('div[data-lng]').first().attr('data-lng'));
			centerZoom = parseInt($el.find('div[data-zoom]').first().attr('data-zoom'));

			vector = new ol.layer.Vector({source: source, style: iconStyle});

			var lang = 'fi';

			var mmlLr = new ol.layer.Tile({
				source: new ol.source.TileImage({
					urls: [ '//tile1.kapsi.fi/taustakartta/{z}/{x}/{y}.png', '//tile2.kapsi.fi/taustakartta/{z}/{x}/{y}.png' ],
					attributions: [new ol.Attribution({
						html: 'Taustakartta: <a href="//www.maanmittauslaitos.fi/avoindata">Maanmittauslaitos</a>'
					})],
				}),
        			minResolution: 1,
				maxResolution: 1000
    			});

    			var aeroLr = new ol.layer.Tile({
				source: new ol.source.TileImage({
					urls: [ '//tile1.kapsi.fi/ortokuva/{z}/{x}/{y}.png', '//tile2.kapsi.fi/ortokuva/{z}/{x}/{y}.png' ],
					attributions: [new ol.Attribution({
						html: 'Taustakartta: <a href="//www.maanmittauslaitos.fi/avoindata">Maanmittauslaitos</a>'
					})],
				}),
        			maxResolution: 1,
    			});

			var osmLr = new ol.layer.Tile({
	        		source: new ol.source.OSM( { crossOrigin: null } ),
	        		minResolution: 1000
			});

			var regionsSrc = new ol.source.Vector({
        			format: new ol.format.GeoJSON(),
         			url: '/energialoikka/wp-content/plugins/energialoikka-kartta/kunnat_100k.geojson?ver=2',
				attributions: [new ol.Attribution({
					html: 'Kuntarajat: <a href="http://www.maanmittauslaitos.fi/avoindata">Maanmittauslaitos</a>'
				})]
      			});

			var listenerKey = regionsSrc.on('change', function(e) {
	  			if (regionsSrc.getState() == 'ready') {
					allRegionsArr = regionsSrc.getFeatures();
					//doMapModeChange(mapMode);
					//putSavedToMap();
    					ol.Observable.unByKey(listenerKey);
  				}
			});

			muniLr = new ol.layer.Vector({
      				title: 'Suomen kunnat',
      				source: regionsSrc,
				style: function(e) {

					var style;

					if (e.get('nationalLevel') == '4thOrder') {

						var alphaS = '0'; var alphaF = '0'; var label = '';
						if (mapMode == 'municipality') { 
							alphaS = '0.5'; 
							alphaF = '0.9'; 
							label = e.get('text').split(',')[0].replace('(2:', '');
						}

						style = new ol.style.Style({ 

							stroke: new ol.style.Stroke({ 
								color: 'rgba(0, 0, 0, ' + alphaS + ')',
								width: 1 
							}),
							fill: new ol.style.Fill({ 
								color: 'rgba(255, 255, 255, ' + alphaF + ')'
							}),
							text: new ol.style.Text({
								text: label,
								font: 'bold 12px arial'
							})
						});
					} else {

						style = new ol.style.Style( { display: 'none' } );

					}

					return [style];
				}
  			});

			regLr = new ol.layer.Vector({
      				title: 'Suomen maakunnat',
      				source: regionsSrc,
				style: function(e) {

					var style = new ol.style.Style( { display: 'none' } );

					if (e.get('nationalLevel') == '3rdOrder') {

						var alphaS = '0'; var alphaF = '0'; var label = '';
						if (mapMode == 'region') { 
							alphaS = '0.5'; 
							alphaF = '0.9'; 
							label = e.get('text').split(',')[0].replace('(2:', '');
						}

						style = new ol.style.Style({ 

							stroke: new ol.style.Stroke({ 
								color: 'rgba(0, 0, 0, ' + alphaS + ')',
								width: 1
							}),
							fill: new ol.style.Fill({ 
								color: 'rgba(255, 255, 255, ' + alphaF + ')'
							}) ,
							text: new ol.style.Text({
								text: label,
								font: 'bold 12px arial'
							})
						});
					}
					return [style];
				}
  			});  

			finLr = new ol.layer.Vector({
      				title: 'Suomi',
      				source: regionsSrc,
				style: function(e) {
	
					var style = new ol.style.Style( { display: 'none' } );

					if (e.get('nationalLevel') == '2ndOrder') {

						var alphaS = '0'; var alphaF = '0';
						if (mapMode == 'finland') { 
							alphaS = '0.5'; 
							alphaF = '0.9'; 
						}

						style = new ol.style.Style({ 

							fill: new ol.style.Fill({ 
								color: 'rgba(255, 255, 255, ' + alphaF + ')'
							})

						});
					}
					return [style];
				}
  			});  

	 		var map = new ol.Map({
	        		layers: [
	          			osmLr,
					mmlLr,
					aeroLr,
					finLr,
					regLr,					
					muniLr,
					vector
		        	],
		        	target: 'elMap',
	        		controls: ol.control.defaults({
	          			attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
	            				collapsible: true
		          		})
		          	}).extend([
	        	  		new ol.control.FullScreen(),
	          				//new ol.control.LayerSwitcher()
		          	]),	
		          	loadTilesWhileAnimating: true
		      	});

			// Custom filter
			filter = new ol.filter.Colorize();
			map.addFilter(filter);
			filter.setActive(false);

	      		map.setView(new ol.View({
		        	center: ol.proj.transform([centerLon, centerLat], 'EPSG:4326', 'EPSG:3857' ),
			        zoom: centerZoom 
			})); 

			var selectMunis = new ol.interaction.Select({ layers: function(layer) {
        			return (layer === muniLr);
			}});

			var selectRegs = new ol.interaction.Select({ layers: function(layer) {
        			return (layer === regLr);
			}});

			var select = new ol.interaction.Select();
     			//map.addInteraction(select); 

   			selectedFeatures = select.getFeatures();	    
			
			selectMunis.on('select', function() {
						
				if (selectMunis.getFeatures().getLength === 0) {
		            	
					// do nothing?	
		       		
				} else {

					//var nameArr = [];
					var zoomToExt = ol.extent.createEmpty();

					selectMunis.getFeatures().forEach(function(mf) {

						ol.extent.extend(zoomToExt, mf.getGeometry().getExtent())

						//var muniName = mf.get('text').split(',')[0].replace('(2:', '');
						//nameArr.push(muniName);
					});
	
					map.getView().fit(zoomToExt, { duration: 1000} );
							
					lastLng = '';
					lastLat = '';
					lastMuni = '';
					lastReg = '';
					vector.getSource().clear();
				}

				mapSelectionUpdated();
			});

			selectRegs.on('select', function() {
						
				if (selectRegs.getFeatures().getLength === 0) {
		            	
					// do nothing?	
		       		
				} else {
					 
					var extent = ol.extent.createEmpty();
				
					selectRegs.getFeatures().forEach(function(feature) {
						ol.extent.extend(extent, feature.getGeometry().getExtent())
					});
	
					map.getView().fit(extent, { duration: 1000} ); 
									
					lastLng = '';
					lastLat = '';
					lastMuni = '';
					lastReg = '';

					vector.getSource().clear();
				}
				mapSelectionUpdated();
			});


	      		var draw = new ol.interaction.Draw({
        	   		source: source,
            			type: /** @type {ol.geom.GeometryType} */ ("Point")
	          	});
     			//map.addInteraction(draw); 

    			draw.on('drawstart', function(e) {
				source.clear();
			});

    			draw.on('drawend', function(e) {

				lastLng = '';
				lastLat = '';
				lastMuni = '';
				lastReg = '';

				mapSelectionUpdated();

			});

			var geocoder = new Geocoder('nominatim', {
  				provider: 'osm', //'photon', //'osm', //'mapquest',
  				//key: '__some_key__',
  				lang: 'fi-FI', //en-US, fr-FR
  				placeholder: 'Kirjoita osoite ...',
  				targetType: 'glass-button', //'text-input',
				featureStyle: iconStyle,  
				limit: 5,
				countrycodes: 'fi', 
				autoComplete: true,
				autoCompleteMinLength: 2,
  				keepOpen: false,
				preventDefault: true 
			});
			map.addControl(geocoder);

			geocoder.on('addresschosen', function(evt){

  				var feature = evt.feature,
      				coord = evt.coordinate,
      				address = evt.address,
				city = evt.address.details.city;
				if (typeof city == 'undefined') { city = evt.address.details.name; }

				if (mapMode == 'municipality') {
					
					var muniF;

					if (allRegionsArr.length == 0) {
						allRegionsArr = regionsSrc.getFeatures();
					}

					for (var m in allRegionsArr) {
						if (allRegionsArr[m].get('text').indexOf(city + ',') > 0) {
							muniF = allRegionsArr[m];
							break;
						}
					}					

					selectMunis.getFeatures().push(muniF);
					var extent = ol.extent.createEmpty();
					//muniF.getGeometry().getExtent();

					selectMunis.getFeatures().forEach(function(feature){ 
						ol.extent.extend(extent, feature.getGeometry().getExtent());
					});
       	
					map.getView().fit(extent, { duration: 1000} );

					//mapSelectionUpdated();

  				} else {

					if (mapMode == 'region') {
		      				map.setView(new ol.View({
				       	 		center: coord,
					       	 	zoom: 10 
						}));
						//mapSelectionUpdated();

					} else {

						source.clear();		
					
						// add marker
						var iconFeature = new ol.Feature({
  							geometry: new ol.geom.Point(coord), //ol.proj.transform([lon, lat], 'EPSG:4326', 'EPSG:3857' )),
							name: 'Location'
						});
						source.addFeature(iconFeature);

		      				map.setView(new ol.View({ center: coord, zoom: 16 }));

						
					}
				}

				map.once('postrender', function(event) {

					mapSelectionUpdated();

				});

				     	    
			});

			regionsSrc.refresh({force:true});
				
			setTimeout(function() {
				putSavedToMap();
				doMapModeChange();
			}, 500);				



			function mapSelectionUpdated() {

			    setTimeout(function() {

				// clear old selections
				jQuery('#acf-administrative_area option').prop('selected', false);
				$el.find('input.input-muni').val('');
				$el.find('input.input-region').val('');
				$el.find('.input-lat').val('');
				$el.find('.input-lng').val('');
				
				var out = '';
				var muniName;
				var regName;
				var regsOut;

				if (mapMode == 'precise' && source.getFeatures().length > 0) {

					out = '<div style="margin-top: 8px; margin-right: 5px; background-color: #fff; border-radius: 5px; padding: 2px 8px; float: left; font-weight: bold;">Valittu:</div>';
					
					var muniF;
					var regF;

					var coord = source.getFeatures()[0].getGeometry().getCoordinates();

					var lonlat = ol.proj.transform(coord, 'EPSG:3857', 'EPSG:4326');
 					var lon = lonlat[0];
 					var lat = lonlat[1];

					out += '<div style="margin-top: 8px; margin-right: 5px; background-color: #eee; border-radius: 5px; padding: 2px 8px; float: left;">' + Math.round(lat*10000)/10000 + '&deg; N, ' + Math.round(lon*10000)/10000 + '&deg; E</div>';
					
					var pixel = map.getPixelFromCoordinate(coord);

					map.forEachFeatureAtPixel(pixel, 
						function(feature, layer) {
          						if (layer == muniLr) { muniF = feature; }
							else if (layer == regLr) { regF = feature; }
        					}
					);

					var muniName = muniF.get('text').split(',')[0].replace('(2:', '');
					var regName = regF.get('text').split(',')[0].replace('(2:', '');

					//$muniOptions = jQuery('#acf-administrative_area option:contains("'+muniName+'")').filter(function() { return ($(this).text().length == (muniName.length + 4)); });
					//$muniOptions.prop('selected', true);
					
					jQuery('#acf-administrative_area option:contains("'+muniName+'")').prop('selected', true);
					jQuery('#acf-administrative_area option:contains("'+regName+'")').prop('selected', true);

					$el.find('input.input-muni').val(muniName);
					$el.find('input.input-region').val(regName);
					$el.find('.input-lat').val(lat);
					$el.find('.input-lng').val(lon);
						
					out += '<div style="margin-top: 8px; margin-right: 5px; background-color: #ffeeee; border-radius: 5px; padding: 2px 8px; float: left;">' + muniName + '</div>';
					out += '<div style="margin-top: 8px; margin-right: 5px; background-color: #eeffee; border-radius: 5px; padding: 2px 8px; float: left;">' + regName + '</div>';

					out += '<div style="clear: both;"></div>';

				} else if (mapMode == 'municipality') {

					var munisOut = [];
					var regsOut = {};

					selectMunis.getFeatures().forEach(function(mFeat) { 

						muniName = mFeat.get('text').split(',')[0].replace('(2:', '');
						
						munisOut.push('<div style="margin-top: 8px; margin-right: 5px; background-color: #ffeeee; border-radius: 5px; padding: 2px 8px; float: left;">' + muniName + '</div>');

						regName = getRegNameFromMuni(mFeat);	
					
						regsOut[regName] = '<div style="margin-top: 8px; margin-right: 5px; background-color: #eeffee; border-radius: 5px; padding: 2px 8px; float: left;">' + regName + '</div>';

						//$muniOptions = jQuery('#acf-administrative_area option:contains("'+muniName+'")').filter(function() { return ($(this).text().length == (muniName.length + 2)); });
						//$muniOptions.prop('selected', true);
	
						jQuery('#acf-administrative_area option:contains("'+muniName+'")').prop('selected', true);
						jQuery('#acf-administrative_area option:contains("'+regName+'")').prop('selected', true);
	
						//$el.find('input.input-muni').val(muniName);
						//$el.find('input.input-region').val(regName);

					});

					if (munisOut.length > 0) {

						out = '<div style="margin-top: 8px; margin-right: 5px; background-color: #fff; border-radius: 5px; padding: 2px 8px; float: left; font-weight: bold;">Valittu:</div>';
						out += munisOut.join('');

						for (var r in regsOut) { out += regsOut[r]; }
					
						out += '<div style="clear: both;"></div>';
					}
					
				} else if (mapMode == 'region') {

					var regsOutA = [];
 
					selectRegs.getFeatures().forEach(function(feature) { 

						regName = feature.get('text').split(',')[0].replace('(2:', '');
						regsOutA.push('<div style="margin-top: 8px; margin-right: 5px; background-color: #eeffee; border-radius: 5px; padding: 2px 8px; float: left;">' + regName + '</div>');
						jQuery('#acf-administrative_area option:contains("'+regName+'")').prop('selected', true);	
					});

					if (regsOutA.length > 0) {

						out = '<div style="margin-top: 8px; margin-right: 5px; background-color: #fff; border-radius: 5px; padding: 2px 8px; float: left; font-weight: bold;">Valittu:</div>';
						out += regsOutA.join('');
						out += '<div style="clear: both;"></div>';
					}

				} else if (mapMode == 'finland') {

					jQuery('#acf-administrative_area option:contains("Koko Suomi")').prop('selected', true);	

					out = '<div style="margin-top: 8px; margin-right: 5px; background-color: #fff; border-radius: 5px; padding: 2px 8px; float: left; font-weight: bold;">Valittu:</div>';
					out += '<div style="margin-top: 8px; margin-right: 5px; background-color: #eeffee; border-radius: 5px; padding: 2px 8px; float: left;">Koko Suomi</div>';
					out += '<div style="clear: both;"></div>';
					
				} else {

					// do nothing?

				}

				jQuery('#el-map-info').html(out);

			    }, 1200);

			}

			function getRegionWithName(name) {

				if (allRegionsArr.length == 0) {
					allRegionsArr = regionsSrc.getFeatures();
				}

				var f;
				for (var r in allRegionsArr) {

					if (allRegionsArr[r].get('text').indexOf(name + ',') > 0) {
						f = allRegionsArr[r];
						break;
					}
				}
				return f;
			}

			function getMuniNameFromCoordinate(coord) {

				var pixel = map.getPixelFromCoordinate(coord);

				var f;

				map.forEachFeatureAtPixel(pixel, 
					function(feature, layer) {
          					if (layer == muniLr) { f = feature; }
        				}
				);

				return f.get('text').split(',')[0].replace('(2:', '');
			}

			function getRegNameFromCoordinate(coord) {

				var pixel = map.getPixelFromCoordinate(coord);

				var f;

				map.forEachFeatureAtPixel(pixel, 
					function(feature, layer) {
          					if (layer == regLr) { f = feature; }
        				}
				);

				return f.get('text').split(',')[0].replace('(2:', '');
			}


			function getRegNameFromMuni(muni) {

				var muniGeom = muni.getGeometry();
				var muniPoly = muniGeom.getPolygons()[0];
				var muniCent = muniPoly.getInteriorPoint();

				var pixel = map.getPixelFromCoordinate(muniCent.getCoordinates());

				var f;

				map.forEachFeatureAtPixel(pixel, 
					function(feature, layer) {
          					if (layer == regLr) { f = feature; }
						//console.log('feat: ' + feature.get('text').split(',')[0].replace('(2:', ''));
        				}
				);

				return f.get('text').split(',')[0].replace('(2:', '');
			}	

			jQuery('#acf-accuracy input').on('change', function() {
				doMapModeChange(jQuery( this ).val());
			});

			function putSavedToMap() {

				source.clear();		

				var munisArr = [];
				var regsArr = [];

				jQuery('#acf-administrative_area option:checked').each(function() {

					option = jQuery( this ).html();

					if (option.charCodeAt(0) == 8212) {
						if (option.charCodeAt(2) == 8212) {
							munisArr.push(option.substr(4));
							selectMunis.getFeatures().push(getRegionWithName(option.substr(4)));
						} else {
							regsArr.push(option.substr(2));
							selectRegs.getFeatures().push(getRegionWithName(option.substr(2)));
						}							
					} else {
						// do nothing with Koko Suomi
					}
				});

				// add marker
				var lon = parseFloat($el.find('.input-lng').val());
				var lat = parseFloat($el.find('.input-lat').val());
				
				if (!isNaN(lon) && !isNaN(lat)) {
					var iconFeature = new ol.Feature({
  						geometry: new ol.geom.Point(ol.proj.transform([lon, lat], 'EPSG:4326', 'EPSG:3857' )),
						name: 'Location'
					});
					source.addFeature(iconFeature);
				}

				switch ( mapMode ) {
					case 'finland':
						map.setView(new ol.View({ center: ol.proj.transform([centerLon, centerLat], 'EPSG:4326', 'EPSG:3857' ), zoom: centerZoom }));
						break;


					case 'region':

						if (regsArr.length > 0) {
							var zoomToExt = ol.extent.createEmpty();
							selectRegs.getFeatures().forEach(function(mf) {
								ol.extent.extend(zoomToExt, mf.getGeometry().getExtent());
							});
							map.getView().fit(zoomToExt, { duration: 0} );
						} else {
							map.setView(new ol.View({ center: ol.proj.transform([centerLon, centerLat], 'EPSG:4326', 'EPSG:3857' ), zoom: centerZoom }));
						}
						break;

					case 'municipality':
						if (munisArr.length > 0) {
							var zoomToExt = ol.extent.createEmpty();
							selectMunis.getFeatures().forEach(function(mf) {
								ol.extent.extend(zoomToExt, mf.getGeometry().getExtent());
							});
							map.getView().fit(zoomToExt, { duration: 0} );
						} else {
							map.setView(new ol.View({ center: ol.proj.transform([centerLon, centerLat], 'EPSG:4326', 'EPSG:3857' ), zoom: centerZoom }));
						}
						break;

					case 'precise':

						if (!isNaN(lon) && !isNaN(lat)) {
			      				map.setView(new ol.View({ center: iconFeature.getGeometry(), zoom: 10 }));
						} else {
							map.setView(new ol.View({ center: ol.proj.transform([centerLon, centerLat], 'EPSG:4326', 'EPSG:3857' ), zoom: centerZoom }));
						}
						break;

					default:

						vector.setVisible(false);
						muniLr.setVisible(true);
						regLr.setVisible(true);
						filter.setActive(true);

						map.removeInteraction(selectMunis);
						map.removeInteraction(selectRegs);
						map.removeInteraction(draw);

						map.setView(new ol.View({ center: ol.proj.transform([centerLon, centerLat], 'EPSG:4326', 'EPSG:3857' ), zoom: centerZoom }));
						
				}


			}


			function doMapModeChange( newMode ) {

				var munisArr = [];
				var regsArr = [];

				if (typeof newMode == 'undefined') {

					newMode = jQuery('#acf-accuracy input:checked').val();					
				}

				jQuery('#acf-administrative_area option:checked').each(function() {

					option = jQuery( this ).html();
	
					if (option.charCodeAt(0) == 8212) {

						if (option.charCodeAt(2) == 8212) {
							munisArr.push(option.substr(4));
						} else {
							regsArr.push(option.substr(2));
						}
					} else {

						// do nothing with "Koko Suomi"
					}
				});

				mapMode = newMode;

				var lng = $el.find('.input-lng').val();
				var lat = $el.find('.input-lat').val();

				$el.find('input.input-lat').val('');
				$el.find('input.input-lng').val('');

				filter.setActive(false);
							
				switch ( mapMode ) {

					case 'finland':

						muniLr.setVisible(true);
						regLr.setVisible(true);
						finLr.setVisible(true);
						vector.setVisible(false);

						map.setView(new ol.View({ center: [2900000, 9700000], zoom: 4, duration: 1000 }));

						map.removeInteraction(selectMunis);
						map.removeInteraction(selectRegs);
						map.removeInteraction(draw);

						//mapSelectionUpdated();

						regionsSrc.refresh({force:true});

						break;

					case 'region':

						selectRegs.getFeatures().clear();

						for (reg in regsArr) {
							selectRegs.getFeatures().push(getRegionWithName(regsArr[reg]));	
						}

						muniLr.setVisible(true);
						regLr.setVisible(true);
						finLr.setVisible(true);
						vector.setVisible(false);

						map.removeInteraction(draw);
						map.removeInteraction(selectMunis);
						map.addInteraction(selectRegs);

						if (regsArr.length > 0) {
							var zoomToExt = ol.extent.createEmpty();
							selectRegs.getFeatures().forEach(function(mf) {
								ol.extent.extend(zoomToExt, mf.getGeometry().getExtent());
							});
							map.getView().fit(zoomToExt, { duration: 1000} );
						} else {
							regionsSrc.refresh({force:true});
						}

						break;

					case 'municipality':

						selectMunis.getFeatures().clear();
						for (muni in munisArr) {
							selectMunis.getFeatures().push(getRegionWithName(munisArr[muni]));
						}

						muniLr.setVisible(true);
						regLr.setVisible(true);
						finLr.setVisible(true);
						vector.setVisible(false);

						map.removeInteraction(draw);
						map.addInteraction(selectMunis);
						map.removeInteraction(selectRegs);

						if (munisArr.length > 0) {
							var zoomToExt = ol.extent.createEmpty();
							selectMunis.getFeatures().forEach(function(mf) {
								ol.extent.extend(zoomToExt, mf.getGeometry().getExtent());
							});
							map.getView().fit(zoomToExt, { duration: 1000} );
						} else {							
							regionsSrc.refresh({force:true});
						}

						break;

					case 'precise':

						vector.setVisible(true);
						muniLr.setVisible(true);
						regLr.setVisible(true);
						finLr.setVisible(true);

						if (source.getFeatures().length > 0) {
							map.setView(new ol.View({ center: source.getFeatures()[0].getGeometry().getCoordinates(), zoom: 14, duration: 1000 }));
							$el.find('input.input-lat').val(lat);
							$el.find('input.input-lng').val(lng);
						}

						map.removeInteraction(selectMunis);
						map.removeInteraction(selectRegs);
						map.addInteraction(draw);

						//regionsSrc.refresh({force:true});

						break;

					default:

						vector.setVisible(false);
						muniLr.setVisible(true);
						regLr.setVisible(true);
						finLr.setVisible(true);
						filter.setActive(true);

						map.removeInteraction(selectMunis);
						map.removeInteraction(selectRegs);
						map.removeInteraction(draw);

						regionsSrc.refresh({force:true});						
				}

				mapSelectionUpdated();
			}

		});

	}
	
	
	if( typeof acf.add_action !== 'undefined' ) {
	
		/*
		*  ready append (ACF5)
		*
		*  These are 2 events which are fired during the page load
		*  ready = on page load similar to $(document).ready()
		*  append = on new DOM elements appended via repeater field
		*
		*  @type	event
		*  @date	20/07/13
		*
		*  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
		*  @return	n/a
		*/
		
		acf.add_action('ready append', function( $el ){
			
			// search $el for fields of type 'energialoikka_openlayers'
			acf.get_fields({ type : 'energialoikka_openlayers'}, $el).each(function(){
				
				initialize_field( $(this) );
				
			});
			
		});
		
		
	} else {
		
		
		/*
		*  acf/setup_fields (ACF4)
		*
		*  This event is triggered when ACF adds any new elements to the DOM. 
		*
		*  @type	function
		*  @since	1.0.0
		*  @date	01/01/12
		*
		*  @param	event		e: an event object. This can be ignored
		*  @param	Element		postbox: An element which contains the new HTML
		*
		*  @return	n/a
		*/
		
		$(document).on('acf/setup_fields', function(e, postbox){
			
			$(postbox).find('.field[data-field_type="energialoikka_openlayers"]').each(function(){
				
				initialize_field( $(this) );
				
			});
		
		});
	
	
	}


})(jQuery);
