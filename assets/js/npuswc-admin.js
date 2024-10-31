/**
 * Set Default
**/
window.npuswc = window.npuswc || {};
window.npuswc.admin = window.npuswc.admin || {};

/**
 * Define npuswc
 *
 * @requires Underscore
 * 
**/
( function ( root, factory ) {

	"use strict";

	window.npuswc.admin = window.npuswc.admin || {};
	root.npuswc.admin = new factory();

} ( this, function() {

	"use strict";

	function methods() {

		/**
		 * Tools
		**/

			/**
			 * Get the template of underscore by ID 
			 * @param  string   noticeClass
			 * @return template 
			**/
			function addNotice( noticeClass )
			{

				var noticeClass = noticeClass || '';

				var template = _.template( jQuery( '#' + templateID ).html() );

				return template;

			}

		return {
			"addNotice": addNotice,
		};

	}

	return methods();

}));