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

class SimpleProductOptions extends Handle {

	public function added_options( $options, $product_id ): array {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['pofw_option'] ) ) {
			return $options;
		}

		$post_data = map_deep( wp_unslash( (array) $_POST['pofw_option'] ), 'sanitize_text_field' );

		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$product = wc_get_product( $product_id );

		$options_data = $this->prepare_options_data( $product->get_id(), $post_data );

		$options['options']  = $options_data;
		$options['amount']   = $this->get_total_options( $options_data, $product );
		$options['quantity'] = $this->get_quantity();

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

		include_once Pektsekye_PO()->getPluginPath() . 'Model/Option.php';

		return new Pektsekye_ProductOptions_Model_Option();
	}


	public function prepare_options_data( $product_id, $selected_values ): array {

		$options = $this->getOptionModel()->getProductOptions( $product_id );

		if ( empty( $options ) ) {
			return [];
		}

		$formatted_values = [];

		foreach ( $options as $option_id => $option ) {
			$selected_value = $selected_values[ $option_id ] ?? null;

			if ( ! $selected_value ) {
				continue;
			}

			$value = '';
			$price = 0;

			if ( ! is_array( $selected_value ) && in_array( $option['type'], [ 'radio', 'drop_down' ], true ) ) {
				$value_id = (int) $selected_value;
				if ( isset( $option['values'][ $value_id ] ) ) {
					$value = $this->format_price( $option['values'][ $value_id ]['title'], (float) $option['values'][ $value_id ]['price'] );
					$price = $option['values'][ $value_id ]['price'];
				}
			} elseif ( in_array( $option['type'], [ 'checkbox', 'multiple' ], true ) ) {
				foreach ( (array) $selected_value as $value_id ) {
					if ( ! isset( $option['values'][ $value_id ] ) ) {
						continue;
					}

					$price += (float) $option['values'][ $value_id ]['price'];
					$value .= ( $value ? ', ' : '' ) . $this->format_price( $option['values'][ $value_id ]['title'], (float) $option['values'][ $value_id ]['price'] );
				}
			} elseif ( in_array( $option['type'], [ 'field', 'area' ], true ) && ! is_array( $selected_value ) ) {
				$value = $selected_value;
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


	protected function format_price( $text, $price ): string {

		$price = empty( $price ) ? '' : sprintf( ' (+%s)', wc_price( $price ) );

		return sprintf( '%s %s', $text, $price );
	}
}
