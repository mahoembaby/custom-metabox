<?php
/*
 * Plugin Name:       Custom Metabox
 * Plugin URI:        https://github.com/mahoembaby/custom-metabox
 * Description:       This plugin in care of the SEO of the website as you can write the description of the page and choose your keywords.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Mahmoud Hosny
 * Author URI:        https://github.com/mahoembaby
 */

 if( ! defined( 'ABSPATH' )) {
    exit;
 }


class Custom_Metabox {

	/**
     * Array that defines display locations.
	 */

	private $display_locations = ['page'];
	

	/**
	 * Variables array that defines fields/options for the meta box.
	 */

	private $fields = [
		'title_page' => [
			'type' => 'text',
			'label' => 'Title of Page',
			'default' => '',
		],
		'description_page' => [
			'type' => 'text',
			'label' => 'description',
			'default' => '',
		],
	];
	
	/**
	 * Adds actions to WordPress hooks "add_meta_boxes" and "save_post".
	 */

	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'custom_metabox_register_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'custom_metabox_save_meta_box_fields' ] );
	}
	
	/**
	 * Adds meta boxes to appropriate WordPress screens.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function custom_metabox_register_meta_boxes() : void {
		foreach ( $this->display_locations as $location ) {
			add_meta_box(
				'custom_metabox', /* The id of our meta box. */
				'Custom MetaBox', /* The title of our meta box. */
				[ $this, 'custom_metabox_render_meta_box_fields' ], /* The callback function that renders the metabox. */
				$location, /* The screen on which to show the box. */
				'normal', /* The placement of our meta box. */
				'default', /* The priority of our meta box. */
			);
		}
	}
	
	/**
	 * Renders the Meta Box and its fields.
	 */

	public function custom_metabox_render_meta_box_fields(WP_Post $post) : void {
		wp_nonce_field( 'custom_metabox_data', 'custom_metabox_nonce' );
		echo '<h3>Custom MetaBox</h3>';
		$html = '';
		foreach( $this->fields as $field_id => $field ){
			$meta_value = get_post_meta( $post->ID, $field_id, true );
			if ( empty( $meta_value ) && isset( $field['default'] ) ) {
				$meta_value = $field['default'];
			}
	
			$field_html = $this->custom_metabox_render_input_field( $field_id, $field, $meta_value );
			$label = "<label for='$field_id'>{$field['label']}</label>";
			$html .= $this->custom_metabox_format_field( $label, $field_html );
		}
		echo '<table class="form-table"><tbody>' . $html . '</tbody></table>';
	}
	
	/**
	 * Formats each field to table display.
	 */
	public function custom_metabox_format_field( string $label, string $field ): string {
		return '<tr class="form-field"><th>' . $label . '</th><td>' . $field . '</td></tr>';
	}
	
	/**
	 * Renders each individual field HTML code.
	 */
	public function custom_metabox_render_input_field( string $field_id, array $field, string $field_value): string {
		switch( $field['type'] ){
			case 'select': {
				$field_html = '<select name="'.$field_id.'" id="'.$field_id.'">';
					foreach( $field['options'] as $key => $value ) {
						$key = !is_numeric( $key ) ? $key : $value;
						$selected = '';
						if( $field_value === $key ) {
							$selected = 'selected="selected"';
						}
						$field_html .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
					}
				$field_html .= '</select>';
				break;
			}
			case 'textarea': {
				$field_html = '<textarea name="' . $field_id . '" id="' . $field_id . '" rows="6">' . $field_value . '</textarea>';
				break;
			}
			default: {
				$field_html = "<input type='{$field['type']}' id='$field_id' name='$field_id' value='$field_value' />";
				break;
			}
		}
	
		return $field_html;
	}
	
	/**
	 * Called when this metabox is saved.
	 */

	public function custom_metabox_save_meta_box_fields( int $post_id ) {
		if ( ! isset( $_POST['custom_metabox_nonce'] ) ) return;
	
		$nonce = $_POST['custom_metabox_nonce'];
		if ( !wp_verify_nonce( $nonce, 'custom_metabox_data' ) ) return;
	
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
		foreach ( $this->fields as $field_id => $field ) {
			if( isset( $_POST[$field_id] ) ){
				// Sanitize fields that need to be sanitized.
				switch( $field['type'] ) {
					case 'email': {
						$_POST[$field_id] = sanitize_email( $_POST[$field_id] );
						break;
					}
					case 'text': {
						$_POST[$field_id] = sanitize_text_field( $_POST[$field_id] );
						break;
					}
				}
				update_post_meta( $post_id, $field_id, $_POST[$field_id] );
			}
		}
	}
	
}

// Fire the Class 

if ( class_exists( 'Custom_Metabox' ) ) {
	new Custom_Metabox();
}

