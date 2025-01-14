<?php

namespace Art\AwoocProductOptions\Handles;

use Art\AwoocProductOptions\Handle;
use Art\WoocommerceProductOptions\Main;
use WC_Order_Item_Product;

class ArtWoocommerceProductOptions extends Handle {

	/**
	 * @var \Art\WoocommerceProductOptions\Main|null
	 */
	protected ?Main $main = null;


	protected ?array $option = [];


	public function __construct() {

		$this->main = Main::instance();
	}


	public function added_options( $options, $data, $product_id ) {

		if ( ! class_exists( '\Art\WoocommerceProductOptions\Main' ) ) {
			return $options;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['awpo_option'] ) ) {
			return $options;
		}

		$product = wc_get_product( $product_id );

		$post_data          = map_deep( wp_unslash( (array) $_POST['awpo_option'] ), 'sanitize_text_field' );
		$post_data_quantity = sanitize_text_field( wp_unslash( (int) $_POST['quantity'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$options_data = $this->main->get_cart()->prepared_selected_data( $product->get_id(), $post_data );

		$options['options']  = $options_data;
		$options['amount']   = $this->get_amount_price( $options_data, $product );
		$options['quantity'] = $post_data_quantity;

		return $options;
	}


	public function added_option_in_post_data(): void {

		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				$( document.body ).on( 'awooc_popup_ajax_trigger', function( event, response ) {
					if ( response.data.toOrder !== undefined ) {
						$( 'input[name="awooc_options"]' ).val( JSON.stringify( response.data.toOrder.options ) );
					} else {
						console.log( 'Объект toOrder не существует' );
					}
				} );

			} );
		</script>
		<?php
	}


	/**
	 * @throws \JsonException
	 */
	public function add_option_in_order( $order, $contact_form, $posted_data ): void {

		if ( empty( $posted_data['awooc_options'] ) ) {
			return;
		}

		$posted_data_options = json_decode( $posted_data['awooc_options'], true, 512, JSON_THROW_ON_ERROR );

		$order_total   = 0;
		$options_total = 0;

		/** @var WC_Order_Item_Product $item */
		foreach ( $order->get_items() as $item ) {


			foreach ( $posted_data_options as $field ) {
				if ( empty( $field['value'] ) ) {
					continue;
				}

				$product = $item->get_product();

				if ( ! $product ) {
					continue;
				}

				$item->update_meta_data( $field['label'], $this->main->get_cart()->format_price( $field['value'], $field['price'] ) );

				$current_price = $product->get_price();

				if ( ! empty( $field['price'] ) ) {
					$options_total += $field['price'];
				}

				$product_price_with_options = ( $options_total + $current_price ) * $item->get_quantity();

				$item->set_subtotal( $product_price_with_options );
				$item->set_total( $product_price_with_options );

				$item->save();

				$order_total += $item->get_total() + $item->get_total_tax();
			}
		}

		$discount_total = $order->get_discount_total();

		if ( $discount_total > 0 ) {
			$order_total = $order_total - $discount_total;
		}

		$order->set_total( $order_total );
		$order->save();
	}


	/**
	 * @param  array                 $options_data
	 * @param  bool|\WC_Product|null $product
	 *
	 * @return float|int
	 */
	protected function get_amount_price( array $options_data, bool|\WC_Product|null $product ): int|float {

		$options_total = 0;
		$amount        = 0;

		foreach ( $options_data as $field ) {
			if ( empty( $field['value'] ) ) {
				continue;
			}

			$current_price = $product->get_price();

			if ( ! empty( $field['price'] ) ) {
				$options_total += $field['price'];
			}

			$amount = (float) $options_total + (float) $current_price;
		}

		return $amount;
	}

}