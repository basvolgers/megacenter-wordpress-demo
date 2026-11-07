/**
 * Activiteiten-filter: filtert de kaarten direct op de pagina (progressive enhancement).
 * Zonder JavaScript zijn de knoppen gewone links naar de soort-pagina's.
 */
( function () {
	'use strict';
	document.querySelectorAll( '.mc-filter[data-mc-enhance]' ).forEach( function ( nav ) {
		const scope = nav.closest( 'main' ) || document;
		const cards = scope.querySelectorAll( '.wp-block-post-template > .type-activiteit' );
		const status = nav.querySelector( '[data-mc-filter-status]' );
		if ( ! cards.length ) {
			return;
		}
		nav.addEventListener( 'click', function ( event ) {
			const chip = event.target.closest( '[data-filter]' );
			if ( ! chip ) {
				return;
			}
			event.preventDefault();
			const filter = chip.getAttribute( 'data-filter' );
			let visible = 0;
			cards.forEach( function ( card ) {
				const show = filter === 'all' || card.classList.contains( filter );
				card.hidden = ! show;
				if ( show ) {
					visible++;
				}
			} );
			nav.querySelectorAll( '[data-filter]' ).forEach( function ( c ) {
				if ( c === chip ) {
					c.setAttribute( 'aria-current', 'true' );
				} else {
					c.removeAttribute( 'aria-current' );
				}
			} );
			if ( status ) {
				status.textContent = visible + ( visible === 1 ? ' activiteit' : ' activiteiten' ) + ' zichtbaar';
			}
			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', chip.href );
			}
		} );
	} );
}() );
