<?php

namespace Art\AwoocProductOptions;

use WC_Product;

abstract class Handler {

	/**
	 * @var int
	 */
	protected int $quantity;


	public function setup_hooks(): void {

		add_filter( 'awooc_data_ajax_options', [ $this, 'added_options' ], 10, 2 );
		add_action( 'awooc_create_order', [ $this, 'add_option_in_order' ], 100, 3 );

		add_filter( 'awooc_added_hidden_fields', [ $this, 'added_hidden_fields' ], 10, 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'added_option_in_post_data' ], 100 );
	}


	abstract public function added_options( $options, $product_id ): array;


	/**
	 * @throws \JsonException If the JSON decoding fails.
	 */
	public function add_option_in_order( $order, $contact_form, $posted_data ): void {

		$options = $this->extract_options( $posted_data );

		if ( empty( $options ) ) {
			return;
		}

		$this->process_options( $order, $options );
	}


	/**
	 * Extract options from posted data.
	 *
	 * @param  array $posted_data The data posted from the request.
	 *
	 * @return array Decoded options array.
	 * @throws \JsonException If the JSON decoding fails.
	 */
	protected function extract_options( array $posted_data ): array {

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


	/**
	 * @return int
	 */
	public function get_quantity(): int {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$this->quantity = ! empty( $_POST['quantity'] ) ? max( 1, (int) sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) ) : 1;

		// phpcs:enable WordPress.Security.NonceVerification.Missing
		return $this->quantity;
	}


	/**
	 * @param  array       $options_data
	 * @param  \WC_Product $product
	 *
	 * @return float
	 *
	 * @todo может быть использовать трейты или интерфес?
	 */
	protected function get_total_options( array $options_data, WC_Product $product ): float {

		$total_options = 0.0;

		foreach ( $options_data as $field ) {
			if ( ! empty( $field['value'] ) && ! empty( $field['price'] ) ) {
				$total_options += (float) $field['price'];
			}
		}

		return (float) $product->get_price() + $total_options;
	}
}
