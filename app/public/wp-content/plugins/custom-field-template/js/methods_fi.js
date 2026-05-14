/*
 * Localized default methods for the jQuery validation plugin.
 * Locale: FI
 */
jQuery.extend( jQuery.validator.methods, {
	date: function( value, element ) {
		return this.optional( element ) || /^\d{1,2}\.\d{1,2}\.\d{4}jQuery/.test( value );
	},
	number: function( value, element ) {
		return this.optional( element ) || /^-?(?:\d+)(?:,\d+)?jQuery/.test( value );
	}
} );
