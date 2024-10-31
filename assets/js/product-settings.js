( function( $ ) {

	var productSettings = {

		// Vars
		template: '',
		templateID: '',
		tokenValueID: '',
		template: '',

		/**
		 * Init
		 * 
		 * @return void
		**/
		init: function()
		{

			// First Init
			productSettings.initDatePicker();

			// For the variation, this will trigger after loading variations
			$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function( e ) {
				productSettings.initDatePicker();
			});

		},

		/**
		 * Init datepicker
		 * 
		 * @return void
		**/
		initDatePicker: function()
		{
			$( '.npuswc-date-picker' ).datepicker({
				dateFormat: 'yy-mm-dd',
			});
		}

	};

	$( function() {

		productSettings.init();

	});
	
}) ( jQuery );