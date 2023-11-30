<?php

namespace Art\AwoocProductOptions;

class Handle {

	public function setup_hooks() {

		add_filter( 'awooc_data_ajax_options', [ $this, 'added_options' ], 10, 3 );
	}


	public function added_options( $options_names, $product_parent_id, $product_id ) { }


	/**
	 * @param        $custom_options
	 * @param  array $options_names
	 *
	 * @return array
	 */
	protected function get_options_names( $custom_options, array $options_names ): array {

		foreach ( $custom_options as $option ) {
			$options_names[] = sprintf( '%s: %s', $option['name'], $option['value'] );
		}

		return $options_names;
	}

}