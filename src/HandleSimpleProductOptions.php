<?php

namespace Art\AwoocProductOptions;

use Pektsekye_ProductOptions_Model_Observer;

class HandleSimpleProductOptions extends Handle {

	public function added_options( $options_names, $product_parent_id, $product_id ) {

		if ( ! class_exists( 'Pektsekye_ProductOptions_Model_Observer' ) ) {
			return $options_names;
		}

		$Model_Observer = new Pektsekye_ProductOptions_Model_Observer();

		$options = $Model_Observer->save_selected_options( [], $product_id );

		if ( empty( $options['pofw_option'] ) ) {
			return $options_names;
		}

		$pofw_options = $Model_Observer->formatSelectedValues( $product_id, $options['pofw_option'] );

		if ( empty( $pofw_options ) ) {
			return $options_names;
		}

		return $this->get_options_names( $pofw_options, $options_names, get_class( $this ) );
	}

}