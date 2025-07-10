(() => {
	/* --------------------------------------------------------------------
	 * 1. Verify all required globals exist
	 * ------------------------------------------------------------------ */
	const { wc, wp } = window || {};

	const depsReady =
		wc?.wcSettings?.getSetting &&
		wc?.wcBlocksRegistry?.registerPaymentMethod &&
		wp?.element?.createElement &&
		wp?.htmlEntities?.decodeEntities;

	if ( ! depsReady ) {
		/* eslint-disable-next-line no-console */
		console.warn(
			'[ViaBill Blocks] Missing WooCommerce Blocks or WordPress globals - gateway not registered.'
		);
		return; // <-- legal now, we’re inside a function scope
	}

	/* --------------------------------------------------------------------
	 * 2. Helper shortcuts
	 * ------------------------------------------------------------------ */
	const decode   = wp.htmlEntities.decodeEntities;	
    const __       = wp?.i18n?.__ ?? ( ( str ) => str ); 
	const el       = wp.element.createElement;

	/* --------------------------------------------------------------------
	 * 3. Read dynamic settings injected by PHP
	 * ------------------------------------------------------------------ */
	const settings =
		wc.wcSettings.getSetting( 'viabill_official_data', {} ) || {};

	const label =
		decode( settings.title || '' ) ||
		__( 'ViaBill - Monthly Payments', 'viabill' );

	/* --------------------------------------------------------------------
	 * 4. <p> description renderer (for both “content” and “edit”)
	 * ------------------------------------------------------------------ */
	const Description = () =>
		el( 'p', null, decode( settings.description || '' ) );

	/* --------------------------------------------------------------------
	 * 5. Final registration object
	 * ------------------------------------------------------------------ */
	wc.wcBlocksRegistry.registerPaymentMethod( {
		name: 'viabill_official',
		label: label,
		ariaLabel: label,
		content: el( Description, null ),
		edit: el( Description, null ),
		canMakePayment: () => true, // add real logic when needed
		supports: {
			features: Array.isArray( settings.supports )
				? settings.supports
				: [ 'products' ], // sensible default
		},
	} );
})();
