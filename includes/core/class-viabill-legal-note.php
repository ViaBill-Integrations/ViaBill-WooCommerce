<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'Viabill_Legal_Note' ) ) {
  /**
   * Legal note (credit warning) shown next to the ViaBill payment methods on
   * the checkout page.
   *
   * It is always shown. The text is the default legal note, translated in the
   * plugin's language files, in the shop's language ("auto") or in a fixed
   * language. The merchant can only change how it looks, in the "Legal Note"
   * section of the gateway settings.
   */
  class Viabill_Legal_Note {
    /**
     * Gateway settings field that shows the legal note, read only. Nothing is
     * stored under this key.
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
     * Gateway setting: font size of the legal note (e.g. '14px', '0.9em'),
     * empty to use the font size of the theme.
     *
     * @var string
     */
    const FONT_SIZE_SETTING = 'legal-note-font-size';

    /**
     * Gateway setting: font style of the legal note ('normal', 'bold',
     * 'italic' or 'bold-italic').
     *
     * @var string
     */
    const FONT_STYLE_SETTING = 'legal-note-font-style';

    /**
     * Gateway setting: text color of the legal note (hex), empty to use the
     * text color of the theme.
     *
     * @var string
     */
    const COLOR_SETTING = 'legal-note-color';

    /**
     * Gateway setting: text alignment of the legal note ('', 'left', 'center'
     * or 'right'), empty to use the alignment of the theme.
     *
     * @var string
     */
    const ALIGN_SETTING = 'legal-note-align';

    /**
     * Gateway setting: extra CSS declarations applied to the legal note.
     *
     * @var string
     */
    const EXTRA_CSS_SETTING = 'legal-note-extra-css';

    /**
     * Gateway setting: space above the legal note (e.g. '4px', '-12px').
     *
     * @var string
     */
    const MARGIN_TOP_SETTING = 'legal-note-margin-top';

    /**
     * Gateway setting: space below the legal note (e.g. '4px').
     *
     * @var string
     */
    const MARGIN_BOTTOM_SETTING = 'legal-note-margin-bottom';

    /**
     * Default font style: the same as the payment method name.
     *
     * @var string
     */
    const DEFAULT_FONT_STYLE = 'normal';

    /**
     * Default space above the legal note. The block checkout gives the payment
     * method name 16px of bottom padding, so -12px leaves a 4px gap.
     *
     * @var string
     */
    const DEFAULT_MARGIN_TOP = '-12px';

    /**
     * Default space below the legal note. The payment method box has its own
     * bottom padding.
     *
     * @var string
     */
    const DEFAULT_MARGIN_BOTTOM = '0px';

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
     * Return the options of the font style setting.
     *
     * @return array
     */
    public static function get_font_styles() {
      return array(
        'normal'      => __( 'Normal', 'viabill' ),
        'bold'        => __( 'Bold', 'viabill' ),
        'italic'      => __( 'Italic', 'viabill' ),
        'bold-italic' => __( 'Bold italic', 'viabill' ),
      );
    }

    /**
     * Return the options of the text alignment setting.
     *
     * @return array
     */
    public static function get_alignments() {
      return array(
        ''       => __( 'Theme default', 'viabill' ),
        'left'   => __( 'Left', 'viabill' ),
        'center' => __( 'Center', 'viabill' ),
        'right'  => __( 'Right', 'viabill' ),
      );
    }

    /**
     * Return a valid CSS font size, or empty string. A plain number is taken
     * as pixels.
     *
     * @param  mixed $value Value entered by the merchant.
     * @return string
     */
    public static function sanitize_font_size( $value ) {
      $value = is_string( $value ) ? strtolower( trim( $value ) ) : '';
      $value = str_replace( ',', '.', $value );

      if ( ! preg_match( '/^(\d+(?:\.\d+)?|\.\d+)\s*(px|em|rem|pt|%)?$/', $value, $matches ) ) {
        return '';
      }
      if ( (float) $matches[1] <= 0 ) {
        return '';
      }

      return $matches[1] . ( empty( $matches[2] ) ? 'px' : $matches[2] );
    }

    /**
     * Return the extra CSS declarations (e.g. "padding: 8px; border: 1px solid
     * #ccc") in a form that is safe to print inside a style attribute. Only
     * declarations are supported, so anything that could open a new rule or
     * a tag is removed.
     *
     * @param  mixed $css Value entered by the merchant.
     * @return string
     */
    public static function sanitize_extra_css( $css ) {
      if ( ! is_string( $css ) || '' === trim( $css ) ) {
        return '';
      }

      $css = wp_strip_all_tags( $css );
      $css = preg_replace( '#/\*.*?\*/#s', '', $css );
      $css = str_replace( array( '{', '}', '<', '>', '\\' ), '', $css );
      $css = preg_replace( '/expression\s*\(|javascript\s*:|@import/i', '', $css );
      $css = preg_replace( '/\s+/', ' ', $css );

      return trim( $css, " ;\t\n\r" );
    }

    /**
     * Return the inline style of the legal note, built from the styling
     * settings, e.g. "font-weight:600;color:#cc0000".
     *
     * @return string
     */
    public static function get_style() {
      $declarations = array();

      $font_size = self::sanitize_font_size( Viabill_Main::get_gateway_settings( self::FONT_SIZE_SETTING ) );
      if ( '' !== $font_size ) {
        $declarations[] = 'font-size:' . $font_size;
      }

      $font_style = Viabill_Main::get_gateway_settings( self::FONT_STYLE_SETTING );
      if ( ! is_string( $font_style ) || ! array_key_exists( $font_style, self::get_font_styles() ) ) {
        $font_style = self::DEFAULT_FONT_STYLE;
      }
      $declarations[] = 'font-weight:' . ( false !== strpos( $font_style, 'bold' ) ? '600' : 'normal' );
      $declarations[] = 'font-style:' . ( false !== strpos( $font_style, 'italic' ) ? 'italic' : 'normal' );

      $color = Viabill_Main::get_gateway_settings( self::COLOR_SETTING );
      $color = is_string( $color ) ? sanitize_hex_color( trim( $color ) ) : '';
      if ( ! empty( $color ) ) {
        $declarations[] = 'color:' . $color;
      }

      $align = Viabill_Main::get_gateway_settings( self::ALIGN_SETTING );
      if ( is_string( $align ) && in_array( $align, array( 'left', 'center', 'right' ), true ) ) {
        $declarations[] = 'text-align:' . $align;
      }

      $declarations[] = 'margin-top:' . self::get_margin( self::MARGIN_TOP_SETTING );
      $declarations[] = 'margin-bottom:' . self::get_margin( self::MARGIN_BOTTOM_SETTING );

      // The extra CSS comes last, so it can override the controls above.
      $extra_css = self::sanitize_extra_css( Viabill_Main::get_gateway_settings( self::EXTRA_CSS_SETTING ) );
      if ( '' !== $extra_css ) {
        $declarations[] = $extra_css;
      }

      return (string) apply_filters( 'viabill_legal_note_style', implode( ';', $declarations ) );
    }

    /**
     * Return a valid CSS margin, or empty string. A plain number is taken as
     * pixels, and negative values are allowed, e.g. to move the legal note
     * closer to the payment method name.
     *
     * @param  mixed $value Value entered by the merchant.
     * @return string
     */
    public static function sanitize_margin( $value ) {
      $value = is_string( $value ) ? strtolower( trim( $value ) ) : '';
      $value = str_replace( ',', '.', $value );

      if ( ! preg_match( '/^(-?)(\d+(?:\.\d+)?|\.\d+)\s*(px|em|rem|pt|%)?$/', $value, $matches ) ) {
        return '';
      }
      if ( 0.0 === (float) $matches[2] ) {
        return '0px';
      }

      return $matches[1] . $matches[2] . ( empty( $matches[3] ) ? 'px' : $matches[3] );
    }

    /**
     * Settings field sanitize callback for the top margin: stores a valid
     * margin, or the default if the value is empty or not valid.
     *
     * @param  mixed $value Posted value.
     * @return string
     */
    public static function sanitize_margin_top_setting( $value ) {
      $margin = self::sanitize_margin( is_string( $value ) ? wp_unslash( $value ) : '' );
      return '' !== $margin ? $margin : self::DEFAULT_MARGIN_TOP;
    }

    /**
     * Settings field sanitize callback for the bottom margin: stores a valid
     * margin, or the default if the value is empty or not valid.
     *
     * @param  mixed $value Posted value.
     * @return string
     */
    public static function sanitize_margin_bottom_setting( $value ) {
      $margin = self::sanitize_margin( is_string( $value ) ? wp_unslash( $value ) : '' );
      return '' !== $margin ? $margin : self::DEFAULT_MARGIN_BOTTOM;
    }

    /**
     * Settings field sanitize callback for the font size: stores a valid font
     * size, or empty (the font size of the theme) if it is not valid.
     *
     * @param  mixed $value Posted value.
     * @return string
     */
    public static function sanitize_font_size_setting( $value ) {
      return self::sanitize_font_size( is_string( $value ) ? wp_unslash( $value ) : '' );
    }

    /**
     * Return the margin stored in one of the margin settings, or the default.
     *
     * @param  string $setting MARGIN_TOP_SETTING or MARGIN_BOTTOM_SETTING.
     * @return string
     */
    private static function get_margin( $setting ) {
      $margin = self::sanitize_margin( Viabill_Main::get_gateway_settings( $setting ) );
      if ( '' !== $margin ) {
        return $margin;
      }
      return self::MARGIN_TOP_SETTING === $setting ? self::DEFAULT_MARGIN_TOP : self::DEFAULT_MARGIN_BOTTOM;
    }

    /**
     * Return the default legal note in each language of the language setting,
     * keyed like its options.
     *
     * @return array
     */
    public static function get_default_texts() {
      $texts = array();
      foreach ( array_keys( self::get_languages() ) as $language ) {
        $texts[ $language ] = self::get_default_text( $language );
      }
      return $texts;
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
     * Return the legal note to show at the checkout: the default text in the
     * language of the language setting.
     *
     * @return string
     */
    public static function get_text() {
      $language = Viabill_Main::get_gateway_settings( self::LANGUAGE_SETTING );
      $text     = self::get_default_text( is_string( $language ) ? $language : 'auto' );

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

      $style = self::get_style();
      $style = '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '';

      return '<div class="viabill-legal-note"' . $style . '>' . wpautop( wp_kses_post( $text ) ) . '</div>';
    }
  }
}
