	( function( $ ) {

	var adminSettingPage = {

		/**
		 * Initializer
		 * @return {[void]}
		 */
		init: function()
		{

			$buttonSelector = $( '.wp-ajax.button-update-option' );
			$buttonSelector.each( function( index ) {
				// Register
				adminSettingPage.registerActionUpdate( $( this ).context.id );

			});

		},

		/**
		 * 
		 * @param  {[context]} e [description]
		 * @return {[type]}   [description]
		 */
		registerActionUpdate: function( buttonID )
		{

			$button = $( '#' + buttonID );

			// Buttons Attributes
			var updateButtonIDSelector = '#' + $button.attr( 'id' );
			var optionName = $button.data( 'option' );
			var nonceIDSelector = '#' + $button.data( 'nonce' );

			// Selectors
			var $updateButton = $( updateButtonIDSelector );
			var $inputClass = $( '.option_' + optionName );
			var $nonce = $( nonceIDSelector );

			$updateButton.on( 'click', function( e ) {

				// nonce
				var nonceVal = $nonce.val();
				var nonceName = $nonce.attr( 'name' );
				var nonce = {
					"name"  : nonceName,
					"value" : nonceVal
				};

				// saved data
				var data = {};
				$inputClass.each( function( index, element ) {

					$this = $( this );

					var propName = $this.attr( 'data-prop-key' );
					var propVal = $this.val();
					if ( 'checkbox' === $this.attr( 'type' ) ) {
						if ( $this.prop( "checked" ) ) {
							var propVal = propVal;
						} else {
							var propVal = 'no';
						}
					}

					if ( undefined === data[ propName ] ) {
						data[ propName ] = propVal;
					}

				});

				// request update
				adminSettingPage.actionUpdateOption( optionName, nonce, data );


			});

		},

		/**
		 * 
		 * @param  {[string]} optionName [optionName]
		 * @param  {[string]} nonce      [nonce]
		 * @param  {[array]}  data       [data]
		 * @return {[type]}   [description]
		 */
		actionUpdateOption: function( optionName, nonce, data )
		{

			// check the requirements
			if ( data.length ) {
				return false;
			}

			var action = 'npuswc_update_option_data';
			var dataInJSON = JSON.stringify( data );

			var formData = new FormData();

			formData.append( 'action', action );
			formData.append( nonce['name'], nonce['value'] );
			formData.append( 'option_key', optionName );
			formData.append( 'option_data', dataInJSON );

			// Request
			var request = $.ajax({
				url         : ajaxurl,
				method      : "POST",
				dataType    : "json",
				data        : formData,
				processData : false,
				contentType : false,
				tryLimit    : 2,
				tryCount    : 0,
				error       : function( jqXHR, textStatus, errorThrown ) {

					console.log( jqXHR );
					if ( textStatus == 'timeout' ) {

						this.tryCount++;

						if ( this.tryCount <= this.retryLimit ) {

							//try again
							$.ajax( this );

						}            

						return;

					}

					if ( jqXHR.status == 500 ) {

						this.tryCount++;

						if ( this.tryCount <= this.retryLimit ) {

							//try again
							$.ajax( this );

						}            

						return;

					} else {
						//handle error
					}

				},
				success     : function( data, status, jqHXR ) {

				}
			});
			request.done( function( data ) {
				console.log( data );
				location.reload( true )
				return;
			});

		}

	};


	// Trigger init
	adminSettingPage.init();

}) ( jQuery );