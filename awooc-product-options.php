<?php
/**
 * Plugin Name: AWOOC Product Options
 * Plugin URI: https://github.com/artikus11/awooc-product-options
 * Text Domain: awooc-product-options
 * Domain Path: /languages
 * Description: Дополнение к плагину Art WooCommerce Order One Click (3.0.0). Поддержка плагинов дополнительных опций товаров Advanced Product Fields for WooCommerce (1.6.4), Simple Product Options for WooCommerce (1.0.0) и Extra product options For WooCommerce (3.2.4). Важно: опции передаются в заказ, но их стоимость не учитывается.
 *
 * Author: Andreslav Kozlov, Artem Abramovich
 * Author URI: https://gist.github.com/Andreslav
 * Version: 1.0.1
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * WC requires at least: 6.0.0
 * WC tested up to: 8.3.0
 *
 * Requires PHP: 7.4
 * Requires WP:5.5
 */

$plugin_data = get_file_data(
	__FILE__,
	[
		'ver'  => 'Version',
		'name' => 'Plugin Name',
	]
);

const AWOOC_PO_PLUGIN_DIR = __DIR__;

define( 'AWOOC_PO_PLUGIN_VER', $plugin_data['ver'] );
define( 'AWOOC_PO_PLUGIN_NAME', $plugin_data['name'] );

require AWOOC_PO_PLUGIN_DIR . '/vendor/autoload.php';

( new \Art\AwoocProductOptions\Main() )->init();
