<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'Viabill_Registration' ) ) {
  /**
   * Viabill_Registration class
   */
  class Viabill_Registration {
    /**
     * API's interface.
     *
     * @var Viabill_Connector
     */
    private $connector;

    /**
     * Merchant's profile, object which holds merchant's data and it's methods.
     *
     * @var Viabill_Merchant_Profile
     */
    private $merchant;

    /**
     * Logger.
     *
     * @var Viabill_Logger
     */
    private $logger;

    /**
     * Was the registration already tried.
     *
     * @var bool
     */
    public static $tried_to_register = false;

    /**
     * Registration page slug.
     *
     * @static
     * @var string
     */
    const SLUG = 'viabill-register';

    /**
     * Class constructor, initialize class attributes and defines hooked methods.
     *
     * @param boolean $register_settings_page Defaults to false.
     */
    public function __construct( $register_settings_page = false ) {
      if ( ! defined( 'VIABILL_DIR_PATH' ) ) {	
        define( 'VIABILL_DIR_PATH', plugin_dir_path( __FILE__ ) );
      }
      
      require_once( VIABILL_DIR_PATH . '/includes/core/class-viabill-notices.php' );
      require_once( VIABILL_DIR_PATH . '/includes/core/class-viabill-connector.php' );
      require_once( VIABILL_DIR_PATH . '/includes/core/class-viabill-merchant-profile.php' );
      require_once( VIABILL_DIR_PATH . '/includes/utilities/class-viabill-logger.php' );

      $this->connector = new Viabill_Connector();
      $this->merchant  = new Viabill_Merchant_Profile();
      // Enabled by default because at this point gateway is always disabled.
      $this->logger = new Viabill_Logger( true );

      add_action( 'admin_init', array( $this, 'maybe_process' ) );

      if ( $register_settings_page && ! Viabill_Main::is_merchant_registered() ) {
        add_action( 'admin_menu', array( $this, 'register_settings_page' ), 200 );
      }
    }

    /**
     * Return registration page admin URL.
     *
     * @return string
     */
    public static function get_admin_url() {
      return get_admin_url( null, 'admin.php?page=' . self::SLUG );
    }

    /**
     * Trigger process method and, if successful, redirect to notices page.
     */
    public function maybe_process() {
      if ( self::$tried_to_register ) {
        return false;
      }
      self::$tried_to_register = true;

      $response = $this->process();
      if ( is_array( $response ) ) {
        if ( isset( $response['success'] ) && $response['success'] ) {
          wp_safe_redirect( Viabill_Main::get_settings_link() );
          exit;
        } else {
          $this->reg_response = $response;
          add_action( 'admin_notices', array( $this, 'show_register_wp_notice' ) );
        }
      }
    }

    /**
     * Process manually entered ViaBill account credentials (fetched from
     * $_POST) and return an array with the following structure:
     *    ['success' => bool, 'message' => string]
     * or false if nothing was submitted.
     *
     * The merchant is expected to obtain the API key, API secret and
     * PriceTag script elsewhere (e.g. from the ViaBill merchant portal)
     * and enter them here manually. No remote call to the ViaBill server
     * is performed.
     *
     * @return array|bool
     */
    private function process() {
      $response = array(
        'success' => false,
        'message' => __( 'Something went wrong, please try again.', 'viabill' ),
      );

      if ( isset( $_POST[ self::SLUG ] ) && isset( $_POST[ self::SLUG . '-nonce' ] ) ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
          return $response;
        }

        $nonce = sanitize_key( $_POST[ self::SLUG . '-nonce' ] );
        if ( wp_verify_nonce( $nonce, self::SLUG . '-action' ) === 1 ) {
          $key      = isset( $_POST['viabill-api-key'] ) ? sanitize_text_field( wp_unslash( $_POST['viabill-api-key'] ) ) : '';
          $secret   = isset( $_POST['viabill-api-secret'] ) ? sanitize_text_field( wp_unslash( $_POST['viabill-api-secret'] ) ) : '';
          $pricetag = isset( $_POST['viabill-pricetag'] ) ? self::sanitize_pricetag_script( wp_unslash( $_POST['viabill-pricetag'] ) ) : '';

          // Allow the merchant to keep the already stored secret by leaving
          // the (masked) secret field empty.
          if ( empty( $secret ) ) {
            $secret = (string) get_option( 'viabill_secret', '' );
          }

          if ( empty( $key ) || empty( $secret ) || empty( $pricetag ) ) {
            $response['message'] = __( 'Please fill in the API key, API secret and PriceTag script. All three values are required.', 'viabill' );
            return $response;
          }

          $is_saved = $this->merchant->save_registration_data(
            array(
              'key'            => $key,
              'secret'         => $secret,
              'pricetagScript' => $pricetag,
            )
          );

          if ( $is_saved ) {
            $response['success'] = true;
            $response['message'] = __( 'ViaBill account credentials saved successfully.', 'viabill' );
          } else {
            $response['message'] = __( 'Failed to save the ViaBill account credentials, please try again.', 'viabill' );
          }

          $this->logger->log( $response['message'], $response['success'] ? 'info' : 'critical' );
          return $response;
        } else {
          $this->logger->log( 'Failed to verify nonce \'' . $nonce . '\' while trying to save the merchant credentials.', 'alert' );
          return $response;
        }
      }
      return false;
    }

    /**
     * Sanitize the PriceTag script. The PriceTag snippet provided by ViaBill
     * is a <script> tag so it can't go through the regular sanitize/kses
     * helpers (they would strip it). Only whitespace normalization is done
     * here; saving is restricted to shop managers/administrators.
     *
     * @param  string $script
     * @return string
     */
    public static function sanitize_pricetag_script( $script ) {
      $script = trim( (string) $script );
      if ( '' === $script ) {
        return '';
      }
      // Require the value to actually look like a script snippet.
      if ( false === stripos( $script, '<script' ) ) {
        return '';
      }
      return $script;
    }

    /**
     * Echo merchant's account credentials form.
     *
     * The merchant obtains the API key, API secret and PriceTag script
     * elsewhere (e.g. from the ViaBill merchant portal) and enters them
     * here manually.
     */
    public function show() {
      $key      = get_option( 'viabill_key', '' );
      $secret   = get_option( 'viabill_secret', '' );
      $pricetag = get_option( 'viabill_pricetag', '' );

      $has_secret = is_string( $secret ) && '' !== $secret;
      ?>
      <form method="post">
        <h2><?php esc_html_e( 'ViaBill Account Credentials', 'viabill' ); ?></h2>
        <p><?php esc_html_e( 'Enter your ViaBill API key, API secret and PriceTag script below. You can find these values in your ViaBill merchant account.', 'viabill' ); ?></p>
        <table class="form-table">
          <tbody>
            <?php
            $this->do_field(
              __( 'API Key', 'viabill' ),
              array(
                'id'       => 'viabill-api-key',
                'name'     => 'viabill-api-key',
                'type'     => 'text',
                'value'    => esc_attr( $key ),
                'class'    => 'input-text regular-input',
                'required' => true,
                'autocomplete' => 'off',
              )
            );
            ?>

            <?php
            // The secret is never printed back to the page. If a secret is
            // already stored the field simply shows a masked placeholder and
            // can be left empty to keep the current value.
            $secret_field_args = array(
              'id'           => 'viabill-api-secret',
              'name'         => 'viabill-api-secret',
              'type'         => 'password',
              'value'        => '',
              'class'        => 'input-text regular-input',
              'autocomplete' => 'new-password',
            );
            if ( $has_secret ) {
              $secret_field_args['placeholder'] = '********';
            } else {
              $secret_field_args['required'] = true;
            }
            $this->do_field( __( 'API Secret', 'viabill' ), $secret_field_args );
            if ( $has_secret ) {
              echo '<tr valign="top"><th scope="row" class="titledesc"></th><td class="forminp"><p class="description">' . esc_html__( 'An API secret is already saved. Leave this field empty to keep it.', 'viabill' ) . '</p></td></tr>';
            }
            ?>

            <tr valign="top">
              <th scope="row" class="titledesc">
                <label for="viabill-pricetag"><?php esc_html_e( 'PriceTag Script', 'viabill' ); ?></label>
              </th>
              <td class="forminp">
                <textarea id="viabill-pricetag" name="viabill-pricetag" class="input-text wide-input" style="width: 400px; min-height: 120px;" required><?php echo esc_textarea( $pricetag ); ?></textarea>
                <p class="description"><?php esc_html_e( 'Paste the whole PriceTag script snippet, including the <script> tags.', 'viabill' ); ?></p>
              </td>
            </tr>

            <?php $this->do_submit( self::SLUG, __( 'Save Credentials', 'viabill' ), self::SLUG . '-action', self::SLUG . '-nonce' ); ?>
          </tbody>
        </table>
      </form>
      <?php
    }

    /**
     * Echo registration submit button HTML.
     *
     * @param string $id
     * @param string $label
     * @param string $nonce_action
     * @param string $nonce_name
     */
    private function do_submit( $id, $label, $nonce_action, $nonce_name ) {
      ?>
      <tr valign="top">
        <th scope="row" class="titledesc"></th>
        <td class="forminp">
          <input id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="button-primary woocommerce-save-button" type="submit" value="<?php echo $label; ?>">
          <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
        </td>
      </tr>
      <?php
    }

    /**
     * Echo registration field's HTML.
     *
     * @param  string $label    Label for input or standalone
     * @param  array  $args     Different attributes:
     *                          array (
     *                            'id'       => 'test_id',
     *                            'name'     => 'test_name',
     *                            'value'    => '',
     *                            'required' => false
     *                          )
     * @param  array  $options  Defaults to empty array.
     */
    private function do_field( $label = '', $args = array(), $options = array() ) {
      array_walk(
        $args,
        function( $attr_val, $attr_name ) use ( &$attrs ) {
          $attrs .= $attr_name . '=\'' . $attr_val . '\' ';
        }
      );
      ?>
      <tr valign="top">
        <th scope="row" class="titledesc">
          <?php if ( ! empty( $label ) && 'checkbox' !== $args['type'] ) : ?>
            <label for="<?php echo $args['id']; ?>"><?php echo $label; ?></label>
          <?php endif; ?>
        </th>
        <td class="forminp">
          <?php if ( 'select' === $args['type'] && $options ) : ?>
            <select <?php echo $attrs ?>>
              <?php foreach ( $options as $option ) : ?>
                <option value="<?php echo $option['code']; ?>"><?php echo $option['name']; ?></option>
              <?php endforeach; ?>
            </select>
          <?php else : ?>
            <input <?php echo $attrs ?>>
            <?php if ( 'checkbox' === $args['type'] && ! empty( $label ) ) : ?>
              <label for="<?php echo $args['id']; ?>"><?php echo $label; ?></label>
            <?php endif; ?>
          <?php endif; ?>
        </td>
      </tr>
      <?php
    }

    /**
     * Echo register notice HTML.
     */
    public function show_register_wp_notice() {
      $type    = isset( $this->reg_response['success'] ) && $this->reg_response['success'] ? 'success' : 'error';
      $message = isset( $this->reg_response['message'] ) ? $this->reg_response['message'] : false;
      if ( ! $message ) {
        if ( 'success' === $type ) {
          $message = __( 'Registration didn\'t went as expected but, for now, everything seems alright.', 'viabill' );
        } else {
          $message = __( 'Something went wrong, please try again.', 'viabill' );
        }
      }
      ?>
      <div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
          <p><?php echo esc_html( $message ); ?></p>
      </div>
      <?php
    }

    /**
     * Register WooCommerce settings subpage.
     */
    public function register_settings_page() {
      add_submenu_page(
        'woocommerce',
        __( 'ViaBill Account Credentials', 'viabill' ),
        __( 'ViaBill Account Credentials', 'viabill' ),
        'manage_woocommerce',
        self::SLUG,
        array( $this, 'show' )
      );
    }

  }
}
