/**
 * Minimal Mediacon Design Core compatibility behavior.
 */

( function () {
	'use strict';

	const elements = document.querySelectorAll( '.mdc-reveal' );

	if ( ! elements.length ) {
		return;
	}

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches || ! ( 'IntersectionObserver' in window ) ) {
		elements.forEach( ( element ) => element.classList.add( 'is-visible' ) );
		return;
	}

	const observer = new IntersectionObserver( ( entries ) => {
		entries.forEach( ( entry ) => {
			if ( entry.isIntersecting ) {
				entry.target.classList.add( 'is-visible' );
				observer.unobserve( entry.target );
			}
		} );
	} );

	elements.forEach( ( element ) => observer.observe( element ) );
}() );
