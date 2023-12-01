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
	 * @param        $class_name
	 *
	 * @return array
	 */
	protected function get_options_names( $custom_options, array $options_names, $class_name ): array {

		$label = $class_name === __NAMESPACE__ . '\HandleSimpleProductOptions' ? 'name' : 'label';

		foreach ( $custom_options as $option ) {
			$options_names[] = sprintf( '%s: %s', $option[ $label ], $option['value'] );
		}

		return $options_names;
	}

}