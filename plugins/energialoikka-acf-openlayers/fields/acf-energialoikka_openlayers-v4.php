<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_energialoikka_openlayers') ) :


class acf_field_energialoikka_openlayers extends acf_field {
	
	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options
		
		
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct( $settings )
	{
		// vars
		$this->name = 'energialoikka_openlayers';
		$this->label = __('Energialoikka-kartta');
		$this->category = __("Basic",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
			'height'		=> '',
			'center_lat'		=> '',
			'center_lng'		=> '',
			'zoom'			=> ''
		);
		$this->default_values = array(
			'height'		=> '400',
			'center_lat'		=> '-37.81411',
			'center_lng'		=> '144.96328',
			'zoom'			=> '14'
		);
		$this->l10n = array(
			'locating'		=>	__("Locating",'acf'),
			'browser_support'	=>	__("Sorry, this browser does not support geolocation",'acf'),
		);
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// settings
		$this->settings = $settings;

	}
	
	
	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like below) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function create_options( $field )
	{
		// vars
		$key = $field['name'];
		
		?>
	<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Center",'acf'); ?></label>
		<p class="description"><?php _e('Center the initial map','acf'); ?></p>
	</td>
	<td>
		<ul class="hl clearfix">
			<li style="width:48%;">
				<?php 
			
				do_action('acf/create_field', array(
					'type'			=> 'text',
					'name'			=> 'fields['.$key.'][center_lat]',
					'value'			=> $field['center_lat'],
					'prepend'		=> 'lat',
					'placeholder'		=> $this->default_values['center_lat']
				));
				
				?>
			</li>
			<li style="width:48%; margin-left:4%;">
				<?php 
			
				do_action('acf/create_field', array(
					'type'			=> 'text',
					'name'			=> 'fields['.$key.'][center_lng]',
					'value'			=> $field['center_lng'],
					'prepend'		=> 'lng',
					'placeholder'		=> $this->default_values['center_lng']
				));
				
				?>
			</li>
		</ul>
		
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Zoom",'acf'); ?></label>
		<p class="description"><?php _e('Set the initial zoom level','acf'); ?></p>
	</td>
	<td>
		<?php 
		
		do_action('acf/create_field', array(
			'type'			=> 'number',
			'name'			=> 'fields['.$key.'][zoom]',
			'value'			=> $field['zoom'],
			'placeholder'		=> $this->default_values['zoom']
		));
		
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Height",'acf'); ?></label>
		<p class="description"><?php _e('Customise the map height','acf'); ?></p>
	</td>
	<td>
		<?php 
		
		do_action('acf/create_field', array(
			'type'			=> 'number',
			'name'			=> 'fields['.$key.'][height]',
			'value'			=> $field['height'],
			'append'		=> 'px',
			'placeholder'		=> $this->default_values['height']
		));
		
		?>
	</td>
</tr>
		<?php
		
	}
	
	
	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_field( $field )
	{
		// defaults?
		/*
		//$field = array_merge($this->defaults, $field);
		*/

		// default value
		if( !is_array($field['value']) )
		{
			$field['value'] = array();
		}
		
		$field['value'] = wp_parse_args($field['value'], array(
			'precision'	=> '',
			'lat'		=> '',
			'lng'		=> '',
			'muni'  	=> '',
			'region'	=> ''
		));

		// vars
		$o = array(
			'class'		=>	'',
		);
		
		if( $field['value']['precision'] )
		{
			$o['class'] = 'active';
		}
		
		
		$atts = '';
		$keys = array( 
			'data-id'	=> 'id', 
			'data-lat'	=> 'center_lat',
			'data-lng'	=> 'center_lng',
			'data-zoom'	=> 'zoom'
		);
		
		foreach( $keys as $k => $v )
		{
			$atts .= ' ' . $k . '="' . esc_attr( $field[ $v ] ) . '"';	
		}		
		
		// default options
		foreach( $this->default_values as $k => $v )
		{
			if( ! $field[ $k ] )
			{
				$field[ $k ] = $v;
			}	
		}
		
		// perhaps use $field['preview_size'] to alter the markup?
		
		
		// create Field HTML
		?>

		<div class="acf-el-ol-map <?php echo $o['class']; ?>" <?php echo $atts; ?>>

			<div style="display:none;">
				<?php foreach( $field['value'] as $k => $v ): ?>
					<input type="hidden" class="input-<?php echo $k; ?>" name="<?php echo esc_attr($field['name']); ?>[<?php echo $k; ?>]" value="<?php echo esc_attr( $v ); ?>" />
				<?php endforeach; ?>
			</div>
			
			<div class="map" id="elMap" style="height: <?php echo $field['height']; ?>px">
				
			</div>
			<div id="el-map-info"></div>
			
		</div>
		<?php
	}
	
	
	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your create_field() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used
		
		
		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];
		
		// register & include JS
		wp_register_script( 'acf-input-energialoikka_openlayers', "{$url}assets/js/input.js", array('acf-input'), $version );
		wp_enqueue_script('acf-input-energialoikka_openlayers');
		
		// register & include CSS
		//wp_register_style( 'acf-input-energialoikka_openlayers', "{$url}assets/css/input.css", array('acf-input'), $version );
		//wp_enqueue_style('acf-input-energialoikka_openlayers');

		wp_enqueue_style( 'openlayers', '//openlayers.org/en/v4.4.2/css/ol.css' );
		//wp_enqueue_style( 'layerswitchercontrol', '//viglino.github.io/ol3-ext/control/layerswitchercontrol.css' );
		//wp_enqueue_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );

		//wp_enqueue_script( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'ol', '//openlayers.org/en/v4.4.2/build/ol.js', array( 'jquery' ) );
		//wp_enqueue_script( 'polyfill', '//cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL', array( 'ol' ) );
		//wp_enqueue_script( 'layerswitchercontrol', '//viglino.github.io/ol3-ext/control/layerswitchercontrol.js', array( 'ol' ) );

		wp_enqueue_style( 'ol3-geocoder', '//cdn.jsdelivr.net/openlayers.geocoder/latest/ol3-geocoder.min.css' );
		wp_enqueue_script( 'ol3-geocoder', '//cdn.jsdelivr.net/openlayers.geocoder/latest/ol3-geocoder.js', array( 'ol' ) );

		wp_enqueue_style( 'ol3-ext-controlbar', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/ol3-ext/control/controlbar.css' );

		wp_enqueue_script( 'ol3-ext-filter', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/ol3-ext/filter/filter.js', array( 'ol' ) );
		wp_enqueue_script( 'ol3-ext-colorizefilter', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/ol3-ext/filter/colorizefilter.js', array( 'ol' ) );
		wp_enqueue_script( 'ol3-ext-controlbar', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/ol3-ext/control/controlbar.js', array( 'ol', 'jquery' ) );
		wp_enqueue_script( 'ol3-ext-buttoncontrol', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/ol3-ext/control/buttoncontrol.js', array( 'ol' ) );
		wp_enqueue_script( 'ol3-ext-togglecontrol', '//www.energialoikka.fi/wp-content/plugins/energialoikka-kartta/ol3-ext/control/togglecontrol.js', array( 'ol' ) );

		wp_enqueue_script( 'jquery-ui', '//code.jquery.com/ui/1.12.1/jquery-ui.js', array( 'jquery' ) );
		wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );	
	
	}
	
	
	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your create_field() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_head()
	{
		// Note: This function can be removed if not used
	}
	
	
	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your create_field_options() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function field_group_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used
	}

	
	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your create_field_options() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function field_group_admin_head()
	{
		// Note: This function can be removed if not used
	}


	/*
	*  load_value()
	*
		*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in the database
	*/
	
	function load_value( $value, $post_id, $field )
	{
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field )
	{
		//if( empty($value) || empty($value['lat']) || empty($value['lng']) ) {
			
		//	return false;
			
		//}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value( $value, $post_id, $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// perhaps use $field['preview_size'] to alter the $value?
		
		
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  format_value_for_api()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is passed back to the API functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api( $value, $post_id, $field )
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/
		
		// perhaps use $field['preview_size'] to alter the $value?
		
		
		// Note: This function can be removed if not used
		return $value;
	}
	
	
	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	
	function load_field( $field )
	{
		// Note: This function can be removed if not used
		return $field;
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field, $post_id )
	{
		// Note: This function can be removed if not used
		return $field;
	}

}


// initialize
new acf_field_energialoikka_openlayers( $this->settings );


// class_exists check
endif;

?>