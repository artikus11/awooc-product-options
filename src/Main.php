<?php

namespace Art\AwoocProductOptions;

use Art\AwoocProductOptions\Formatters\Analytics;
use Art\AwoocProductOptions\Formatters\Mail;
use Art\AwoocProductOptions\Formatters\Popup;
use Art\AwoocProductOptions\Handles\AdvancedProductFields;
use Art\AwoocProductOptions\Handles\ArtWoocommerceProductOptions;
use Art\AwoocProductOptions\Handles\ExtraProductOptions;
use Art\AwoocProductOptions\Handles\SimpleProductOptions;

class Main {

	/**
	 * @var \Art\AwoocProductOptions\Main|null
	 */
	protected static ?Main $instance = null;


	public function init(): void {

		( new PluginsManager() )->init();

		$this->setup_hooks();

		$this->init_classes();
	}


	/**
	 * @return void
	 */
	protected function init_classes(): void {

		if ( PluginsManager::is_plugin_active( 'product-options-for-woocommerce/product-options-for-woocommerce.php' ) ) {
			( new SimpleProductOptions() )->setup_hooks();
		}

		if ( PluginsManager::is_plugin_active( 'woo-extra-product-options/woo-extra-product-options.php' ) ) {
			( new ExtraProductOptions() )->setup_hooks();
		}

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

		add_filter( 'awooc_data_ajax', [ $this, 'add_data_ajax' ], 10, 2 );
	}


	public function add_data_ajax( $data, $product ) {

		$product_parent_id = $product->get_parent_id();
		$product_id        = $product_parent_id ? : $product->get_id();

		$options_data = apply_filters( 'awooc_data_ajax_options', [], $product_id );

		if ( empty( $options_data['options'] ) ) {
			return $data;
		}

		$data = $this->set_data_popup( $options_data, $data );

		$data = $this->set_data_mail( $options_data, $data );

		$data = $this->set_data_analytics( $options_data, $data );

		$data['toOrder']['options'] = $options_data['options'];

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


	protected function set_data_popup( $options_data, $data ): array {

		$popup_formatter            = new Popup();
		$data['toPopup']['options'] = $popup_formatter->format_options_with_label( $popup_formatter->get_options_names( $options_data['options'] ) );

		if ( ! empty( $options_data['amount'] ) ) {
			$data['toPopup']['price'] = $popup_formatter->format_price_with_label( $options_data['amount'] );
			$data['toPopup']['sum']   = $popup_formatter->format_sum_with_label( $options_data['amount'], $options_data['quantity'] );
		}

		return $data;
	}


	protected function set_data_mail( $options_data, array $data ): array {

		$mail_formatter            = new Mail();
		$data['toMail']['options'] = $mail_formatter->format_options_with_label( $mail_formatter->get_options_names( $options_data['options'] ) );

		if ( ! empty( $options_data['amount'] ) ) {
			$data['toMail']['price'] = $mail_formatter->format_price_with_label( $options_data['amount'] );
			$data['toMail']['sum']   = $mail_formatter->format_sum_with_label( $options_data['amount'], $options_data['quantity'] );
		}

		return $data;
	}


	protected function set_data_analytics( $options_data, array $data ): array {

		$analytics_formatter            = new Analytics();
		$data['toAnalytics']['options'] = $analytics_formatter->format_options_list( $analytics_formatter->get_options_names( $options_data['options'] ) );

		if ( ! empty( $options_data['amount'] ) ) {
			$data['toAnalytics']['price'] = $options_data['amount'];
		}

		return $data;
	}
}
