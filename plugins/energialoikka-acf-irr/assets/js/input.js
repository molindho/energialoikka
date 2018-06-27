(function($){
	
	
	function initialize_field( $el ) {
		
		//$el.doStuff();

		jQuery(document).ready(function($) {

			$el.find('.acf-el-irr-field-wrap').each(function($) {

				var $target = jQuery( this );

				var investmentName = jQuery( this ).attr('data-investment');
				var annualreturnName = jQuery( this ).attr('data-annual_return');
				var lifespanName = jQuery( this ).attr('data-lifespan');

				function updateRate() {

					var investment = parseFloat(jQuery('input#acf-field-' + investmentName).val());
					var annualreturn = parseFloat(jQuery('input#acf-field-' + annualreturnName).val());
					var lifespan = parseFloat(jQuery('input#acf-field-' + lifespanName).val());

					if (!isNaN(investment) && !isNaN(annualreturn) && !isNaN(lifespan)) {
		
						var returns = new Array();
						for (var i=0; i<lifespan; i++) { returns.push(annualreturn); }
	
						var cArr = [];
						cArr.push(-investment);					
						for (var i=0; i<lifespan; i++) { cArr.push(annualreturn); }

						//console.log(cArr);
						//console.log(IRR(cArr, 0.01));
	 	
						var finance = new Finance();
						var rate = -999;
						try {
		    					//rate = eval('finance.IRR(-investment, ' + returns.join(', ') + ');');
							rate = IRR(cArr, 0.01) * 100;
						}
						catch(err) {
					//console.log(err);
    							rate = -999;
						}
					//console.log(rate);
						if (rate != -999) {
							if (Number.isInteger(lifespan)) {
								$target.find('input').val(Math.round(rate));
								$target.find('.acf-el-irr-readonly').val(Math.round(rate) + ' %');
							} else {
								$target.find('input').val(Math.round(rate*10)/10);
								$target.find('.acf-el-irr-readonly').val(Math.round(rate*10)/10 + ' %');
							}
							
						} else {
							$target.find('input').val('');
						}
					} else {

						$target.find('input').val('');
					}
				}
	
				jQuery('input#acf-field-' + jQuery( this ).attr('data-investment') + ', input#acf-field-' + jQuery( this ).attr('data-annual_return') + ', input#acf-field-' + jQuery( this ).attr('data-lifespan')).on('change', function() {
	
					updateRate();

				}); 

				updateRate();

			});
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
			
			// search $el for fields of type 'energialoikka_irr'
			acf.get_fields({ type : 'energialoikka_irr'}, $el).each(function(){
				
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
			
			$(postbox).find('.field[data-field_type="energialoikka_irr"]').each(function(){
				
				initialize_field( $(this) );
				
			});
		
		});
	
	
	}

function IRR(values, guess) {
  // Credits: algorithm inspired by Apache OpenOffice
  
  // Calculates the resulting amount
  var irrResult = function(values, dates, rate) {
    var r = rate + 1;
    var result = values[0];
    for (var i = 1; i < values.length; i++) {
      result += values[i] / Math.pow(r, (dates[i] - dates[0]) / 365);
    }
    return result;
  }

  // Calculates the first derivation
  var irrResultDeriv = function(values, dates, rate) {
    var r = rate + 1;
    var result = 0;
    for (var i = 1; i < values.length; i++) {
      var frac = (dates[i] - dates[0]) / 365;
      result -= frac * values[i] / Math.pow(r, frac + 1);
    }
    return result;
  }

  // Initialize dates and check that values contains at least one positive value and one negative value
  var dates = [];
  var positive = false;
  var negative = false;
  for (var i = 0; i < values.length; i++) {
    dates[i] = (i === 0) ? 0 : dates[i - 1] + 365;
    if (values[i] > 0) positive = true;
    if (values[i] < 0) negative = true;
  }
  
  // Return error if values does not contain at least one positive value and one negative value
  if (!positive || !negative) return '#NUM!';

  // Initialize guess and resultRate
  var guess = (typeof guess === 'undefined') ? 0.1 : guess;
  var resultRate = guess;
  
  // Set maximum epsilon for end of iteration
  var epsMax = 1e-10;
  
  // Set maximum number of iterations
  var iterMax = 50;

  // Implement Newton's method
  var newRate, epsRate, resultValue;
  var iteration = 0;
  var contLoop = true;
  do {
    resultValue = irrResult(values, dates, resultRate);
    newRate = resultRate - resultValue / irrResultDeriv(values, dates, resultRate);
    epsRate = Math.abs(newRate - resultRate);
    resultRate = newRate;
    contLoop = (epsRate > epsMax) && (Math.abs(resultValue) > epsMax);
  } while(contLoop && (++iteration < iterMax));

  if(contLoop) return '#NUM!';

  // Return internal rate of return
  return resultRate;
}

})(jQuery);
