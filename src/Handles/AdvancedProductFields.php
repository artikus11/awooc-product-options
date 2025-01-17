<?php
/**
 * Обработка для плагина Advanced Product Fields for WooCommerce
 *
 * @see     https://wpruse.ru/my-plugins/awooc-product-options/
 * @package awooc-product-options/src/Handlers
 * @version 1.1.0
 */

namespace Art\AwoocProductOptions\Handles;

use Art\AwoocProductOptions\Handle;
use SW_WAPF\Includes\Classes\Enumerable;
use SW_WAPF\Includes\Classes\Field_Groups;
use SW_WAPF\Includes\Classes\Fields;
use SW_WAPF\Includes\Models\Field;
use WC_Product;

class AdvancedProductFields extends Handle {

	public function added_options( $options, $product_id ): array {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['wapf'] ) || ! isset( $_POST['wapf_field_groups'] ) ) {
			return $options;
		}

		if ( ! is_array( $_POST['wapf'] ) ) {
			return $options;
		}

		$field_groups = Field_Groups::get_by_ids( explode( ',', sanitize_text_field( wp_unslash( $_POST['wapf_field_groups'] ) ) ) );
		$fields       = Enumerable::from( $field_groups )->merge( function ( $x ) {

			return $x->fields;
		} )->toArray();

		$post_data = map_deep( wp_unslash( (array) $_POST['wapf'] ), 'sanitize_text_field' );

		$product = wc_get_product( $product_id );

		$options_data = $this->prepare_options_data( $post_data, $fields, $product );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$options['options']  = $options_data;
		$options['amount']   = $this->get_total_options( $options_data, $product );
		$options['quantity'] = $this->get_quantity();

		return $options;
	}


	protected function prepare_options_data( $post_data, array $fields, WC_Product $product ): array {

		$options_data = [];

		foreach ( $post_data as $raw_field_id => $field_value ) {
			if ( '' === $field_value ) {
				continue;
			}

			$field_id = str_replace( 'field_', '', $raw_field_id );

			$field = Enumerable::from( $fields )->firstOrDefault( function ( $x ) use ( $field_id ) {

				return $x->id === $field_id;
			} );

			if ( ! $field ) {
				continue;
			}

			$options_data[] = $this->prepared_selected_data( $field, $product );
		}

		return $options_data;
	}


	protected function prepared_selected_data( Field $field, $product, $raw_value = null ): array {

		if ( is_null( $raw_value ) ) {
			$raw_value = Fields::get_raw_field_value_from_request( $field );
		}

		$price_addition = [];

		if ( $field->pricing_enabled() ) {
			$price_addition = Fields::pricing_value( $field, $raw_value );
		}

		return [
			'id'         => $field->id,
			'type'       => $field->type,
			'raw'        => is_string( $raw_value ) ? sanitize_textarea_field( $raw_value ) : array_map( 'sanitize_textarea_field', $raw_value ),
			'value'      => Fields::value_to_string( $field, $raw_value, $price_addition > 0, $product ),
			'value_cart' => Fields::value_to_string( $field, $raw_value, $price_addition > 0, $product, 'cart' ),
			'price'      => $price_addition,
			'label'      => esc_html( $field->label ),
		];
	}


	/**
	 * @param  array      $options_data
	 * @param  WC_Product $product
	 *
	 * @return float
	 */
	protected function get_total_options( array $options_data, WC_Product $product ): float {

		$total_options = 0.0;

		foreach ( $options_data as $field ) {
			if ( ! empty( $field['value'] ) && ! empty( $field['price'] ) ) {
				$total_options += (float) $this->get_total_option_price( $field['price'], $total_options );
			}
		}

		return (float) $product->get_price() + $total_options;
	}


	protected function process_options( $order, $options ): void {

		$order_total   = 0;
		$options_total = 0;

		foreach ( $order->get_items() as $item ) {
			foreach ( $options as $option ) {

				if ( empty( $option['value_cart'] ) ) {
					continue;
				}

				$product = $item->get_product();

				if ( ! $product ) {
					continue;
				}

				$item->update_meta_data( $option['label'], $option['value_cart'] );

				$current_price = $product->get_price();

				$options_total = $this->get_total_option_price( $option['price'], $options_total );

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


	/**
	 * @param  array $prices
	 * @param  int   $options_total
	 *
	 * @return string
	 */
	protected function get_total_option_price( array $prices, int $options_total ): mixed {

		foreach ( $prices as $price ) {
			$options_total += $price['value'];
		}

		return $options_total;
	}
}
