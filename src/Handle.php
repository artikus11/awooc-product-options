<?php

namespace Art\AwoocProductOptions;

use WC_Product;

class Handle {

	public function setup_hooks(): void {

		add_filter( 'awooc_data_ajax_options', [ $this, 'added_options' ], 10, 3 );
		add_action( 'awooc_create_order', [ $this, 'add_option_in_order' ], 100, 3 );

		add_filter( 'awooc_added_hidden_fields', [ $this, 'added_hidden_fields' ], 10, 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'added_option_in_post_data' ], 100 );
	}


	public function added_options( $options, $data, $product_id ) {

		return $options;
	}


	/**
	 * @throws \JsonException
	 */
	public function add_option_in_order( $order, $contact_form, $posted_data ): void {

		$options = $this->extract_options( $posted_data );

		if ( empty( $options ) ) {
			return;
		}

		$this->process_options( $order, $options );
	}


	/**
	 * @throws \JsonException
	 */
	protected function extract_options( $posted_data ): array {

		if ( empty( $posted_data['awooc_options'] ) ) {
			return [];
		}

		return json_decode( $posted_data['awooc_options'], true, 512, JSON_THROW_ON_ERROR ) ?? [];
	}


	protected function process_options( $order, $options ): void {}


	public function added_option_in_post_data(): void {

		$inline_script = "
    jQuery( document ).ready( function( $ ) {
        $( document.body ).on( 'awooc_popup_ajax_trigger', function( event, response ) {
            if ( response.data.toOrder !== undefined ) {
                $( 'input[name=\"awooc_options\"]' ).val( JSON.stringify( response.data.toOrder.options ) );
            } else {
                console.log( 'Объект toOrder не существует' );
            }
        } );
    } );
    ";

		wp_add_inline_script( 'awooc-scripts', $inline_script );
	}


	public function added_hidden_fields( $addon_fields ) {

		$addon_fields['awooc_options'] = '';

		return $addon_fields;
	}


	protected function get_total_options( array $options_data, WC_Product $product ): float {

		return 0;
	}


	/**
	 * @param        $custom_options
	 * @param  array $options_names
	 * @param        $class_name
	 *
	 * @return array
	 */
	protected function get_options_names( $custom_options, array $options_names, $class_name ): array {

		$label = $class_name === __NAMESPACE__ . '\HandleSimpleProductOptions' ? 'name' : 'label';

		foreach ( $custom_options as $option ) {
			$options_names[] = sprintf( '%s: %s', $option[ $label ], $option['value'] );
		}

		return $options_names;
	}

}