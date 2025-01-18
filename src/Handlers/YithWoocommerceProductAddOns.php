<?php
/**
 * Обработка для плагина YITH WooCommerce Product Add-ons & Extra Options
 *
 * @see     https://wpruse.ru/my-plugins/awooc-product-options/
 * @package awooc-product-options/src/Handlers
 * @version 1.1.0
 */

namespace Art\AwoocProductOptions\Handlers;

use Art\AwoocProductOptions\Handler;
use WC_Product;
use YITH_WAPO;

class YithWoocommerceProductAddOns extends Handler {

	public function added_options( $options, $product_id ): array {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['yith_wapo'] ) ) {
			return $options;
		}

		if ( ! is_array( $_POST['yith_wapo'] ) ) {
			return $options;
		}

		$post_data = map_deep( wp_unslash( (array) $_POST['yith_wapo'] ), 'sanitize_text_field' );

		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$product = wc_get_product( $product_id );

		$options_data = $this->prepare_options_data( $post_data, $product );

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

				$item->update_meta_data( $option['label'], $option['value'] );

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


	protected function prepare_options_data( array $post_data, WC_Product $product ): array {

		$options_data    = [];
		$grouped_in_cart = ! apply_filters( 'yith_wapo_show_options_grouped_in_cart', true );

		foreach ( $post_data as $option ) {
			foreach ( $option as $key => $value ) {

				if ( ! $key || '' === $value ) {
					continue;
				}

				$value = is_array( $value ) && null !== reset( $value ) ? reset( $value ) : $value;

				$values = YITH_WAPO::get_instance()->split_addon_and_option_ids( $key, $value );

				$addon_id  = $values['addon_id'];
				$option_id = $values['option_id'];

				$info = yith_wapo_get_option_info( $addon_id, $option_id );

				$skip_addon = ! empty( $info['addon_type'] )
								&& 'product' === $info['addon_type']
								&& ! empty( $info['sell_individually'] )
								&& wc_string_to_bool( $info['sell_individually'] );

				if ( $skip_addon ) {
					continue;
				}

				$addon_name   = $this->get_addon_name( $info, $grouped_in_cart );
				$addon_value  = $this->get_addon_value( $info, $value, $product, $grouped_in_cart );
				$addon_prices = $this->get_addon_prices( $info, $value, $product );

				$option_price    = ! empty( $addon_prices['price_sale'] ) ? floatval( $addon_prices['price_sale'] ) : floatval( $addon_prices['price'] );
				$formatted_value = $this->format_price( $info, $addon_value, $addon_prices['sign'], $option_price );

				if ( ! isset( $options_data[ $addon_name ] ) ) {
					$options_data[ $addon_name ] = [
						'label' => $addon_name,
						'value' => $formatted_value,
						'price' => $option_price,
					];
				} else {
					$options_data[ $addon_name ]['value'] .= ', ' . $formatted_value;
					$options_data[ $addon_name ]['price'] += $option_price;
				}
			}
		}

		return array_values( $options_data );
	}


	public function get_addon_name( $info, $grouped_in_cart = false ) {

		$fields = [
			'addon_label'       => '',
			'title_in_cart'     => '',
			'title_in_cart_opt' => '',
			'label'             => '',
			'label_in_cart'     => '',
			'label_in_cart_opt' => '',
		];

		$values = $this->get_extract_info_data( $fields, $info );

		if ( ! wc_string_to_bool( $values['title_in_cart'] ) && ! empty( $values['title_in_cart_opt'] ) ) {
			$values['addon_label'] = $values['title_in_cart_opt'];
		}
		if ( ! wc_string_to_bool( $values['label_in_cart'] ) && ! empty( $values['label_in_cart_opt'] ) ) {
			$values['label'] = $values['label_in_cart_opt'];
		}

		return ( $grouped_in_cart || empty( $values['addon_label'] ) ) ? $values['label'] : $values['addon_label'];
	}


	public function get_addon_value( $info, $original_value, $product, $grouped_in_cart = false ) {

		$fields = [
			'addon'             => '',
			'addon_label'       => '',
			'title_in_cart'     => '',
			'title_in_cart_opt' => '',
			'addon_type'        => '',
			'label'             => '',
			'label_in_cart'     => '',
			'label_in_cart_opt' => '',
		];

		$values = $this->get_extract_info_data( $fields, $info );

		$is_empty_title = false;

		if ( ! wc_string_to_bool( $values['title_in_cart'] ) && ! empty( $values['title_in_cart_opt'] ) ) {
			$values['label'] = $values['title_in_cart_opt'];
		}

		if (
			( empty( $values['addon_label'] ) && wc_string_to_bool( $values['title_in_cart'] ) )
			|| ( empty( $values['addon_label'] )
				&& ! wc_string_to_bool( $values['title_in_cart'] )
				&& empty( $values['title_in_cart_opt'] ) )
		) {
			$is_empty_title = true;
		}

		$addon = $values['addon'];
		$label = $values['label'];

		switch ( $values['addon_type'] ) {
			case 'product':
				if ( $product instanceof WC_Product ) {
					$product_name = $addon->get_product_addon_name( $product->get_id() );

					return $this->get_quantity() . ' x ' . $product_name;
				}

				return '';

			case 'file':
				$file = is_array( $original_value ) && null !== reset( $original_value ) ? reset( $original_value ) : $original_value;

				if ( ! is_string( $file ) ) {
					return '';
				}
				$file_url   = urldecode( $file );
				$file_split = explode( '/', $file_url );
				// translators: [FRONT] Label shown on cart for add-on type Upload
				$file_name = apply_filters( 'yith_wapo_show_attached_file_name', true ) ? end( $file_split ) : __( 'Attached file', 'yith-woocommerce-product-add-ons' );
				$file_link = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $file_url ), sanitize_file_name( $file_name ) );

				return empty( $label ) ? $file_link : $label . ': ' . $file_link;

			case 'text':
			case 'textarea':
			case 'number':
			case 'date':
			case 'colorpicker':
				$apply_urldecode = apply_filters( 'yith_wapo_apply_urldecode', true );
				$original_value  = sanitize_text_field( $apply_urldecode ? urldecode( $original_value ) : $original_value );

				if ( ! $grouped_in_cart && ! $is_empty_title ) {
					$label = ! empty( $label ) ? $label . ': ' : '';

					return $label . $original_value;
				}

				return $original_value;

			default:
				return $label;
		}
	}


	public function get_addon_prices( $info, $value, $product, $calculate_taxes = true ): array {

		$fields = [
			'price_method' => '',
			'price_type'   => '',
			'price'        => '',
			'price_sale'   => '',
			'addon_type'   => '',
		];

		$values           = $this->get_extract_info_data( $fields, $info );
		$addon_price      = 0;
		$addon_price_sale = 0;

		if ( in_array( $values['price_method'], [ 'increase', 'decrease' ], true ) ) {
			$addon_price      = $this->get_price( $values['price'], $values['price_method'], $values['price_type'], $product->get_price(), $value );
			$addon_price_sale = $this->get_price( $values['price_sale'], $values['price_method'], $values['price_type'], $product->get_price(), $value );

		} elseif ( in_array( $values['price_method'], [ 'product', 'discount' ], true ) && $product instanceof WC_Product ) {
			$product_price_addon = $calculate_taxes ? wc_get_price_to_display( $product ) : $product->get_price();

			if ( 'discount' === $values['price_method'] ) {
				$option_discount_value = floatval( $values['price'] );

				$addon_price = ( 'percentage' === $values['price'] )
					? $product_price_addon - ( ( $product_price_addon / 100 ) * $option_discount_value )
					: $product_price_addon - $option_discount_value;

			} else {
				$addon_price = $product_price_addon;
			}
		} elseif ( 'value_x_product' === $values['price_method'] && is_numeric( $value ) ) {
			$addon_price = $value * $product->get_price();
		}

		$addon_price      = ! empty( (float) $addon_price ) ? (float) $addon_price : 0;
		$addon_price_sale = $addon_price_sale > 0 ? (float) $addon_price_sale : '';

		$is_negative_number_price =
			'number' === $values['addon_type']
			&& 'multiplied' === $values['price_type']
			&& ( $addon_price < 0 || $addon_price_sale < 0 );

		$is_decrease_method = 'decrease' === $values['price_method'];

		$sign = ( $is_negative_number_price || $is_decrease_method ) ? '-' : '+';

		return [
			'price'      => $addon_price,
			'price_sale' => $addon_price_sale,
			'sign'       => $sign,
		];
	}


	public function format_price( $info, $value, $sign, $price ): string {

		$sign = '-' === $sign ? '' : $sign;

		$hide_products_prices = wc_string_to_bool( $info['hide_products_prices'] ) ?? ''; // Only for add-on type Product.
		$hide_options_prices  = wc_string_to_bool( $info['hide_options_prices'] ) ?? ''; // Only for add-on type Product.

		$formatted_price = sprintf( ' (%s)', wp_strip_all_tags( $sign . wc_price( $price ) ) );
		$formatted_price = '' !== $price && ! empty( $price ) ? $formatted_price : '';

		$display = sprintf( '%s%s', $value, $formatted_price );

		if ( $hide_products_prices || $hide_options_prices ) {
			$display = $value;
		}

		return $display;
	}


	public function get_price( &$price, $price_method, $price_type, $product_price, $value ) {

		if ( ! is_numeric( $price ) || ! is_numeric( $product_price ) ) {
			return $price;
		}

		if ( $price <= 0 ) {
			return $price;
		}

		if ( 'fixed' === $price_type ) {
			$price = 'decrease' === $price_method ? - $price : $price;
		} elseif ( 'percentage' === $price_type ) {
			$price = ( $product_price * $price ) / 100;
			$price = 'decrease' === $price_method ? - $price : $price;
		} elseif ( 'characters' === $price_type ) {
			$remove_spaces        = apply_filters( 'yith_wapo_remove_spaces', false );
			$value                = $remove_spaces ? str_replace( ' ', '', $value ) : $value;
			$number_of_characters = function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );

			$price = $price * $number_of_characters;
		} elseif ( 'multiplied' === $price_type ) {
			$price = $price * $value;
		}

		return $price;
	}


	protected function get_extract_info_data( array $fields, $info ): array {

		$result = [];

		foreach ( $fields as $key => $value ) {
			$result[ $key ] = $info[ $key ] ?? $value;
		}

		return $result;
	}
}
