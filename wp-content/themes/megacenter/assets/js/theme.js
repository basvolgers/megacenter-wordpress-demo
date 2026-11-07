/**
 * Megacenter – één klein script, geen bibliotheken.
 * 1. Header krimpt bij scrollen (class .mc-scrolled op <html>).
 * 2. Roterend woord in de hero.
 * 3. Reveal-on-scroll met IntersectionObserver.
 * 4. Focus naar de melding van het aanvraagformulier na verzenden.
 * Respecteert prefers-reduced-motion.
 */
( function () {
	'use strict';

	const root = document.documentElement;
	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// 1. Header.
	let ticking = false;
	const onScroll = function () {
		if ( ticking ) {
			return;
		}
		ticking = true;
		window.requestAnimationFrame( function () {
			root.classList.toggle( 'mc-scrolled', window.scrollY > 24 );
			ticking = false;
		} );
	};
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	onScroll();

	// 2. Roterend woord.
	if ( ! reduce ) {
		document.querySelectorAll( '[data-mc-rotator]' ).forEach( function ( el ) {
			const words = el.querySelectorAll( '.mc-rotator__word' );
			if ( words.length < 2 ) {
				return;
			}
			let i = 0;
			let timer = null;
			const tick = function () {
				const current = words[ i ];
				i = ( i + 1 ) % words.length;
				current.classList.remove( 'is-active' );
				current.classList.add( 'is-leaving' );
				words[ i ].classList.add( 'is-active' );
				window.setTimeout( function () {
					current.classList.remove( 'is-leaving' );
				}, 600 );
			};
			const start = function () {
				if ( ! timer ) {
					timer = window.setInterval( tick, 2200 );
				}
			};
			const stop = function () {
				window.clearInterval( timer );
				timer = null;
			};
			// Alleen draaien als de kop in beeld is en het tabblad zichtbaar is.
			if ( 'IntersectionObserver' in window ) {
				new IntersectionObserver( function ( entries ) {
					entries[ 0 ].isIntersecting ? start() : stop();
				} ).observe( el );
			} else {
				start();
			}
			document.addEventListener( 'visibilitychange', function () {
				document.hidden ? stop() : start();
			} );
		} );
	}

	// 3. Reveal-on-scroll.
	const targets = document.querySelectorAll(
		'.mc-section-head, .mc-cards .wp-block-post, .mc-tile, .mc-step, .mc-package, .mc-review, .mc-visit__card, .mc-split__media, .mc-split__text, .mc-highlights__item, .mc-arr, .mc-age, .mc-offer, .mc-spot'
	);
	if ( ! reduce && 'IntersectionObserver' in window && targets.length ) {
		const io = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						io.unobserve( entry.target );
					}
				} );
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
		);
		targets.forEach( function ( el ) {
			// Elementen die al in beeld staan niet verbergen (geen flits bij laden).
			const rect = el.getBoundingClientRect();
			if ( rect.top < window.innerHeight * 0.92 ) {
				return;
			}
			const siblings = el.parentElement ? Array.prototype.indexOf.call( el.parentElement.children, el ) : 0;
			el.style.setProperty( '--i', String( Math.min( siblings, 6 ) ) );
			el.classList.add( 'mc-reveal' );
			io.observe( el );
		} );
	}

	// 4. Formulier: focus naar foutmelding of bedankt-melding.
	const status = document.querySelector( '.mc-form__errors, .mc-form__success' );
	if ( status && window.location.search.indexOf( 'mc_form=' ) !== -1 ) {
		status.setAttribute( 'tabindex', '-1' );
		status.focus( { preventScroll: false } );
	}
}() );
