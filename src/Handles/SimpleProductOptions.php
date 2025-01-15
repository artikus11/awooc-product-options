<?php
/**
 * Обработка для плагина Product Options for WooCommerce
 *
 * @see     https://wpruse.ru/my-plugins/awooc-product-options/
 * @package awooc-product-options/src/Handlers
 * @version 1.1.0
 */

namespace Art\AwoocProductOptions\Handles;

use Art\AwoocProductOptions\Handle;
use Pektsekye_ProductOptions_Model_Option;
use WC_Product;

class SimpleProductOptions extends Handle {

	public function added_options( $options, $data, $product_id ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['pofw_option'] ) ) {
			return $options;
		}

		$product = wc_get_product( $product_id );

		$post_data          = map_deep( wp_unslash( (array) $_POST['pofw_option'] ), 'sanitize_text_field' );
		$post_data_quantity = sanitize_text_field( wp_unslash( (int) $_POST['quantity'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$options_data = $this->prepared_selected_data( $product->get_id(), $post_data );

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

				$item->update_meta_data( $option['label'], $this->format_price( $option['value'], $option['price'] ) );

				$current_price = $product->get_price();

				if ( ! empty( $option['price'] ) ) {
					$options_total += $option['price'];
				}

				$product_price_with_options = ( $options_total + $current_price ) * $item->get_quantity();

				$item->set_subtotal( $product_price_with_options );
				$item->set_total( $product_price_with_options );

				$item->save();
			}

			$order_total += $item->get_total() + $item->get_total_tax();
		}

		$discount_total = $order->get_discount_total();

		if ( $discount_total > 0 ) {
			$order_total = $order_total - $discount_total;
		}

		$order->set_total( $order_total );
		$order->save();
	}


	public function getOptionModel(): Pektsekye_ProductOptions_Model_Option {

		include_once( Pektsekye_PO()->getPluginPath() . 'Model/Option.php' );

		return new Pektsekye_ProductOptions_Model_Option();
	}


	public function prepared_selected_data( $product_id, $selected_values ): array { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		$options = $this->getOptionModel()->getProductOptions( $product_id );

		if ( empty( $options ) ) {
			return [];
		}

		$formatted_values = [];

		foreach ( $options as $option_id => $option ) {
			if ( empty( $selected_values[ $option_id ] ) ) {
				continue;
			}

			$selected_value = $selected_values[ $option_id ];

			$value = '';
			$price = 0;

			switch ( $option['type'] ) {
				case 'radio':
				case 'drop_down':
					if ( is_array( $selected_value ) ) {
						break;
					}

					$value_id = (int) $selected_value;

					if ( ! isset( $option['values'][ $value_id ] ) ) {
						break;
					}

					$value = $option['values'][ $value_id ]['title'];
					$price = $option['values'][ $value_id ]['price'];

					break;
				case 'checkbox':
				case 'multiple':
					foreach ( (array) $selected_value as $value_id ) {
						if ( ! isset( $option['values'][ $value_id ] ) ) {
							continue;
						}

						$value .= ( '' !== $value ? ', ' : '' ) . $option['values'][ $value_id ]['title'];
						$price += (float) $option['values'][ $value_id ]['price'];
					}
					break;
				case 'field':
				case 'area':
					if ( is_array( $selected_value ) ) {
						break;
					}

					$value = $selected_value;
					break;
			}

			if ( $value ) {
				$formatted_values[] = [
					'label' => $option['title'],
					'value' => $value,
					'price' => $price,
				];
			}
		}

		return $formatted_values;
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


	protected function format_price( $text, $price ): string {

		$price = empty( $price ) ? '' : sprintf( ' +%s', wc_price( $price ) );

		return sprintf( '%s %s', $text, $price );
	}
}