<?php 
?>
<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
	<?php 
	foreach ( $tabs as $tab_name => $tab_data ) {
	?>
		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => $tab_name ), $setting_url_base ) ); ?>"
			class="<?php echo esc_attr( $tab_data['class'] ); ?>"
		><?php echo esc_html( $tab_data['text'] ); ?></a>
	<?php
	}
	?>
</nav>

