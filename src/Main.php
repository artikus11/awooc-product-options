<?php

namespace Art\AwoocProductOptions;

use Art\AwoocProductOptions\Handles\AdvancedProductFields;
use Art\AwoocProductOptions\Handles\ArtWoocommerceProductOptions;
use Art\AwoocProductOptions\Handles\ExtraProductOptions;
use Art\AwoocProductOptions\Handles\SimpleProductOptions;
use Art\AwoocProductOptions\Handles\YithWoocommerceProductAddOns;

class Main {

	/**
	 * @var \Art\AwoocProductOptions\Main|null
	 */
	protected static ?Main $instance = null;


	public function init(): void {

		( new PluginsManager() )->init();
		( new Settings() )->init_hooks();

		$this->init_classes();
	}


	/**
	 * @return void
	 */
	protected function init_classes(): void {

		( new AwoocAjaxHandler() )->init_hooks();

		if ( PluginsManager::is_plugin_active( 'yith-woocommerce-product-add-ons/init.php' ) ) {
			( new YithWoocommerceProductAddOns() )->setup_hooks();
		}

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


	public static function instance(): ?Main {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;
	}
}
