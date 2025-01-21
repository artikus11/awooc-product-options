<?php

namespace Art\AwoocProductOptions;

use Art\AwoocProductOptions\Handlers\AdvancedProductFields;
use Art\AwoocProductOptions\Handlers\ArtWoocommerceProductOptions;
use Art\AwoocProductOptions\Handlers\ExtraProductOptions;
use Art\AwoocProductOptions\Handlers\SimpleProductOptions;
use Art\AwoocProductOptions\Handlers\YithWoocommerceProductAddOns;

class Main {

	/**
	 * @var \Art\AwoocProductOptions\Main|null
	 */
	protected static ?Main $instance = null;


	public function init(): void {

		( new PluginsManager() )->init();
		( new Settings() )->init_hooks();

		$this->init_classes();
		$this->init_hooks();
	}


	/**
	 * @return void
	 */
	protected function init_classes(): void {

		( new HookHandler() )->init_hooks();

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


	public function init_hooks(): void {

		add_action( 'init', [ $this, 'load_textdomain' ] );

		add_action( 'before_woocommerce_init', static function () {

			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
					'custom_order_tables',
					AWOOC_PO_PLUGIN_FILE,
					true
				);
			}
		} );
	}


	public function load_textdomain(): void {

		load_plugin_textdomain(
			'awooc-product-options',
			false,
			dirname( AWOOC_PO_PLUGIN_FILE ) . '/languages/'
		);
	}


	public static function instance(): ?Main {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;
	}
}
