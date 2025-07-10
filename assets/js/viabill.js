/**
 * Price-tag relocation that also works when the target element
 * is injected later by other JavaScript.
 *
 * Requirements:
 *  – jQuery 1.7+ (for on/ready)
 *  – Modern browsers that support MutationObserver
 */
jQuery(function ($) {

  //---------------------------------------------------------------------------
  // Public bootstrap
  //---------------------------------------------------------------------------
  relocateAllPriceTags();                           // try once at load
  $(document.body).on('updated_cart_totals',        // Woo fallback
    relocateAllPriceTags);

  //---------------------------------------------------------------------------
  // Helpers
  //---------------------------------------------------------------------------

  /**
   * Iterate over every element that owns a data-append-target attribute
   * and (re)locate its surrounding .viabill-pricetag-wrap.
   */
  function relocateAllPriceTags () {
    $('[data-append-target]').each(function () {
      const $tag     = $(this);
      const target   = $tag.data('append-target');

      // Skip if we’ve already handled this tag once
      if ($tag.data('vb-attached') || !target) { return; }

      relocateOnePriceTag($tag, target.trim());
    });
  }

  /**
   * Move one price-tag wrapper next to/before/after its selector.
   * If the selector is not present yet, watch until it is.
   */
  function relocateOnePriceTag ($tag, targetSpec) {
    let selector     = targetSpec;
    let insertAfter  = false;
    let insertFirst  = false;

    // Parse modifiers like ".my-class:first:after"
    if (selector.includes(':after')) {
      insertAfter = true;
      selector    = selector.replace(':after', '').trim();
    }
    if (selector.includes(':first')) {
      insertFirst = true;
      selector    = selector.replace(':first', '').trim();
    }

    const $wrap = $tag.closest('.viabill-pricetag-wrap');

    /**
     * Perform the actual DOM move exactly once.
     */
    const insert = () => {
      if ($wrap.data('vb-inserted')) { return; }        // safety

      if (insertAfter) {
        insertFirst ? $(selector).first().after($wrap)
                    : $(selector).after($wrap);
      } else {
        insertFirst ? $(selector).first().before($wrap)
                    : $(selector).before($wrap);
      }

      $tag.addClass('viabill-pricetag');
      $wrap.data('vb-inserted', true);
      $tag.data('vb-attached', true);

      // Let ViaBill recalc its price if needed
      $tag[0].dispatchEvent(new CustomEvent('vb-update-price'));
    };

    // Case 1 – target already exists - insert immediately
    if ($(selector).length) {
      insert();
      return;
    }

    // Case 2 – target not yet in DOM - observe until it appears
    const obs = new MutationObserver(() => {
      if ($(selector).length) {
        insert();
        obs.disconnect();
      }
    });

    obs.observe(document.body, { childList: true, subtree: true });

    // Fallback for very old browsers (no MutationObserver)
    if (!('MutationObserver' in window)) {
      const intId = setInterval(() => {
        if ($(selector).length) {
          insert();
          clearInterval(intId);
        }
      }, 250);
    }
  }

});
 