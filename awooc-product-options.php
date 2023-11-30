<?php
/**
 * Plugin Name: AWOOC Product Options
 * Plugin URI: https://gist.github.com/Andreslav/7f5ebabf0729161f338e83e98d22def0#file-support-product-options-for-awooc-php-L49
 * Text Domain: awooc-product-options
 * Domain Path: /languages
 * Description: Дополнение к плагину Art WooCommerce Order One Click. Поддержка плагинов дополнительных полей для товаров Advanced Product Fields for WooCommerce, Simple Product Options for WooCommerce, Extra product options For WooCommerce и других.
 *
 * Author: Andreslav Kozlov, Artem Abramovich
 * Author URI: https://gist.github.com/Andreslav
 * Version: 1.0.0
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
define( 'AWOOC_PO_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
define( 'AWOOC_PO_PLUGIN_FILE', plugin_basename( __FILE__ ) );

define( 'AWOOC_PO_PLUGIN_VER', $plugin_data['ver'] );
define( 'AWOOC_PO_PLUGIN_NAME', $plugin_data['name'] );

require AWOOC_PO_PLUGIN_DIR . '/vendor/autoload.php';

( new \Art\AwoocProductOptions\Main() )->init();
