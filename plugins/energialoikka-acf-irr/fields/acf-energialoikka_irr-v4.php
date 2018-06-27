<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_energialoikka_irr') ) :


class acf_field_energialoikka_irr extends acf_field {

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
		$this->name = 'energialoikka_irr';
		$this->label = __('Energialoikka-IRR');
		$this->category = __("Basic",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
			'investment'		=> '',
			'annual_return'	=> '',
			'lifespan'	=> ''
		);
		$this->default_values = array(
			'investment'		=> '',
			'annual_return'	=> '',
			'lifespan'	=> ''
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
			<label><?php _e("Investment field",'acf'); ?></label>
			<p class="description"><?php _e('Name of the field where investment costs are entered','acf'); ?></p>
		</td>
		<td>
				<?php

				do_action('acf/create_field', array(
					'type'			=> 'text',
					'name'			=> 'fields['.$key.'][investment]',
					'value'			=> $field['investment'],
					//'prepend'		=> 'investment',
					'placeholder'	=> $this->default_values['investment']
				));

				?>
		</td>
	</tr>

<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Annual return field",'acf'); ?></label>
		<p class="description"><?php _e('Name of the field where annual return is entered','acf'); ?></p>
	</td>
	<td>
		<?php

		do_action('acf/create_field', array(
			'type'			=> 'text',
			'name'			=> 'fields['.$key.'][annual_return]',
			'value'			=> $field['annual_return'],
			'placeholder'	=> $this->default_values['annual_return']
		));

		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Lifespan field",'acf'); ?></label>
		<p class="description"><?php _e('Name of the field where the estimated lifespan in years of the investment is entered','acf'); ?></p>
	</td>
	<td>
		<?php

		do_action('acf/create_field', array(
			'type'			=> 'text',
			'name'			=> 'fields['.$key.'][lifespan]',
			'value'			=> $field['lifespan'],
			//'append'		=> 'px',
			'placeholder'	=> $this->default_values['lifespan']
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

		// vars
		$o = array( 'id', 'class', 'name', 'value', 'Xplaceholder' );
		$e = '';

		$atts = '';
		$keys = array( 
			'data-id'	=> 'id', 
			'data-investment'	=> 'investment',
			'data-annual_return'	=> 'annual_return',
			'data-lifespan'	=> 'lifespan'
		);
		
		foreach( $keys as $k => $v )
		{
			$atts .= ' ' . $k . '="' . esc_attr( $field[ $v ] ) . '"';	
		}

		$e .= '<div class="acf-input-wrap acf-el-irr-field-wrap" ' . $atts . '>';
		$e .= '<div style="display: none;">';
		$e .= '<input ' . $atts . ' type="text" ';

		foreach( $o as $k )
		{
			$e .= ' ' . $k . '="' . esc_attr( $field[ $k ] ) . '"';
		}

		$e .= ' />';
		$e .= '</div>';
		$e .= '<input disabled type="text" class=".acf-el-irr-readonly" placeholder="Sisäinen korko lasketaan automaattiseti kustannusten, säästön ja käyttöiän perusteella" />';
		$e .= '</div>';


		// return
		echo $e;
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
		wp_register_script( 'acf-input-energialoikka_irr', "{$url}assets/js/input.js", array('acf-input'), $version );
		wp_enqueue_script('acf-input-energialoikka_irr');

		wp_register_script( 'acf-input-energialoikka_finance', "{$url}assets/js/finance.js", array('acf-input'), $version );
		wp_enqueue_script('acf-input-energialoikka_finance');

		// register & include CSS
		wp_register_style( 'acf-input-energialoikka_irr', "{$url}assets/css/input.css", array('acf-input'), $version );
		wp_enqueue_style('acf-input-energialoikka_irr');

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
		// Note: This function can be removed if not used
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
new acf_field_energialoikka_irr( $this->settings );


// class_exists check
endif;

?>