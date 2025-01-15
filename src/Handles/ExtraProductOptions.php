<?php
/**
 * Обработка для плагина Extra product options For WooCommerce
 *
 * @see     https://wpruse.ru/my-plugins/awooc-product-options/
 * @package awooc-product-options/src/Handlers
 * @version 1.1.0
 */

namespace Art\AwoocProductOptions\Handles;

use Art\AwoocProductOptions\Handle;
use THWEPOF_Utils;

class ExtraProductOptions extends Handle {

	public function added_options( $options, $product_id ): array {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['thwepof_product_fields'] ) ) {
			return $options;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$options_data = $this->prepare_data();

		$options['options'] = $options_data;

		return $options;
	}


	protected function prepare_data(): array {

		$extra_data    = [];
		$extra_options = $this->prepare_product_options();

		if ( empty( $extra_options ) ) {
			return $extra_data;
		}

		foreach ( $extra_options as $name => $field ) {
			$posted_value = $this->get_posted_value( $name );

			if ( ! $posted_value ) {
				continue;
			}

			if ( is_array( $posted_value ) ) {
				// Filter out any empty values
				$posted_value = array_filter( $posted_value );

				if ( empty( $posted_value ) ) {
					continue;
				}

				$posted_value = implode( ',', $posted_value );
			}

			$extra_data[ $name ]['name']    = $name;
			$extra_data[ $name ]['value']   = $posted_value;
			$extra_data[ $name ]['type']    = $field->get_property( 'type' );
			$extra_data[ $name ]['label']   = $field->get_property( 'title' );
			$extra_data[ $name ]['options'] = $field->get_property( 'options' );
		}

		return $extra_data;
	}


	protected function prepare_product_options(): array {

		$final_fields = [];

		$allow_get_method = THWEPOF_Utils::get_settings( 'allow_get_method' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( $allow_get_method ) {
			$product_fields = isset( $_REQUEST['thwepof_product_fields'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['thwepof_product_fields'] ) ) : '';
		} else {
			$product_fields = isset( $_POST['thwepof_product_fields'] ) ? sanitize_text_field( wp_unslash( $_POST['thwepof_product_fields'] ) ) : '';
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$prod_fields = $product_fields ? explode( ',', $product_fields ) : [];

		$extra_options = THWEPOF_Utils::get_product_fields_full();

		foreach ( $prod_fields as $name ) {
			if ( isset( $extra_options[ $name ] ) ) {
				$final_fields[ $name ] = $extra_options[ $name ];
			}
		}

		return $final_fields;
	}


	protected function get_posted_value( $name, $type = false ) {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$is_posted = isset( $_POST[ $name ] ) || isset( $_REQUEST[ $name ] );
		$value     = '';

		if ( ! $is_posted ) {
			return $value;
		}

		$value = ! empty( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : false;
		$value = empty( $value ) && isset( $_REQUEST[ $name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $name ] ) ) : $value;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		if ( 'textarea' === $type ) {
			$value = sanitize_textarea_field( wp_unslash( $value ) );
		} else {
			$value = wc_clean( wp_unslash( ( $value ) ) );
		}

		return $value;
	}


	protected function process_options( $order, $options ): void {

		foreach ( $order->get_items() as $item ) {
			foreach ( $options as $option ) {

				if ( empty( $option['value'] ) ) {
					continue;
				}

				$item->update_meta_data( $option['label'], $option['value'] );

				$item->save();
			}
		}
	}
}
