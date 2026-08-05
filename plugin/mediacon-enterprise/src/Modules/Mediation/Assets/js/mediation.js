( () => {
	'use strict';

	const accordions = document.querySelectorAll( '[data-mediation-accordion]' );

	accordions.forEach( ( accordion ) => {
		accordion.querySelectorAll( 'button[aria-controls]' ).forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				const panel = document.getElementById( button.getAttribute( 'aria-controls' ) );

				if ( ! panel ) {
					return;
				}

				const expanded = button.getAttribute( 'aria-expanded' ) === 'true';
				button.setAttribute( 'aria-expanded', String( ! expanded ) );
				panel.hidden = expanded;
			} );
		} );
	} );
} )();
