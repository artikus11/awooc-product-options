<?php

namespace Art\AwoocProductOptions;

use THWEPOF_Public;

class HandleExtraProductOptions extends Handle {

	public function added_options( $options_names, $product_parent_id, $product_id ) {

		if ( ! class_exists( 'THWEPOF_Public' ) ) {
			return $options_names;
		}

		$options = ( new THWEPOF_Public() )->woo_add_cart_item_data( [], $product_parent_id, $product_id );

		if ( empty( $options['thwepof_options'] ) ) {
			return $options_names;
		}

		return $this->get_options_names( $options['thwepof_options'], $options_names, get_class( $this ) );

	}

}