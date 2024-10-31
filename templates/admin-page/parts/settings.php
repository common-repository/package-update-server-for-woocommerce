<?php
/**
 * Requires to define 
 * 		$option_data
 * 		$options
 * Defined vars :
 * 
 * 
 * 
**/

// Vars
$data_option = npuswc_get_data_option( 'package_update_server' );
$option_data = $data_option->get_data();
//npuswc_test_var_dump( $option_data );

$options = npuswc()->get_option_manager()->get_option_form_inputs( 'package_update_server' );

?>
<div id="package_update_server-settings-wrapper" class="settings-wrapper postbox" style="">
	<h3 id="package_update_server-settings-h2" class="form-table-title hndle"><?php esc_html_e( 'Package Update Server for WooCommerce', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></h3>
	<div class="inside"><div class="main">
		<table id="package_update_server-settings" class="form-table">
			<tbody>
				<?php
				foreach ( $option_data as $option_index => $option_value ) {
				?>
					<tr>
						<th scope="row">
							<?php npuswc_print_form_label( $options[ $option_index ]['name'], $options[ $option_index ]['label'] ) ?>
						</th>
						<td>
							<?php
							npuswc_print_form_input( $options[ $option_index ]['type'],
								$options[ $option_index ]['id'],
								'option_package_update_server',
								$options[ $option_index ]['name'],
								$option_value
							);
							?>
						</td>
						<td><?php echo esc_html( $options[ $option_index ]['description'] ); ?></td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
		<?php
			/** 
			 * 
			 * @param [string] $option_name 
			 * @param [string] $text 
			 * @param [bool]   $with_nonce 
			**/
			npuswc_print_form_option_button_for_ajax(
				'package_update_server',
				__( 'Save', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				true
			);
		?>
	</div></div>
</div>
