<?php

namespace Art\AwoocProductOptions;

use Art\AWOOC\Prepare\Popup;
use Art\AwoocProductOptions\Handles\ArtWoocommerceProductOptions;

class Main {

	/**
	 * @var \Art\AwoocProductOptions\Main|null
	 */
	protected static ?Main $instance = null;


	public function init(): void {

		$this->setup_hooks();

		//( new HandleExtraProductOptions() )->setup_hooks();
		//( new HandleSimpleProductOptions() )->setup_hooks();
		//( new HandleAdvancedProductFields() )->setup_hooks();
		( new ArtWoocommerceProductOptions() )->setup_hooks();
	}


	public function setup_hooks(): void {

		add_filter( 'awooc_select_elements_item', [ $this, 'add_select_elements' ] );
		add_action( 'awooc_popup_column_left', [ $this, 'add_popup_element' ], 35, 2 );

		add_filter( 'awooc_added_hidden_fields', [ $this, 'added_hidden_fields' ], 10, 1 );

		add_filter( 'awooc_data_ajax', [ $this, 'add_data_ajax' ], 10, 2 );
	}


	public function added_hidden_fields( $addon_fields ) {


		$addon_fields['awooc_options']       = '';
		$addon_fields['awooc_options_price'] = '';

		return $addon_fields;
	}


	public function add_data_ajax( $data, $product ) {

		$product_parent_id = $product->get_parent_id();
		$product_id        = $product_parent_id ? : $product->get_id();

		$options_data = apply_filters( 'awooc_data_ajax_options', [], $data, $product_id );

		if ( empty( $options_data['options'] ) ) {
			return $data;
		}

		error_log( print_r( $options_data, true ) );

		$data['toOrder']['options'] = $options_data['options'];

		$data['toPopup']['options'] = $this->get_option_for_popup( $this->get_options_names( $options_data['options'] ) );
		$data['toPopup']['price']   = $this->get_formatted_price_for_popup( $options_data['amount'] );
		$data['toPopup']['sum']     = $this->get_formatted_sum_for_popup( $options_data['amount'], $options_data['quantity'] );

		$data['toMail']['options'] = $this->get_option_for_mail( $this->get_options_names( $options_data['options'] ) );
		$data['toMail']['price']   = $this->get_formatted_price_for_mail( $options_data['amount'] );
		$data['toMail']['sum']     = $this->get_formatted_sum_for_mail( $options_data['amount'], $options_data['quantity'] );

		$data['toAnalytics']['options'] = implode( '; ', $this->get_options_names( $options_data['options'] ) );
		$data['toAnalytics']['price']   = $options_data['amount'];

		return $data;
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


	public static function instance(): ?Main {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;
	}


	protected function get_options_names( $custom_options ): array {

		$options_names = [];

		foreach ( $custom_options as $option ) {

			$label = ! isset ( $option['label'] ) ? $option['name'] : $option['label'];

			$options_names[] = sprintf( '%s: %s', $label, $option['value'] );
		}

		return $options_names;
	}


	/**
	 * @param  mixed $options_names
	 *
	 * @return string
	 */
	protected function get_option_for_popup( mixed $options_names ): string {

		return sprintf(
			'<span class="awooc-attr-label">%s</span></br><span class="awooc-attr-value"><span>%s</span></span>',
			apply_filters( 'awooc_popup_options_label', esc_html__( 'Options: ', 'awooc-product-options' ) ),
			implode( '; </span><span>', $options_names )
		);
	}


	protected function get_formatted_price_for_popup( $amount ): string {

		return sprintf(
			'<span class="awooc-price-label">%s</span><span class="awooc-price-value">%s</span>',
			apply_filters( 'awooc_popup_price_label', __( 'Price: ', 'art-woocommerce-order-one-click' ) ),
			wc_price( $amount )
		);
	}


	protected function get_formatted_sum_for_popup( $amount, $qty ): string {


		return sprintf(
			'<span class="awooc-sum-label">%s</span><span class="awooc-sum-value">%s</span>',
			apply_filters( 'awooc_popup_sum_label', __( 'Amount: ', 'art-woocommerce-order-one-click' ) ),
			wc_price( $amount * $qty )
		);
	}


	protected function get_formatted_price_for_mail( $amount ): string {

		return sprintf(
			'%s%s',
			__( 'Price: ', 'art-woocommerce-order-one-click' ),
			wp_filter_nohtml_kses( wc_price( $amount ) )
		);
	}


	protected function get_formatted_sum_for_mail( $amount, $qty ): string {


		return sprintf(
			'%s%s',
			__( 'Amount: ', 'art-woocommerce-order-one-click' ),
			wp_filter_nohtml_kses( wc_price( $amount * $qty ) )
		);
	}


	/**
	 * @param  mixed $options_names
	 *
	 * @return string
	 */
	protected function get_option_for_mail( mixed $options_names ): string {

		return sprintf(
			__( 'Options: %s', 'awooc-product-options' ),
			implode( '; ', $options_names )
		);
	}

}