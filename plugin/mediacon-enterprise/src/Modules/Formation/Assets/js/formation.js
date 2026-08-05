/**
 * Accessible formation interactions.
 */

document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '[data-formation-accordion]' ).forEach( ( accordion ) => {
		accordion.querySelectorAll( 'button[aria-controls]' ).forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				const panel = document.getElementById( button.getAttribute( 'aria-controls' ) );

				if ( ! panel ) {
					return;
				}

				const expanded = button.getAttribute( 'aria-expanded' ) === 'true';
				button.setAttribute( 'aria-expanded', String( ! expanded ) );
				panel.hidden = expanded;
				const marker = button.querySelector( '[aria-hidden="true"]' );

				if ( marker ) {
					marker.textContent = expanded ? '+' : '−';
				}
			} );
		} );
	} );
} );
