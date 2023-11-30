<?php

namespace Art\AwoocProductOptions;

class Main {

	/**
	 * @var \Art\AwoocProductOptions\Main|null
	 */
	private static ?Main $instance = null;


	public function init() {

		$this->setup_hooks();

		( new HandleExtraProductOptions() )->setup_hooks();
		( new HandleSimpleProductOptions() )->setup_hooks();
		( new HandleAdvancedProductFields() )->setup_hooks();

	}


	public function setup_hooks() {

		add_filter( 'awooc_select_elements_item', [ $this, 'add_select_elements' ] );
		add_action( 'awooc_popup_column_left', [ $this, 'add_popup_element' ], 35, 2 );

		add_filter( 'awooc_data_ajax', [ $this, 'add_data_ajax' ], 10, 2 );

	}


	public function add_data_ajax( $data, $product ) {

		$product_parent_id = $product->get_parent_id();
		$product_id        = $product_parent_id ? : $product->get_id();

		$options_names = apply_filters( 'awooc_data_ajax_options', [], $product_parent_id, $product_id );

		if ( ! $options_names ) {
			return $data;
		}

		$data['toPopup']['options'] = sprintf(
			'<span class="awooc-attr-label">%s</span></br><span class="awooc-attr-value"><span>%s</span></span>',
			apply_filters( 'awooc_popup_options_label', esc_html__( 'Options: ', 'awooc-product-options' ) ),
			implode( '; </span><span>', $options_names )
		);

		$data['toMail']['options'] = sprintf(
			__( 'Options: %s', 'awooc-product-options' ),
			implode( '; ', $options_names )
		);

		$data['toAnalytics']['options'] = implode( '; ', $options_names );

		return $data;
	}


	public function add_popup_element( array $elements ) {

		if ( in_array( 'options', $elements, true ) ) {
			echo '<div class="awooc-form-custom-order-options awooc-popup-item awooc-popup-options skeleton-loader"></div>';
		}
	}


	public function add_select_elements( $data ) {

		$data['options'] = __( 'Options', 'awooc-product-options' );

		return $data;
	}


	public static function instance(): ?Main {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;
	}

}