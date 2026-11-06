<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'Viabill_Legal_Note' ) ) {
  /**
   * Legal note (credit warning) shown next to the ViaBill payment methods on
   * the checkout page.
   *
   * It is configured in the "Legal Note" section of the gateway settings: it
   * can be switched off, and the merchant can replace the default text with
   * their own. The default text is translated in the plugin's language files
   * and is shown either in the shop's language ("auto") or in a fixed language.
   */
  class Viabill_Legal_Note {
    /**
     * Gateway setting: 'yes' to show the legal note (the default).
     *
     * @var string
     */
    const ENABLED_SETTING = 'legal-note-enabled';

    /**
     * Gateway setting: the merchant's own legal note, empty for the default.
     *
     * @var string
     */
    const TEXT_SETTING = 'legal-note-text';

    /**
     * Gateway setting: language of the default legal note ('auto', 'en', 'da' or 'es').
     *
     * @var string
     */
    const LANGUAGE_SETTING = 'legal-note-language';

    /**
     * Default legal note. It must be identical to the text in get_default_text(),
     * which is the one the translation tools pick up.
     *
     * @var string
     */
    const DEFAULT_TEXT = 'Warning! Borrow responsibly - it costs money.';

    /**
     * Locales of the language files, keyed by language code.
     *
     * @var array
     */
    private static $locales = array(
      'da' => 'da_DK',
      'es' => 'es_ES',
    );

    /**
     * Return the options of the language setting.
     *
     * @return array
     */
    public static function get_languages() {
      return array(
        'auto' => __( 'Auto', 'viabill' ),
        'en'   => __( 'English', 'viabill' ),
        'da'   => __( 'Danish', 'viabill' ),
        'es'   => __( 'Spanish', 'viabill' ),
      );
    }

    /**
     * Return true if the legal note should be shown. It is shown unless the
     * merchant has switched it off.
     *
     * @return bool
     */
    public static function is_enabled() {
      $enabled = Viabill_Main::get_gateway_settings( self::ENABLED_SETTING );
      return false === $enabled || 'yes' === $enabled;
    }

    /**
     * Return the default legal note in the given language.
     *
     * @param  string $language 'auto' (the shop's language), 'en', 'da' or 'es'.
     * @return string
     */
    public static function get_default_text( $language = 'auto' ) {
      if ( 'en' === $language ) {
        return self::DEFAULT_TEXT;
      }

      if ( isset( self::$locales[ $language ] ) ) {
        // Load the language file of that locale under its own text domain, so
        // the text can be translated regardless of the shop's language.
        $locale = self::$locales[ $language ];
        $domain = 'viabill-legal-note-' . $locale;
        if ( ! is_textdomain_loaded( $domain ) ) {
          load_textdomain( $domain, VIABILL_DIR_PATH . 'languages/viabill-' . $locale . '.mo' );
        }
        return translate( self::DEFAULT_TEXT, $domain ); // phpcs:ignore WordPress.WP.I18n
      }

      return __( 'Warning! Borrow responsibly - it costs money.', 'viabill' );
    }

    /**
     * Return the legal note to show at the checkout: the merchant's own text if
     * set, otherwise the default text. Returns empty string if switched off.
     *
     * @return string
     */
    public static function get_text() {
      if ( ! self::is_enabled() ) {
        return '';
      }

      $text = Viabill_Main::get_gateway_settings( self::TEXT_SETTING );
      $text = is_string( $text ) ? trim( $text ) : '';

      if ( '' === $text ) {
        $language = Viabill_Main::get_gateway_settings( self::LANGUAGE_SETTING );
        $text     = self::get_default_text( is_string( $language ) ? $language : 'auto' );
      }

      return (string) apply_filters( 'viabill_legal_note_text', $text );
    }

    /**
     * Return the legal note HTML for the checkout page, or empty string.
     *
     * @return string
     */
    public static function get_html() {
      $text = self::get_text();
      if ( '' === $text ) {
        return '';
      }

      return '<div class="viabill-legal-note">' . wpautop( wp_kses_post( $text ) ) . '</div>';
    }
  }
}
