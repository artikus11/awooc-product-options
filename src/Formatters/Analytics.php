<?php

namespace Art\AwoocProductOptions\Formatters;

use Art\AwoocProductOptions\Formatter;

class Analytics extends Formatter {

	public function format( array $options_data, array $data ): array {

		if ( ! empty( $options_data['options'] ) ) {
			$data['toAnalytics']['options'] = $this->format_options_list( $this->format_options_names( $options_data['options'] ) );
		}

		if ( ! empty( $options_data['amount'] ) ) {
			$data['toAnalytics']['price'] = $options_data['amount'];
		}

		return $data;
	}
}
