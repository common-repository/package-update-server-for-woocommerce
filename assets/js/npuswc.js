/**
 * Set Default
**/
window.npuswc = window.npuswc || {};
window.npuswc.tools = window.npuswc.tools || {};

/**
 * Define npuswc
 *
 * @requires Underscore
 * 
**/
( function ( root, factory ) {

	"use strict";

	window.npuswc = window.npuswc || {};

	if ( typeof define === 'function' && define.amd ) {
		// AMD. Register as an anonymous module.
		define([], factory);
	} else if ( typeof exports === 'object' ) {
		// Node. Does not work with strict CommonJS, but
		// only CommonJS-like environments that support module.exports,
		// like Node.
		module.exports = factory;
	} else {
		// Browser globals (root is window)
		root.npuswc = new factory();
		//_.extend( root.npuswc.prototype, factory() );
	}

} ( this, function() {

	"use strict";

	function methods() {

		/**
		 * Tools
		**/

			/**
			 * Get the template of underscore by ID 
			 * @param  string   templateID
			 * @return template 
			**/
			function getTemplateByID( templateID )
			{

				var templateID = templateID || '';

				var template = _.template( jQuery( '#' + templateID ).html() );

				return template;

			}

			/**
			 * Copy the text to clipboard
			 * @param  {string} textVal
			 * @return {bool}   
			**/
			function copyTextToClipboard( textVal ) {

				// テキストエリアを用意する
				var copyFrom = document.createElement( "textarea" );
				// テキストエリアへ値をセット
				copyFrom.textContent = textVal;

				// bodyタグの要素を取得
				var bodyElm = document.getElementsByTagName( "body" )[0];
				// 子要素にテキストエリアを配置
				bodyElm.appendChild( copyFrom );

				// テキストエリアの値を選択
				copyFrom.select();
				// コピーコマンド発行
				var retVal = document.execCommand( 'copy' );
				// 追加テキストエリアを削除
				bodyElm.removeChild( copyFrom );

				// 処理結果を返却
				return retVal;

			}

			/**
			 * Copy the text to clipboard
			 * @param  {template} template : Template object of Underscore
			 * @param  {string}   where    : Selector for where to display
			 * @return {void}
			**/
			function showFadingText( templateHTML, where ) {

				var templateHTML = templateHTML || '';
				if ( '' === template ) {
					console.log( 'No HTML to display.' );
				}
				var where    = where || '.message-ajax-done';

				jQuery( where ).children().remove();
				jQuery( where ).append( templateHTML );

			}

			/**
			 * Copy the text to clipboard
			 * @param  {string}   type     : Display type "done" "error"
			 * @param  {string}   where    : Selector for where to display
			 * @return {bool}
			**/
			function showFadingIcon( type, where ) {

				var type = type || 'done';
				if ( '' === template ) {
					console.log( 'No HTML to display.' );
				}
				var where    = where || '.message-ajax-done';

				// Check the type
				if ( 'done' === type ) {
					var templateID = '#npuswc-template-notice-done';
				}

				var template = _.template( jQuery( templateID ).html() );

				jQuery( where ).children().remove();
				jQuery( where ).append( templateHTML );

				return retVal;

			}

		return {
			"customerDownloadsTemplateForToken" : "npuswc-template-popup-customer-purchased-token",
			"getTemplateByID": getTemplateByID,
			"copyTextToClipboard": copyTextToClipboard,
			"showFadingText": showFadingText,
		};

	}

	return methods();

}));