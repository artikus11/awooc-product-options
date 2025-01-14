<?php

namespace Art\AwoocProductOptions;

class Handle {

	public function setup_hooks() {

		add_filter( 'awooc_data_ajax_options', [ $this, 'added_options' ], 10, 3 );
		add_action( 'awooc_create_order', [ $this, 'add_option_in_order' ], 100, 3 );

		add_action( 'wp_footer', [ $this, 'added_option_in_post_data' ], 100 );
	}


	public function added_option_in_post_data(): void {}


	public function added_options( $options, $data, $product_id) {

		return $options;
	}


	public function add_option_in_order( $order, $contact_form, $posted_data ): void {}


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