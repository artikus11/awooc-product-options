<?php

namespace Art\AwoocProductOptions;

class PluginsManager {

	const SUPPORT_PLUGINS = [
		'advanced-product-fields-for-woocommerce/advanced-product-fields-for-woocommerce.php',
		'woo-extra-product-options/woo-extra-product-options.php',
		'product-options-for-woocommerce/product-options-for-woocommerce.php',
		'art-woocommerce-product-options/art-woocommerce-product-options.php',
	];


	public function init(): void {

		add_action( 'activated_plugin', [ self::class, 'on_plugin_activated' ] );
	}


	public static function on_plugin_activated( $plugin ): void {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! in_array( $plugin, self::SUPPORT_PLUGINS, true ) ) {
			return;
		}

		foreach ( self::SUPPORT_PLUGINS as $group_plugin ) {
			if ( $group_plugin !== $plugin && is_plugin_active( $group_plugin ) ) {
				deactivate_plugins( $group_plugin );
			}
		}
	}


	/**
	 * @param  string $plugin
	 *
	 * @return bool
	 */
	public static function is_plugin_active( string $plugin ): bool {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin );
	}
}
