( function( $ ) {

	var customerDownloads = {

		// Vars
		template: '',
		templateID: '',
		tokenValueID: '',
		template: '',

		/**
		 * Copy text
		 *
		 * @uses customerDownloads.openPopupToken( productId, token )
		 *
		 * @return void
		**/
		init: function()
		{

			// Set the ID
			customerDownloads.tokenPopupTemplate = npuswc.getTemplateByID( npuswc.customerDownloadsTemplateForToken );

			var Timer = setInterval( function() {

				var buttonsPopupToken = document.querySelectorAll( '.npuswc-customer-purchased-token' );
				console.log( buttonsPopupToken );

				if ( ! buttonsPopupToken ) {
					return;
				}
				
				$( '.npuswc-customer-purchased-token' ).on( 'click', function( e ) {

					e.preventDefault();

					// Token value ID
					var $this = $( this );
					var tokenId = $this.attr( 'data-token-id' );

					// Token
					var $parent = $this.parentsUntil( '.woocommerce-table.woocommerce-table--order-npuswc-tokens > tbody > tr' );
					var token = $parent.find( '#npuswc-hidden-customer-purchased-token-' + tokenId ).val();

					// Product ID
					var productId = $this.attr( 'data-product-id' );

					// Popup
					customerDownloads.openPopupToken( tokenId, token );

				});

				clearInterval( Timer );

			}, 1000 );

		},

		/**
		 * Copy text
		 *
		 * @param  {string}    inputId 
		 *
		 * @uses customerDownloads.closePopup( e )
		 * @uses customerDownloads.copy( inputId )
		 * 
		 * @return {bool}           [description]
		 */
		copy: function( inputId, attr )
		{

			// Case : Input ID is not set
			inputId = inputId || '';
			attr    = attr || '';
			if ( '' === inputId ) {
				return false;
			}

			/* Get the text field */
			var $textarea = $( '#' + inputId );
			if ( '' === attr ) {
				return npuswc.copyTextToClipboard( $textarea.val() );
			} else {
				return npuswc.copyTextToClipboard( $textarea.attr( attr ) );
			}

		},

		/**
		 * Open popup to display token
		 *
		 * @param  {int}    productId
		 * @param  {string} token    
		 *
		 * @uses customerDownloads.closePopup( e )
		 * @uses customerDownloads.copy( inputId )
		 * 
		 * @return {void} 
		 */
		openPopupToken: function( tokenId, token )
		{

			// Protect ID
			tokenId   = tokenId || '';

			// Template
			var template = customerDownloads.tokenPopupTemplate;

			// Template HTML
			var templateHTML = template({
				tokenId  : tokenId,
				token    : token,
				textCopy : scriptsObject.copy,
				textClose: scriptsObject.close
			});

			// Open
			$( 'body' ).append( templateHTML );


			// Close
			$( '.npuswc-close-popup' ).on( 'click', function( e ) {

				customerDownloads.closePopup( e );

			});

			// Copy
			$( '.npuswc-copy-text' ).on( 'click', function( e ) {
				customerDownloads.copy( 'npuswc-textarea-customer-purchased-token-' + $( e.target ).attr( 'data-token-id' ) );
			});

		},

		/**
		 * Close popup
		 *
		 * @param  {context} e
		 *
		 * @return {void} 
		 */
		closePopup: function( e )
		{

			var $this = $( '#' + e.target.id );
			$this.parentsUntil( 'body' ).remove();

		}

	};

	document.addEventListener( 'DOMContentLoaded', function() {
		customerDownloads.init();
		console.log( customerDownloads );
	} );

}) ( jQuery );