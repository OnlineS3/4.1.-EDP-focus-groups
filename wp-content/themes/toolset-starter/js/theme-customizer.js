(function ($) {

	function update_header_background_image() {
		$( '.header-background-image img' ).css( {
			'min-height' : $( '.js-header-height' ).height(),
			'margin-bottom' : -$( '.js-header-height' ).height()
		});
	}

	//Update site color in real time...
	wp.customize( 'logo', function (value) {
		value.bind( function (newval) {
			var logo = $( '.header-nav .logo' );

			if( newval != '' ) { // new image set
				logo.html( '<img src="'+ newval +'" alt="">' );
				logo.find( 'img' ).on( 'load', function() {
					update_header_background_image();
				});
			} else { // "remove" button
				logo.html( '' );
				update_header_background_image();
			}

		});
	});


	$('body').append('<style id="custom-ref-style-life"></style>');
	wp.customize('primary_color', function (value) {
		value.bind(function (newval) {
			window.parent.jQuery( '#customize-control-primary_color' ).find( 'input.wp-color-picker' ).trigger( 'change' );
		});
	});

	wp.customize( 'setting_custom_css', function (value) {
		value.bind( function (newval) {
			$.ajax({
				url: toolset.ajaxurl,
				type: 'POST',
				data: ({ action: 'toolset_sanitize_css_ajax', customcss: '' + newval + '', ajax: 1 }),
				success: function (response) {
					if( ! $( '#toolset-custom-css' ).length ) {
						$( '<style id="toolset-custom-css" type="text/css"></style>' ).appendTo( 'body' );
					}
					$( '#toolset-custom-css' ).html( response );
				}
			});
		});
	});

})(jQuery);