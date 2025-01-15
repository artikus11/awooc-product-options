<?php

namespace Art\AwoocProductOptions;

use Art\AwoocProductOptions\Formatters\Analytics;
use Art\AwoocProductOptions\Formatters\Mail;
use Art\AwoocProductOptions\Formatters\Popup;
use Art\AwoocProductOptions\Handles\AdvancedProductFields;
use Art\AwoocProductOptions\Handles\ArtWoocommerceProductOptions;

class Main {

	/**
	 * @var \Art\AwoocProductOptions\Main|null
	 */
	protected static ?Main $instance = null;


	public function init(): void {

		( new PluginsManager() )->init();

		$this->setup_hooks();

		//( new HandleExtraProductOptions() )->setup_hooks();
		//( new HandleSimpleProductOptions() )->setup_hooks();

		if ( PluginsManager::is_plugin_active( 'advanced-product-fields-for-woocommerce/advanced-product-fields-for-woocommerce.php' ) ) {
			( new AdvancedProductFields() )->setup_hooks();
		}

		if ( PluginsManager::is_plugin_active( 'art-woocommerce-product-options/art-woocommerce-product-options.php' ) ) {
			( new ArtWoocommerceProductOptions() )->setup_hooks();
		}
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

		$data = $this->set_data_popup( $options_data, $data );

		$data = $this->set_data_mail( $options_data, $data );

		$data = $this->set_data_analytics( $options_data, $data );

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


	/**
	 * @param  mixed $options_data
	 * @param        $data
	 *
	 * @return array
	 */
	protected function set_data_popup( mixed $options_data, $data ): array {

		$popup_formatter            = new Popup();
		$data['toPopup']['options'] = $popup_formatter->format_options_with_label( $popup_formatter->get_options_names( $options_data['options'] ) );
		$data['toPopup']['price']   = $popup_formatter->format_price_with_label( $options_data['amount'] );
		$data['toPopup']['sum']     = $popup_formatter->format_sum_with_label( $options_data['amount'], $options_data['quantity'] );

		return $data;
	}


	/**
	 * @param  mixed $options_data
	 * @param  array $data
	 *
	 * @return array
	 */
	protected function set_data_mail( mixed $options_data, array $data ): array {

		$mail_formatter            = new Mail();
		$data['toMail']['options'] = $mail_formatter->format_options_with_label( $mail_formatter->get_options_names( $options_data['options'] ) );
		$data['toMail']['price']   = $mail_formatter->format_price_with_label( $options_data['amount'] );
		$data['toMail']['sum']     = $mail_formatter->format_sum_with_label( $options_data['amount'], $options_data['quantity'] );

		return $data;
	}


	/**
	 * @param  mixed $options_data
	 * @param  array $data
	 *
	 * @return array
	 */
	protected function set_data_analytics( mixed $options_data, array $data ): array {

		$analytics_formatter            = new Analytics();
		$data['toAnalytics']['options'] = $analytics_formatter->format_options_list( $analytics_formatter->get_options_names( $options_data['options'] ) );
		$data['toAnalytics']['price']   = $options_data['amount'];

		return $data;
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