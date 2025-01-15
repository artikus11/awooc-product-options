<?php
/**
 * Обработка для плагина Art WooCommerce Product Options
 *
 * @see     https://wpruse.ru/my-plugins/awooc-product-options/
 * @package awooc-product-options/src/Handlers
 * @version 1.1.0
 */

namespace Art\AwoocProductOptions\Handles;

use Art\AwoocProductOptions\Handle;
use Art\WoocommerceProductOptions\Main;
use WC_Product;

class ArtWoocommerceProductOptions extends Handle {

	/**
	 * @var \Art\WoocommerceProductOptions\Main|null
	 */
	protected ?Main $awpo_main = null;


	public function __construct() {

		$this->awpo_main = Main::instance();
	}


	public function added_options( $options, $product_id ): array {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['awpo_option'] ) ) {
			return $options;
		}

		$product = wc_get_product( $product_id );

		$post_data          = map_deep( wp_unslash( (array) $_POST['awpo_option'] ), 'sanitize_text_field' );
		$post_data_quantity = ! empty( $_POST['quantity'] ) ? sanitize_text_field( wp_unslash( (int) $_POST['quantity'] ) ) : 1;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$options_data = $this->awpo_main->get_cart()->prepared_selected_data( $product->get_id(), $post_data );

		$options['options']  = $options_data;
		$options['amount']   = $this->get_total_options( $options_data, $product );
		$options['quantity'] = $post_data_quantity;

		return $options;
	}


	protected function process_options( $order, $options ): void {

		$order_total   = 0;
		$options_total = 0;

		foreach ( $order->get_items() as $item ) {
			foreach ( $options as $option ) {
				if ( empty( $option['value'] ) ) {
					continue;
				}

				$product = $item->get_product();

				if ( ! $product ) {
					continue;
				}

				$item->update_meta_data( $option['label'], $this->awpo_main->get_cart()->format_price( $option['value'], $option['price'] ) );

				$current_price = $product->get_price();

				if ( ! empty( $option['price'] ) ) {
					$options_total += $option['price'];
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
	 * @param  array      $options_data
	 * @param  WC_Product $product
	 *
	 * @return float
	 */
	protected function get_total_options( array $options_data, WC_Product $product ): float {

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
