(function() {

// Localize jQuery variable
var jQuery;

/******** Load jQuery if not present *********/
jquerypresent = false;
if (window.jQuery !== undefined) {
	verArr = window.jQuery.fn.jquery.split('.');
	if (verArr[0] > 1 || (verArr[0] == 1 && verArr[1] > 9)) { 
		jquerypresent = true; 
	}
}
	
if (!jquerypresent) {
    var script_tag = document.createElement('script');
    script_tag.setAttribute("type","text/javascript");
    script_tag.setAttribute("src",
        "//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
    if (script_tag.readyState) {
      script_tag.onreadystatechange = function () { // For old versions of IE
          if (this.readyState == 'complete' || this.readyState == 'loaded') {
              scriptLoadHandler();
          }
      };
    } else {
      script_tag.onload = scriptLoadHandler;
    }
    // Try to find the head, otherwise default to the documentElement
    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
} else {
    // The jQuery version on the window is the one we want to use
    jQuery = window.jQuery;
    main();
}


/******** Called once jQuery has loaded ******/
function scriptLoadHandler() {
    // Restore $ and window.jQuery to their previous values and store the
    // new jQuery in our local jQuery variable
    jQuery = window.jQuery.noConflict();
    // Call our main function
    main(); 
}

function main() {

	// globals
	var cos30 = Math.cos(30 * (Math.PI/180));

	// Load javascript UI
	//$.getScript( '//code.jquery.com/ui/1.12.1/jquery-ui.js', function() {
	//$.getScript( '//www.jarviwiki.fi/common/slide-to-submit/js/slide-to-submit.js', function() {

	jQuery.getScript( '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery.md5.js' );
	jQuery.getScript( '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery-loading.js' );

	
    	jQuery(document).ready(function() { 

		//console.log('ready');

        	/******* Load CSS *******/
        	var css_link = jQuery("<link>", { 
        	    rel: "stylesheet", 
        	    type: "text/css", 
        	    href: "//www.energialoikka.fi/loikkawidget/main.css" 
        	});
        	css_link.appendTo('head');    

        	css_link = jQuery("<link>", { 
        	    rel: "stylesheet", 
        	    type: "text/css", 
        	    href: "//www.energialoikka.fi/loikkawidget/jquery-loading.css" 
        	});
        	css_link.appendTo('head');  

		widgetCount = 0;

		jQuery('div.loikkawidget').each(function() {

			var rowSize, gridMargin, marginV, curPage, frameColor, frameWidth, taxonomyName, taxonomyLink, taxonomyLogo;

			if (typeof jQuery( this ).attr('data-taxonomy-type') != 'undefined') { taxonomyType = jQuery( this ).attr('data-taxonomy-type'); } else { taxonomyType = ''; }
			if (typeof jQuery( this ).attr('data-taxonomy-id') != 'undefined') { taxonomyId = jQuery( this ).attr('data-taxonomy-id'); } else { taxonomyId = ''; }
			if (typeof jQuery( this ).attr('data-taxonomy-logo') != 'undefined') { taxonomyLogo = jQuery( this ).attr('data-taxonomy-logo'); } else { taxonomyLogo = ''; }
			
			if (typeof jQuery( this ).attr('data-hexagon-rowsize') == 'number') { rowSize = Math.round(jQuery( this ).attr('data-rowsize')); } else { rowSize = 4; }
			if (typeof jQuery( this ).attr('data-container-margin') == 'number') { gridMargin = jQuery( this ).attr('data-padding'); } else { gridMargin = 0; }
			if (typeof jQuery( this ).attr('data-hexagon-space') == 'number') { marginV = jQuery( this ).attr('data-margin'); } else { marginV = 3; }
			if (typeof jQuery( this ).attr('data-background-color') != 'undefined') { bgcolor = jQuery( this ).attr('data-background-color'); } else { bgcolor = '#fff'; }
			if (typeof jQuery( this ).attr('data-title') != 'undefined') { widgetTitle = jQuery( this ).attr('data-title'); } else { widgetTitle = 'Uusimmat loikkaesimerkit'; }

			if (typeof jQuery( this ).attr('data-frame-color') != 'undefined') { 
				frameColor = jQuery( this ).attr('data-frame-color');
				if (frameColor == '') {
					frameColor = '#8dc63f'; frameWidth = 0;
				} else {
					frameWidth = 6;
				}
			} else { 
				frameColor = '#8dc63f'; frameWidth = 6; 
			}

			var even = (rowSize % 2 == 0);

			widgetCount++;

			if (typeof jQuery( this ).attr('id') == 'undefined') {
				widgetID = 'loikkawidget_'+widgetCount;
				jQuery( this ).attr('id', widgetID);
			} else {
				widgetID = jQuery( this ).attr('id');
			}

			var marginH = marginV / cos30;

			// 
			var containerW = jQuery('#'+widgetID).innerWidth();
			//var hexFh = (containerW - (rowSize * marginH) - gridMargin * 2) / (rowSize * 3 + 1);
			var hexFh = (containerW - ((rowSize) * (marginH) * 1) - gridMargin * 2 - 2500/containerW) / (rowSize*3+1);
			var hexFv = hexFh / cos30;
			var hexSize = hexFh * 3 / cos30;

			var header = '<div class="loikkawidget-grid-header"><h3 style="border-top: ' + frameWidth.toString() + 'px solid ' + frameColor + ';">' + widgetTitle + '</h3><div>';
			jQuery( this ).html(header);

			var grid = '<div class="loikkawidget-grid" style="min-height: 100px; ';
			grid += 'padding-top: ' + (hexSize / 2 + gridMargin) + 'px; ';
			grid += 'padding-bottom: ' + (gridMargin) + 'px; ';
			grid += 'padding-left: ' + (hexFh/2 - marginH + gridMargin) + 'px; ';
			grid += 'padding-right: ' + (0) + 'px; ';
			grid += '">';

			jQuery( this ).append(grid);

			var footer = '<div class="loikkawidget-grid-footer"><p style="border-top: ' + (frameWidth/2).toString() + 'px solid ' + frameColor + ';"><i>Loikkaliit&auml;nn&auml;inen</i> - Energia- ja materiaaliloikka - <a href="//www.energialoikka.fi">energialoikka.fi</a></p></div>';
			jQuery( this ).append(footer);

			if (taxonomyType != '' && taxonomyId != '') {

				jQuery.ajax({
					type: 'GET',
					url: '//www.energialoikka.fi/wp-json/wp/v2/' + taxonomyType + '/' + taxonomyId,
					//url: '//www.energialoikka.fi/wp-json/el/v1/posts',
					dataType: 'json',
					widgetID: widgetID,
					success: function(req, textStatus, request) {

						taxonomyName = req.name;
						taxonomyLink = req.link;
						if (taxonomyName == 'GreenEnergyCases') { taxonomyName = 'Green<br />Energy<br />Cases'; }


						//console.log(req);
					},
				});
			}


			function getPage(page) {

				curPage = page;

				showSpinnerOn(jQuery( '#' + widgetID ));
 
			var searchData = {
				'context': 'embed',
				//'my_favorites':,
				//'tags': 38,
				//'categories':,
				//'municipalities': 618,
				//'sectors':,
				//'projects': '',
				'per_page': 1*rowSize,
				'page': page,
				//'offset': 3,
			}
			if (taxonomyType != '' && taxonomyId != '') {
				searchData[taxonomyType] = taxonomyId;
			}

			jQuery.ajax({
				type: 'GET',
				url: '//www.energialoikka.fi/wp-json/wp/v2/posts',
				//url: '//www.energialoikka.fi/wp-json/el/v1/posts',
				data: searchData,
				dataType: 'json',
				widgetID: widgetID,
				success: function(req, textStatus, request) {

					var totPages = request.getResponseHeader('X-WP-TotalPages');

					var containerW = jQuery('#'+widgetID).innerWidth();

					//var marginH = marginV / cos30 ;
					var marginH = marginV / cos30 + containerW/300;


					// 
					
					var hexFh = (containerW - (rowSize * marginH) - gridMargin * 2) / (rowSize * 3 + 1);
					var hexFv = hexFh / cos30;
					var hexSize = hexFh * 3 / cos30;

					var caseCounter = 0;

					var out='';

					if (req.length > 0) {

						//postsWaiting = req.length;

						for (var p in req) {

							var altClass = ' loikkawidget-grid-case-alt_';

							if (caseCounter % rowSize == 0) {
								altClass += '0';
							} else {
								altClass += ((caseCounter-1) % rowSize % 2 + 1).toString();
							}

							var pirClass = ' loikkawidget-grid-nr-in-row_' + (caseCounter % rowSize + 1).toString();

							var title = req[p].title.rendered;
							var titleHash = jQuery.md5(title);
							var rgb = '#' + titleHash.substring(0, 6);
							var titleInt = parseInt('0x' + titleHash.substring(0,1));
							cClass = 'loikkawidget-color-5'; //'loikkawidget-color-' + (Math.round(titleInt/6)).toString();
							
							var img = req[p].featured_media;
							var url = req[p].link;

							//var excerpt = req[p].excerpt.rendered;

							var lCase = '<div data-href="' + url + '" style="display: none; padding: 0; border: 0; ';
							lCase += '" id="loikkawidget-grid-case_' + req[p].id + '" class="loikkawidget-grid-case hexagon loikkawidget-link'+ altClass + pirClass + '" style="">';
							lCase += '<div class="hexagon-in1">';
							lCase += '<div class="hexagon-in2 loikkawidget-grid-img ' + cClass + '" style="Xbackground-color: ' + rgb + ';">';
							lCase += '<div class="loikkawidget-grid-title" style="';

							lCase += '"><span>' + title + '</span></div>';
							lCase += '</div></div></div>';
					
							out += lCase;

//console.log(lCase);
							caseCounter++;

							if (img != 0) {

								jQuery.ajax({
									type: 'GET',
									url: '//www.energialoikka.fi/wp-json/wp/v2/media/' + img,
									dataType: 'json',
									postId: req[p].id,
									success: function(imgReq) {
//console.log(imgReq);
										if (typeof imgReq.media_details.sizes.medium != 'undefined') {
											imgSrc = imgReq.media_details.sizes.medium.source_url;
										} else {
											imgSrc = imgReq.media_details.sizes.full.source_url;
										}
										jQuery('#loikkawidget-grid-case_' + this.postId + ' .loikkawidget-grid-img').css('background-image', 'url("' + imgSrc + '")' );
									}
								});

							} else {
				
								// nothing?

							}

						}

						if (page == 1) {

							//console.log(containerW);
							
							if (caseCounter < rowSize) {
								for (var c=caseCounter; c<rowSize; c++ ) {
									altClass = ' loikkawidget-grid-case-alt_';
									altClass += ((c+1) % 2 + 1).toString();
									
									pirClass = ' loikkawidget-grid-nr-in-row_' + (c+1).toString();
									lCase = '<div style="display: none; padding: 0; border: 0; " id="loikkawidget-grid-case_empty_' + (c+1) + '" class="loikkawidget-grid-case hexagon' + altClass + pirClass + '" style=""><div class="hexagon-in1"><div class="hexagon-in2"></div></div></div>'
									out += lCase;
								}
							}

							// EMloikka logo
							out += '<div style="position: absolute; display: none;';
							out += '" class="loikkawidget-grid-logo"><a href="//www.energialoikka.fi" target="_blank"><img src="//www.energialoikka.fi/loikkawidget/loikka-minitunnus.png" style="width: ' + (hexFh*2.4) + 'px;" /></a></div>';

							// info button
							out += '<div style="position: absolute; display: none;';
							out += '" class="loikkawidget-grid-info hexagon"><div class="hexagon-in1"><div class="hexagon-in2">i</div></div></div>';

							// hankelogo, kunnannimi tai #asiasana
							out += '<div style="position: absolute; display: none; text-align: center;';
							out += '" class="loikkawidget-grid-searchterm">';
							out += '<a style="color: #16becf;" href="' + taxonomyLink + '">';
							out += '<span class="term">#' + taxonomyName + '</span>';
							if (taxonomyLogo != '') {
								out += '<img style="display: block; margin: 0 auto;" src="' + taxonomyLogo + '" />';	
							}
							out += '</a>';
							out += '</div>';
						}

						if (totPages > page) {

							out += '<div title="Hae lisää loikkia" style="position: absolute; width: 0; height: 0; display: none;';
							out += '" class="loikkawidget-grid-more"></div>';
							
						}

					} else {
					
						console.log('No cases!');
						
					}

					out += '<div style="clear: both;"></div></div>';
					jQuery( '#' + this.widgetID + ' .loikkawidget-grid').append(out);
					doResize();

				}, // success
				error: function(err) {
					console.log(err);
				}
			});

			}

			getPage(1); 

			jQuery( document ).on( 'click', '.loikkawidget-grid-more', function() {

				jQuery('.loikkawidget-grid-more').remove();
				getPage(curPage+1);
			}); 


		}); // each widget

		function doResize() {

			// wait for new size
			setTimeout( function() { 
				
				//
				jQuery('div.loikkawidget').each(function() {

					var elem = jQuery( this );
					elem.find('.loikkawidget-grid').css('min-height', '');
					var containerW = (elem.innerWidth());

					if (typeof jQuery( this ).attr('data-hexagon-rowsize') != 'undefined') { rowSize = Math.round(jQuery( this ).attr('data-hexagon-rowsize')); } else { rowSize = (4); }
					if (typeof jQuery( this ).attr('data-container-margin') == 'number') { gridMargin = jQuery( this ).attr('data-padding'); } else { gridMargin = 0; }
					if (typeof jQuery( this ).attr('data-hexagon-space') == 'number') { marginV = jQuery( this ).attr('data-margin'); } else { marginV = 3; }
					if (typeof jQuery( this ).attr('data-background-color') != 'undefined') { bgcolor = jQuery( this ).attr('data-background-color'); } else { bgcolor = '#daff01'; }

					var even = (rowSize % 2 == 0);

					marginV = 5 + (containerW/200)*0.5-4;

					var marginH = marginV / cos30;// + (containerW/200)*2 - 5;
					
					//var hexFh = (containerW - ((rowSize) * marginH * 1) - gridMargin * 2) / (rowSize * 3 + 1);
					var hexFh = Math.floor((containerW - ((rowSize-1) * (marginH) * 1) - gridMargin * 2 - 2500/containerW) / (rowSize*3+1));

					var hexFv = (hexFh / cos30);
					var hexSize = (hexFh * 3 / cos30);

					elem.find('.loikkawidget-grid').css('padding-top', (hexSize / 2 + gridMargin + marginV*2) + 'px');
					elem.find('.loikkawidget-grid').css('padding-bottom', (gridMargin) + 'px');
					elem.find('.loikkawidget-grid').css('padding-left', (hexFh/2 - marginH + gridMargin) + 'px');

					elem.find('.loikkawidget-grid').css('padding-right', (0) + 'px');

					elem.find('.loikkawidget-grid-case').width(hexSize*2);
					elem.find('.loikkawidget-grid-case').height(hexSize);

					elem.find('.loikkawidget-grid-case').css('margin-right', (marginH - hexFh*2).toString() + 'px');
					elem.find('.loikkawidget-grid-case').css('margin-bottom', (marginV).toString() + 'px');
					elem.find('.loikkawidget-grid-case').css('margin-left', (marginH - hexFh*2).toString() + 'px');

					elem.find('.loikkawidget-grid-case-alt_2, .loikkawidget-grid-case-alt_0').css('margin-top', (0 - (hexSize/2 + marginV/2)).toString() + 'px');
					elem.find('.loikkawidget-grid-case-alt_1').css('margin-top', (0).toString() + 'px');
					
					elem.find('.loikkawidget-grid-title').width(hexFh*3+marginH);					
					elem.find('.loikkawidget-grid-title').css('font-size', (hexFv/3.5) + 'px');
					elem.find('.loikkawidget-grid-title').css('line-height', (hexFv/2) + 'px');

					elem.find('.loikkawidget-grid-title').css('left', (hexFh*2-marginH) + 'px');

					setTimeout(function() {
						elem.find('.loikkawidget-grid-title').each(function() {
							jQuery( this ).css('top', (hexFv*3/2-jQuery( this ).height()/2) + 'px');
						}); 
					}, 100);
					elem.find('.loikkawidget-grid-case').show();

					var logoCenter = elem.find('.loikkawidget-grid-nr-in-row_' + rowSize.toString()).first().position().left + elem.find('.loikkawidget-grid-nr-in-row_' + rowSize.toString()).first().width()/2;
					var logo = elem.find('.loikkawidget-grid-logo');
					logo.find('img').width(hexFh*2.4);
					if (even) { logo.css('top', (gridMargin+hexFv*0.1).toString() + 'px'); }
					else { logo.css('bottom', (gridMargin+hexFv*0.1).toString() + 'px'); }
					logo.css('left', (logoCenter - hexFh*2.8).toString() + 'px');
					logo.show();

					var termCenter = elem.find('.loikkawidget-grid-nr-in-row_2').first().position().left + hexFh*1.23 + marginH;
					var term = elem.find('.loikkawidget-grid-searchterm');
					var termW = (hexFh*2.4);
					var termH = (hexFv*1.2);
					term.find('img').css('max-width', (termW) + 'px');
					term.find('img').css('max-height', (termH) + 'px');
					termLen = term.find('.term').html().length;

					if (termLen > 0) {
						if (termLen > 20) {
							term.css('font-size', (hexFv*0.35).toString() + 'px');
							term.css('line-height', (hexFv*0.38).toString() + 'px');
							term.css('margin-top', (hexFv*0.05).toString() + 'px');
						} else if (termLen > 10) {
							term.css('font-size', (hexFv*0.45).toString() + 'px');
							term.css('line-height', (hexFv*0.50).toString() + 'px');
							term.css('margin-top', (hexFv*0.20).toString() + 'px');
						} else {
							term.css('font-size', (hexFv*0.5).toString() + 'px');
							term.css('line-height', (hexFv*0.55).toString() + 'px');
							term.css('margin-top', (hexFv*0.25).toString() + 'px');
						}
					}
					term.width(termW);
					term.height(termH);
					term.css('top', (gridMargin + hexFv * 0.7 - termH/2).toString() + 'px');
					term.css('left', (termCenter - termW/2).toString() + 'px');

					//term.find('span').hide();

					term.show();

					if (even) { var moreCenter = elem.find('.loikkawidget-grid-nr-in-row_3').first().position().left + elem.find('.loikkawidget-grid-nr-in-row_3').first().width()/2; }
					else { var moreCenter = elem.find('.loikkawidget-grid-nr-in-row_' + (rowSize-2).toString()).first().position().left + elem.find('.loikkawidget-grid-nr-in-row_' + (rowSize-2).toString()).first().width()/2; }
					var more = elem.find('.loikkawidget-grid-more');

					more.css('border-left', (hexFh*0.6*0.9).toString() + 'px solid transparent');
					more.css('border-right', (hexFh*0.6*0.9).toString() + 'px solid transparent');
					more.css('border-top', (hexFh*0.9).toString() + 'px solid #daff01');				

					more.css('bottom', (gridMargin+hexFv*0.3).toString() + 'px');
					more.css('left', (moreCenter - hexFh*2.7) + 'px');
					more.show();

					var infoCenter = elem.find('.loikkawidget-grid-nr-in-row_1').first().position().left + elem.find('.loikkawidget-grid-nr-in-row_1').first().width()/2;
					var info = elem.find('.loikkawidget-grid-info');

					info.width(hexSize/2/2);
					info.height(hexSize/2);

					info.css('font-size', (hexFv*0.6).toString() + 'px');
					info.css('line-height', (hexFv*1.5).toString() + 'px');

					info.css('bottom', (gridMargin-hexFv*0.05).toString() + 'px');
					info.css('left', (infoCenter - hexFh*2.65) + 'px');
					info.show();

					hideSpinnerOn(elem);
				});

			 }, 550);
		}

		jQuery( document ).on( 'click', '.loikkawidget-grid-case', function() {

			//window.location.href = jQuery( this ).attr('data-href');
			if (typeof jQuery( this ).attr('data-href') != 'undefined') {
				window.open(jQuery( this ).attr('data-href'), '_blank');
			}
		}); 

		jQuery( window ).on('resize', function() {
			//console.log('resize');
			doResize();
		});

		function showSpinnerOn(elem) {

			elem.find('.citobs_spinner').each(function() {
				jQuery( this ).remove();
			});

			var spinner = '<div class="citobs_spinner" style=""></div>';
			elem.append(spinner);
			elem.show();
		}

		function hideSpinnerOn(elem) {

			elem.find('.citobs_spinner').each(function() {
				jQuery( this ).fadeOut(500, function() {
					jQuery ( this ).remove();
				});
			});

		}

	}); // on document ready

	
} // main()

})(); // We call our anonymous function immediately