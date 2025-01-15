<?php

namespace Art\AwoocProductOptions;

use Art\AwoocProductOptions\Formatters\Analytics;
use Art\AwoocProductOptions\Formatters\Mail;
use Art\AwoocProductOptions\Formatters\Popup;

class AwoocAjaxHandler {

	public function init_hooks(): void {

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
