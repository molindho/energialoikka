<?php
/**
Plugin Name: Energialoikka-pluginswiper
Plugin URI: http://www.energialoikka.fi/
Description: Energialoikka.fi-palvelun loikkaesimerkkien selaus
Version: 0.1
Author: Matti Lindholm
Author URI: ?
License: ?
Copyright: ?
*/

	wp_enqueue_style( 'jTinder', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/css/jTinder.css' );
	//wp_enqueue_style( 'jmspinner', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/css/jm.spinner.css' );
	wp_enqueue_style( 'jquery-loading', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/css/jquery-loading.css' );
	wp_enqueue_style( 'jquery-tablesorter', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery.tablesorter/themes/el/style.css' );


	//wp_enqueue_script( 'jQuery', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery.min.js', array( '' ) );		
	wp_enqueue_script( 'Transform2d', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery.transform2d.js', array( 'jquery' ) );		
	wp_enqueue_script( 'jTinder', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery.jTinder.js', array( 'jquery' ) );		
	//wp_enqueue_script( 'jTinder', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/main.js', array( 'jquery' ) );		
	//wp_enqueue_script( 'jmSpinner', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jm.spinner.js', array( 'jquery' ) );		
	wp_enqueue_script( 'jquery-loading', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery-loading.js', array( 'jquery' ) );	

	wp_enqueue_script( 'md5', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery.md5.js', array( 'jquery' ) );	

	wp_enqueue_script( 'jquery-tablesorter', '//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/js/jquery.tablesorter/jquery.tablesorter.min.js', array( 'jquery' ) );

	add_action( 'wp_head', 'my_postswiper_header_scripts' );
	add_shortcode('eloikka_postswiper', 'eloikka_postswiper_output');

	//add_action( 'wp_body', 'eloikka_postswiper_init' );

function my_postswiper_header_scripts() { ?>

    <style>

	#eloikka_postslider_wrap { position: relative; }

	#tinderslide { margin: 0 20px; }

	#eloikka_postslider_prev {
		display: block;
		position: absolute;
		top: 200px;
		left: -50px;
  		width: 0; 
  		height: 0; 
  		border-top: 30px solid transparent;
  		border-bottom: 30px solid transparent; 
  		border-right: 50px solid #1ebdcf; 
		cursor: pointer;		
	}

	#eloikka_postslider_next {
		display: block;
		position: absolute;
		top: 200px;
		right: -50px;
  		width: 0; 
  		height: 0; 
  		border-top: 30px solid transparent;
  		border-bottom: 30px solid transparent; 
  		border-left: 50px solid #1ebdcf; 
		cursor: pointer;
	}

	.ELcaseTitleContainer { width: 100%; position: absolute; top: 20px; left: 10px; opacity: 0.9; text-align: left !important; }
	.ELcaseTitleContainer a { text-align: left; color: #fff; text-decoration: none !important; box-shadow: none !important; webkit-box-shadow: none !important; }
	.ELcaseTitleContainer a:hover { color: #fff !important; text-decoration: underline !important; }


	.ELcaseTitle {
		position: relative;
		width: 80%;
		background: #115c80;
		font-family: calibri, tahoma, sans-serif; 
		font-size: 24px; 
		padding: 15px;'
		text-transform: uppercase;
	}

  	.ELcaseTitleBefore {
    		content: "";
    		position: absolute;
    		Xright: -50px;
		left: 80%;
    		bottom: 0;
    		width: 0;
    		height: 0;
    		border-left: 51px solid #115c80;
    		border-top: 33px solid transparent;
    		border-bottom: 33px solid transparent;
  	}

.ELcaseInfoBadge .measure {
	position: relative; height: 40px; margin-top: -19px; padding: 0px 10px; color: #fff !important; text-align: center; line-height: 40px; 
	font-size: 9.5px; font-weight: bold; letter-spacing: 0.75px; text-transform: uppercase;
}
.ELcaseInfoBadge .measure span { display: inline-block; vertical-align: middle; line-height: 13px; }

.ELcaseInfoBadge .amount {
	position: relative; height: 55px; line-height: 55px; margin-top: -18px; font-size: 20px; font-weight: bold; color: #fff; text-align: center; white-space: nowrap;
}
.ELcaseInfoBadge .amount span { display: inline-block; vertical-align: middle; line-height: 26px; }


.ELcaseInfoBadge .unit {
	position: relative; height: 40px; margin-top: -21px; line-height: 40px; padding: 0px 30px; z-index: 999; font-size: 18px; font-weight: bold; color: #fff; text-align: center; Xwhite-space: nowrap;
	font-size: 11px; font-weight: bold; letter-spacing: 0.75px; 
}

.ELcaseInfoBadge .unit span { display: inline-block; vertical-align: middle; line-height: 13px; }

	.badges { position: relative; top: -62px; opacity: 0.9; }

	.ELcaseInfoBadge {
  		position: relative;
		float: left;
  		width: 100px;
  		height: 57.74px;
		background: #fff;
		Xmargin-right: 1px;
  	}

	.ELcaseInfoBadge:before {
		bottom: 100%;
  		content: "";
	  	position: absolute;
	  	left: 0;
  		width: 0;
  		border-left: 50px solid transparent;
	  	border-right: 50px solid transparent;
  		border-bottom: 28.87px solid #fff;
	}

	.ELcaseInfoBadge:after {
		top: 100%;
  		content: "";
	  	position: absolute;
	  	left: 0;
  		width: 0;
  		border-left: 50px solid transparent;
	  	border-right: 50px solid transparent;
  		border-top: 28.87px solid #fff;
	}

	.ELcaseInfoBadge.ELcolor_1 { background-color: #1ebdcf; }
	.ELcaseInfoBadge.ELcolor_1:before { border-bottom-color: #1ebdcf; }
	.ELcaseInfoBadge.ELcolor_1:after { border-top-color: #1ebdcf; }

	.ELcaseInfoBadge.ELcolor_2 { background-color: #d7df21; }
	.ELcaseInfoBadge.ELcolor_2:before { border-bottom-color: #d7df21; }
	.ELcaseInfoBadge.ELcolor_2:after { border-top-color: #d7df21; }

	.ELcaseInfoBadge.ELcolor_3 { background-color: #e7a8cc; }
	.ELcaseInfoBadge.ELcolor_3:before { border-bottom-color: #e7a8cc; }
	.ELcaseInfoBadge.ELcolor_3:after { border-top-color: #e7a8cc; }

	#eloikka_postslider_table { padding: 15px; }
	table#el_caseTable, #el_caseTable tr, #el_caseTable th, #el_caseTable td { background-color: transparent; border: none; }
	table#el_caseTable { background-color: #eee; border-radius: 10px; border-spacing: 2px; border-collapse: separate; }

	#el_caseTable thead th { text-align: left; line-height: 15px; }
	#el_caseTable thead th span { font-size: 80%; }
	#el_caseTable thead th:first-child { border-top-left-radius: 10px; background-position: center left; }
	#el_caseTable thead th:last-child { border-top-right-radius: 10px; }
	#el_caseTable tbody tr:last-child td:first-child { border-bottom-left-radius: 10px; }
	#el_caseTable tbody tr:last-child td:last-child { border-bottom-right-radius: 10px; }
	#el_caseTable tbody tr:last-child { border-bottom: none; }
	#el_caseTable tbody tr:nth-child(even) { background-color: transparent; }
	#el_caseTable tbody tr:nth-child(odd) { background-color: #fff; }	
	table.tablesorter th, table.tablesorter td { font-size: 14px !important; padding: 8px 5px !important; }
	table.tablesorter th { padding-right: 24px !important; }

th.rotate {
  /* Something you can count on */
  height: 140px;
  white-space: nowrap;
}

th.rotate > div {
  transform: 
    /* Magic Numbers */
    translate(25px, 51px)
    /* 45 is really 360 - 45 */
    rotate(315deg);
  width: 30px;
}
th.rotate > div > span {
  border-bottom: 1px solid #ccc;
  padding: 5px 10px;
}

    </style>

<?php } 

function eloikka_postswiper_output($obj) { 

	$userFavs = implode(',', get_user_favorites());

	$output = '<div id="eloikka_postslider_wrap">';
	$output .= '<div id="eloikka_postslider_prev"></div>';
	$output .= '<div id="tinderslide" data-favs="'.$userFavs.'"><ul></ul><div id="el_postswiper_reswipe_container" style="display: none; position: absolute; height: 450px; width: 100%; top: 0; left: 0; text-align: center; transform: rotate(1deg); border: 1px dotted #ccc; padding-top: 150px;"><h3>Selasit kaikki loikkaesimerkit.<br/>Haluatko aloittaa alusta?</h3><br /><input type="button" style="width: 200px; height: 50px;" id="el_postswiper_reswipe_button" Value="Aloita alusta"></div></div>';
	$output .= '<div id="eloikka_postslider_next"></div>';
	$output .= '</div>';

	$output .= '<div id="eloikka_postslider_table"></div>';

	eloikka_postswiper_init($obj);

	return $output;
}

function eloikka_postswiper_init($obj) { ?>	

	<script type="text/javascript">

		jQuery(document).ready(function($) {

			var myFavorites = '<?php echo $obj['my_favorites']; ?>'; 
			var tagStr = '<?php echo ($obj['post_tag']); ?>';
			var catStr = '<?php echo ($obj['category']); ?>';
			var muniStr = '<?php echo ($obj['kunta']); ?>';
			var sectorStr = '<?php echo ($obj['sector']); ?>';
			var hankeStr = '<?php echo ($obj['hanke']); ?>';
			var userStr = '<?php echo ($obj['user']); ?>';

			var searchData = { 'context': 'embed' };

			if (myFavorites == 'true') { searchData['my_favorites'] = jQuery( '#tinderslide' ).attr( 'data-favs' ); }
			if (tagStr != '') { searchData['tags'] = parseInt(tagStr); }
			if (catStr != '') { searchData['categories'] = parseInt(catStr); }
			if (muniStr != '') { searchData['municipalities'] = parseInt(muniStr); }
			if (sectorStr != '') { searchData['sectors'] = parseInt(sectorStr); }			
			if (hankeStr != '') { searchData['projects'] = parseInt(hankeStr); }
			if (userStr != '') { searchData['users'] = (userStr); }						

			var postsWaiting = 0;

			jQuery('#tinderslide').loading({ circles: 3, overlay: false, base: 0.3 });
			//console.log(searchData);

console.log(searchData);

		$.ajax({
			type: 'GET',
			//url: '//www.energialoikka.fi/wp-json/wp/v2/posts',
			url: '//www.energialoikka.fi/wp-json/el/v1/posts',
			data: searchData,
			dataType: 'json',
			success: function(req) {

				if (req.length > 0) {

				//req = shuffleArray(req);
				//req.sort(function(a, b) { return (a.favorite.count-b.favorite.count); } );

				req.reverse();

				postsWaiting = req.length;

				var elCaseTable = '';
				
				for (var p in req) {

					var title = req[p].title.rendered;
					var url = req[p].link;
					var img = req[p].featured_media.id;
	
					var elCase = '<li class="pane' + req[p].id + '" style="transform: rotate(' + (2-(Math.random()*4)) + 'deg);">';

					elCase += '<div class="img"></div>';
                    			elCase += '<div class="ELcaseTitleContainer"><div class="ELcaseTitleBefore"></div><div class="ELcaseTitle"><a href="' + url + '">' + title + '</a></div></div>';
					elCase += '<div class="badges">';

					// investment
					var inv = parseFloat(req[p].acf.investment);
					var invPretty = ''; var invMeasure = '<br />Investointi'; var invUnit = 'euroa<br />&nbsp;'; var invAmount = inv;
                    			if (!!inv) {
						invPretty = Math.round(inv).toLocaleString().split(',').join(' '); // + ' <span style="font-size: 80%; font-weight: bold;">euroa</span>';
						if (inv > 10000000) {
							invAmount = (Math.round(inv/1000000)).toLocaleString().split(',').join(' ').replace('.', ',');
							invUnit = 'miljoonaa<br />euroa';
						} else if (inv > 1000000) {
							invAmount = (Math.round(inv/100000)/10).toLocaleString().split(',').join(' ').replace('.', ',');
							invUnit = 'miljoonaa<br />euroa';
						} else {
							invAmount = Math.round(inv).toLocaleString().split(',').join(' ').replace('.', ',');
							invUnit = 'euroa<br />&nbsp;';
						}
						elCase += '<div style="float: left;" class="ELcaseInfoBadge ELcolor_1"><div class="measure"><span>' + invMeasure + '</span></div><div class="amount"><span>' + invAmount + '</span></div><div class="unit"><span>' + invUnit + '</span></div></div>'; 
					} 

					// savings
					var save = parseFloat(req[p].acf.annual_return);
					var savePretty = ''; var saveMeasure = '<br />S&auml;&auml;st&ouml;'; var saveUnit = 'euroa/v<br />&nbsp;'; var saveAmount = save;
                    			if (!!save) {
						savePretty = Math.round(save).toLocaleString().split(',').join(' ');
						if (save > 10000000) {
							saveAmount = (Math.round(save/1000000)).toLocaleString().split(',').join(' ').replace('.', ',');
							saveUnit = 'miljoonaa<br />euroa/v';
						} else if (save > 1000000) {
							saveAmount = (Math.round(save/100000)/10).toLocaleString().split(',').join(' ').replace('.', ',');
							saveUnit = 'miljoonaa<br />euroa/v';
						} else {
							saveAmount = Math.round(save).toLocaleString().split(',').join(' ').replace('.', ',');
							saveUnit = 'euroa/v<br />&nbsp;';
						}
						elCase += '<div style="float: left;" class="ELcaseInfoBadge ELcolor_3"><div class="measure"><span>' + saveMeasure + '</span></div><div class="amount"><span>' + saveAmount + '</span></div><div class="unit"><span>' + saveUnit + '</span></div></div>'; 
					}

					// emission reduction
					var red = parseFloat(req[p].acf.emission_reduction);
					var redPretty = ''; var redMeasure = 'P&auml;&auml;st&ouml;-<br />v&auml;hennys'; var redUnit = 'kg CO&#8322;/v'; var redAmount = red;
                    			if (!!red) {
						redPretty = Math.round(red).toLocaleString().split(',').join(' ');
						if (red > 10000) {
							redAmount = (Math.round(red/1000)).toLocaleString().split(',').join(' ').replace('.', ',');
							redUnit = 'tonnia CO&#8322;/v';
						} else if (red > 1000) {
							redAmount = (Math.round(red/100)/10).toLocaleString().split(',').join(' ').replace('.', ',');
							redUnit = 'tonnia CO&#8322;/v';
						} else {
							redAmount = (Math.round(red)).toLocaleString().split(',').join(' ').replace('.', ',');
							redUnit = 'kg CO&#8322;/v';
						}
						elCase += '<div style="float: left;" class="ELcaseInfoBadge ELcolor_2"><div class="measure"><span>' + redMeasure + '</span></div><div class="amount"><span>' + redAmount + '</span></div><div class="unit"><span>' + redUnit + '</span></div></div>'; 
					} 

					
					// favorite button
					elCase += '<div style="float: right;" class="ELcaseInfoBadge ELcolor_3"><span style="font-size: 14px; font-weight: bold;">LAIKKAA<br />LOIKKAA</span>' + req[p].favorite.button + '</div>';

					elCase += '<div style="clear: both;"></div></div>';

					elCase += '</li>';

					jQuery('#tinderslide ul').append(elCase);

					/*
					jQuery('.ELcaseInfoBadge').each(function() {
						var randomRotation = Math.random() * 5;
						jQuery( this ).css( 'transform', 'rotate(' + randomRotation.toString() + 'deg)');
					});
					*/
          

					if (img != 0) {

						jQuery('.pane' + req[p].id + ' .img').css('background', 'url("' + req[p].featured_media.url.medium_large + '") no-repeat scroll center center' );
						jQuery('.pane' + req[p].id + ' .img').css('background-size', 'cover' );

					} else {
						var hash = jQuery.md5(req[p].post_title);
						var rgb = '#' + hash.substring(0, 6);

						jQuery('.pane' + req[p].id + ' .img').css('background', rgb + ' url("//www.energialoikka.fi/wp-content/plugins/energialoikka-postswiper/img/default.png") no-repeat scroll center center' );
						jQuery('.pane' + req[p].id + ' .img').css('background-size', 'cover' );
					}

					elCase = '<tr><td><a href="' + url + '">' + title + '</a></td>';
					elCase += '<td align="right" data-sort-value="' + inv + '">' + invPretty + '</td>';
					elCase += '<td align="right" data-sort-value="' + save + '">' + savePretty + '</td>';
					elCase += '<td align="right" data-sort-value="' + red + '">' + redPretty + '</td>';
					elCase += '</tr>';

					elCaseTable = elCase + elCaseTable;

				}

				initCases();

				elCaseTableHeader = '<thead><tr>';
				elCaseTableHeader += '<th></th>';
				elCaseTableHeader += '<th class="Xrotate"><div><span>Investointi<br /><span class="el_caseTable_unit">(euroa)</span></span></div></th>';
				elCaseTableHeader += '<th>Sääst&ouml;<br /><span class="el_caseTable_unit">(euroa/v)</span></th>';
				elCaseTableHeader += '<th>Pääst&ouml;vähennys<br /><span class="el_caseTable_unit">(kg CO&#8322;/v)</span></th>';
				elCaseTableHeader += '</tr></thead>';

				jQuery('#eloikka_postslider_table').append('<table id="el_caseTable" class="tablesorter">' + elCaseTableHeader + '<tbody>' + elCaseTable + '</tbody></table>');

				jQuery("#el_caseTable").tablesorter({
    					textExtraction : function( node, table, cellIndex ) {
        					return jQuery(node).attr('data-sort-value') || jQuery(node).text();
    					},
    					headers : {
       						0 : { sorter: 'text' },
      						1 : { sorter: 'digit' },
						2 : { sorter: 'digit' },
						3 : { sorter: 'digit' }
    					}
				});

				jQuery('#tinderslide').loading( { hide: true } );

			} else {
				jQuery('#el_postswiper_reswipe_container').html('<h3>Sinulla ei ole yhtään loikkaesimerkkiä suosikeissasi.<br/><br />Aloita loikkaesimerkkien selaaminen <a href="http://www.energialoikka.fi/hyvat-esimerkit/">Hyvät esimerkit</a> -sivulla, ja merkitse suosikkisi sydämellä.</h3><br /><!--<input type="button" style="width: 200px; height: 50px;" id="el_postswiper_reswipe_button" Value="Aloita alusta">-->');
				jQuery('#el_postswiper_reswipe_container').css('transform', 'rotate(0)');
				jQuery('#el_postswiper_reswipe_container').show();
				jQuery('#tinderslide').loading({hide: true});
			}

			},
			error: function(err) {
				console.log(err);
			}
		});

		function initCases() { 

		/**
 		* jTinder initialization
		*/
		$("#tinderslide").jTinder({

			// dislike callback
    			onDislike: function (item) {
	    			// set the status text
        			jQuery('#status').html('Dislike image ' + (item.index()+1));
    			},
			// like callback
    			onLike: function (item) {
	    			// set the status text
        			jQuery('#status').html('Like image ' + (item.index()+1));
    			},
			animationRevertSpeed: 200,
			animationSpeed: 400,
			threshold: 1,
			likeSelector: '.like',
			dislikeSelector: '.dislike'

		});

		jQuery('.ELcaseTitleContainer').each(function() {

			var h = jQuery( this ).height();
			console.log(h);
			jQuery( this ).find('.ELcaseTitleBefore').css('border-left-width', h/3 + 'px');
			jQuery( this ).find('.ELcaseTitleBefore').css('border-top-width', h/2 + 'px');
			jQuery( this ).find('.ELcaseTitleBefore').css('border-bottom-width', h/2 + 'px');

		});

/*    		content: "";
    		position: absolute;
    		right: -50px;
    		bottom: 0;
    		width: 0;
    		height: 0;
    		border-left: 51px solid #115c80;
    		border-top: 33px solid transparent;
    		border-bottom: 33px solid transparent;
*/

		jQuery('#el_postswiper_reswipe_container').show();
		//$("#tinderslide").scrollSnap();
		//jQuery.scrollify({ section : "#tinderslide", });

		/**
 		* Set button action to trigger jTinder like & dislike.
 		*/
		jQuery('.actions .like, .actions .dislike').click(function(e) {
			e.preventDefault();
			jQuery("#tinderslide").jTinder($(this).attr('class'));
		});

		jQuery('#eloikka_postslider_next').click(function(e) {

			//e.preventDefault();
			if (Math.random() < 0.5) {
				jQuery("#tinderslide").jTinder('like');
			} else {
				jQuery("#tinderslide").jTinder('dislike');
			}	

		});

		jQuery('#eloikka_postslider_prev').click(function(e) {

			var count = jQuery('#tinderslide li:hidden').length;

			if (count > 0) {

				var last = jQuery('#tinderslide li:hidden').first();

				last.css('transform', 'none');
				last.css('transform', 'rotate(' + (2-Math.random()*4) + 'deg)');
				last.show();

				$("#tinderslide").jTinder('refresh');
	
				if (count > 1) {
					for ( var i=1; i<count; i++ ) {
						jQuery("#tinderslide").jTinder('dislike');
					}
				}
			}
		});

		}

		jQuery('#el_postswiper_reswipe_button').on('click touchstart', function() {
				
			jQuery('#tinderslide li').each(function() {

				jQuery( this ).css('transform', 'none');
				jQuery( this ).css('transform', 'rotate(' + (2-Math.random()*4) + 'deg)');
				jQuery( this ).show();
			});

			$("#tinderslide").jTinder('refresh');
		});

/**
 * Randomize array element order in-place.
 * Using Durstenfeld shuffle algorithm.
 */
function shuffleArray(array) {
    for (var i = array.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
    }
    return array;
}

		});


	</script>


<?php } ?>
