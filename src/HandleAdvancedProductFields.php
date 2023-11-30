<?php

namespace Art\AwoocProductOptions;

use SW_WAPF\Includes\Controllers\Product_Controller;

class HandleAdvancedProductFields extends Handle {

	public function added_options( $options_names, $product_parent_id, $product_id ) {

		if ( ! class_exists( '\SW_WAPF\Includes\Controllers\Product_Controller' ) ) {
			return $options_names;
		}

		$options = ( new Product_Controller() )->add_fields_to_cart_item( [], $product_parent_id, $product_id );

		if ( empty( $options['wapf'] ) ) {
			return $options_names;
		}

		return $this->get_options_names( $options['wapf'], $options_names );
	}

}