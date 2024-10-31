<?php


/**
 * List
 * 
**/
	if ( ! function_exists( 'npuswc_print_list' ) ) {
		/**
		 * Print HTML of 'UL' or 'OL'
		 * 
		 * @param array  $list 
		 *     [string] 'html'
		 *     [string] ''
		 * @param [string] $type : Default 'ul' 
		 * 
		 * @return [string]
		**/
		function npuswc_print_list( array $list, string $type = 'ul', bool $echo = true )
		{

			// Check
			if ( ! in_array( $type, array( 'ul', 'ol' ) ) ) {
				return '';
			}

			ob_start();

			echo '<' . $type . '>';

			foreach ( $list as $list_index => $list_item ) {

				echo '<li>';

					echo $list_item['html'];

				echo '</li>';

			}

			echo '</' . $type . '>';

			$html = ob_get_clean();

			/**
			 * Filter the list html
			 * 
			 * @param [string] $html : This will be printed
			 * @param [array]  $list : List data
			 * @param [string] $type : List type 'ul' or 'ol'
			 * 
			 * @return [string] filtered html
			**/
			$html = apply_filters( 'npuswc_filter_print_list', $html, $list, $type );

			if ( $echo ) {
				echo $html;
			}
			return $html;

		}
	}


/**
 * Form
 * 
**/
	if ( ! function_exists( 'npuswc_print_form_label' ) ) {
		/**
		 * Print HTML of form input
		 * 
		 * @param [string] $name
		 * @param [string] $label
		 * 
		 * @return [string]
		**/
		function npuswc_print_form_label( string $name, string $label )
		{

			printf( '<label for="%1$s">%2$s</label>',
				esc_attr( $name ),
				esc_html( $label )
			);

		}
	}

	if ( ! function_exists( 'npuswc_print_form_input' ) ) {
		/**
		 * Print HTML of form input
		 * 
		 * @param [string] $type
		 * @param [string] $id
		 * @param [string] $name
		 * @param [string] $value
		 * 
		 * @return [string]
		**/
		function npuswc_print_form_input( string $type, string $id, string $class, string $name, string $value )
		{


			ob_start();

				if ( 'checkbox' === $type ) {
					npuswc_print_form_input_checkbox( $id, $class, $name, $value );
				} elseif ( 'text' === $type ) {
					npuswc_print_form_input_text( $id, $class, $name, $value );
				}

			$html = ob_get_clean();

			/**
			 * Filter the list html
			 * 
			 * @param [string] $html : This will be printed
			 * @param [array]  $list : List data
			 * @param [string] $type : List type 'ul' or 'ol'
			 * 
			 * @return [string] filtered html
			**/
			echo apply_filters( 'npuswc_filter_print_input', $html, $type, $id, $class, $name, $value );

		}
	}

		if ( ! function_exists( 'npuswc_print_form_input_checkbox' ) ) {
			/**
			 * Print HTML of form checkbox
			 * 
			 * @param [string] $id 
			 * @param [string] $class 
			 * @param [string] $name 
			 * @param [string] $value 
			 * 
			 * @return [string]
			**/
			function npuswc_print_form_input_checkbox( $id, $class, $name, $value )
			{

				ob_start();

				$format = '<input type="checkbox" 
					id="%1$s"
					class="%2$s"
					name="%3$s"
					data-prop-key="%3$s"
					value="yes"
					%4$s 
					style="width:0;"
				>';
				printf( $format,
					esc_attr( $id ),
					esc_attr( $class ),
					esc_attr( $name ),
					checked( 'yes', $value, false )
				);

				$html = ob_get_clean();

				/**
				 * Filter the list html
				 * 
				 * @param [string] $html : This will be printed
				 * @param [string] $id 
				 * @param [string] $class 
				 * @param [string] $name 
				 * @param [string] $value 
				 * 
				 * @return [string] filtered html
				**/
				echo apply_filters( 'npuswc_filter_print_form_input_checkbox', $html, $id, $class, $name, $value );

			}
		}

		if ( ! function_exists( 'npuswc_print_form_input_text' ) ) {
			/**
			 * Print HTML of form text
			 * 
			 * @param [string] $id 
			 * @param [string] $class 
			 * @param [string] $name 
			 * @param [string] $value 
			 * 
			 * @return [string]
			**/
			function npuswc_print_form_input_text( $id, $class, $name, $value )
			{

				ob_start();

				$format = '<input type="text" 
					id="%1$s"
					class="%2$s"
					name="%3$s"
					value="%4$s"
					data-prop-key="%3$s"
				>';
				printf( $format,
					esc_attr( $id ),
					esc_attr( $class ),
					esc_attr( $name ),
					esc_attr( $value )
				);

				$html = ob_get_clean();

				/**
				 * Filter the list html
				 * 
				 * @param [string] $html : This will be printed
				 * @param [string] $id 
				 * @param [string] $class 
				 * @param [string] $name 
				 * @param [string] $value 
				 * 
				 * @return [string] filtered html
				**/
				echo apply_filters( 'npuswc_filter_print_form_input_text', $html, $id, $class, $name, $value );

			}
		}

	if ( ! function_exists( 'npuswc_print_form_option_button' ) ) {
		/**
		 * Print HTML of form button
		 * 
		 * @param [string] $option_name 
		 * @param [string] $text        : Default "Save"
		 * @param [bool] $with_nonce 
		 * 
		 * @return [void]
		**/
		function npuswc_print_form_option_button( $option_name, $text = '', $with_nonce = true )
		{

			if ( '' === $text ) {
				$text = __( 'Save', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
			}

			$name       = 'update_' . $option_name;
			$id         = 'update_' . $option_name;
			$class      = 'button button-primary button-update-option button_update_option_' . $option_name;
			$action     = 'npuswc_update_option_' . $option_name;
			$nonce_id   = 'npuswc_update_option_' . $option_name . '_nonce';
			$input_class = 'option_' . $option_name;
			$text       = __( 'Save', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );

			if ( $with_nonce ) {
				wp_nonce_field( $action, $nonce_id, true, true );
			}
			printf( '<p class="submit">
					<input type="submit"
						name="%1$s"
						id="%2$s"
						class="%3$s"
						data-action="%4$s"
						data-nonce="%5$s"
						data-input-class="%6$s"
						value="%7$s"
					>
				</p>',
				esc_attr( $name ),
				esc_attr( $id ),
				esc_attr( $class ),
				esc_attr( $action ),
				esc_attr( $nonce_id ),
				esc_attr( $input_class ),
				esc_html( $text )
			);

		}

	}

	if ( ! function_exists( 'npuswc_print_form_option_button_for_ajax' ) ) {
		/**
		 * Print HTML of form button
		 * 
		 * @param [string] $option_name 
		 * @param [string] $text        : Default "Save"
		 * @param [bool] $with_nonce 
		 * 
		 * @return [void]
		**/
		function npuswc_print_form_option_button_for_ajax( string $option_name, $text = '', $with_nonce = true )
		{

			if ( '' === $text ) {
				$text = __( 'Save', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
			}

			$id         = 'update_' . $option_name;
			$class      = 'button button-primary wp-ajax button-update-option button_update_option_' . $option_name;
			$action     = 'npuswc_update_option_' . $option_name;
			$nonce_id   = 'npuswc_update_option_' . $option_name . '_nonce';
			$text       = __( 'Save', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );

			if ( $with_nonce ) {
				wp_nonce_field( $action, $nonce_id, true, true );
			}
			printf( '<p class="submit">
					<a href="javascript: void(0);"
						id="%1$s"
						class="%2$s"
						data-action="%3$s"
						data-nonce="%4$s"
						data-option="%5$s"
					>%6$s</a>
				</p>',
				esc_attr( $id ),
				esc_attr( $class ),
				esc_attr( $action ),
				esc_attr( $nonce_id ),
				esc_attr( $option_name ),
				esc_html( $text )
			);

		}

	}





