( function () {
	'use strict';

	document.documentElement.classList.add( 'mediacon-enterprise-ready' );

	document.addEventListener( 'change', function ( event ) {
		var selector = event.target.closest( '.mediacon-governance-selector' );
		var form;
		var fields;
		if ( ! selector ) {
			return;
		}
		selector.disabled = true;
		form = document.createElement( 'form' );
		form.method = 'post';
		form.action = window.ajaxurl ? window.ajaxurl.replace( 'admin-ajax.php', 'admin-post.php' ) : 'admin-post.php';
		fields = {
			action: 'mediacon_enterprise_page_governance',
			module: selector.dataset.module,
			resource: selector.dataset.resource,
			mode: selector.value,
			mediacon_enterprise_governance_nonce: selector.dataset.nonce
		};
		Object.keys( fields ).forEach( function ( name ) {
			var input = document.createElement( 'input' );
			input.type = 'hidden';
			input.name = name;
			input.value = fields[ name ];
			form.appendChild( input );
		} );
		document.body.appendChild( form );
		form.submit();
	} );
}() );
