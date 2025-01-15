<?php

namespace Art\AwoocProductOptions;

class Settings {

	public function init_hooks(): void {

		add_filter( 'awooc_select_elements_item', [ $this, 'add_select_elements' ] );
		add_action( 'awooc_popup_column_left', [ $this, 'add_popup_element' ], 35, 2 );
	}


	public function add_popup_element( array $elements ): void {

		if ( in_array( 'options', $elements, true ) ) {
			echo '<div class="awooc-form-custom-order-options awooc-popup-item awooc-popup-options skeleton-loader"></div>';
		}
	}


	public function add_select_elements( $data ) {

		$data['options'] = __( 'Options', 'awooc-product-options' );

		return $data;
	}
}