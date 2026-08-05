/**
 * Accessible editorial interactions.
 */

document.addEventListener( 'click', async ( event ) => {
	const button = event.target.closest( '[data-editorial-copy-url]' );

	if ( ! button ) {
		return;
	}

	const status = document.querySelector( '[data-editorial-copy-status]' );

	try {
		await navigator.clipboard.writeText( button.dataset.editorialCopyUrl );
		if ( status ) {
			status.textContent = 'Link copiato negli appunti.';
		}
	} catch ( error ) {
		if ( status ) {
			status.textContent = 'Copia non disponibile. Seleziona il link dalla barra del browser.';
		}
	}
} );
