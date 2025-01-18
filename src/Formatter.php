<?php

namespace Art\AwoocProductOptions;

abstract class Formatter {

	abstract public function format( array $options_data, array $data ): array;


	public function format_options_names( $custom_options ): array {

		$options_names = [];

		foreach ( $custom_options as $option ) {

			$label = ! isset( $option['label'] ) ? $option['name'] : $option['label'];

			$options_names[] = sprintf( '%s: %s', $label, $option['value'] );
		}

		return $options_names;
	}


	/**
	 * Форматирование цены.
	 *
	 * @param  float $amount
	 *
	 * @return string
	 */
	public function format_price( float $amount ): string {

		return wc_price( $amount );
	}


	/**
	 * Форматирование суммы.
	 *
	 * @param  float $amount
	 * @param  int   $qty
	 *
	 * @return string
	 */
	public function format_sum( float $amount, int $qty ): string {

		return wc_price( $amount * $qty );
	}


	/**
	 * Форматирование списка опций.
	 *
	 * @param  array $options_names
	 *
	 * @return string
	 */
	public function format_options_list( array $options_names ): string {

		return implode( '; ', $options_names );
	}
}
