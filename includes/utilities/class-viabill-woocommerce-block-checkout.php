<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

if ( ! class_exists( 'WC_Viabill_Blocks' ) ) {

    // Load required core and utility classes
    require_once VIABILL_DIR_PATH . '/includes/core/class-viabill-payment-gateway.php';
    require_once VIABILL_DIR_PATH . '/includes/core/class-viabill-order-admin.php';
    require_once VIABILL_DIR_PATH . '/includes/core/class-viabill-notices.php';
    require_once VIABILL_DIR_PATH . '/includes/core/class-viabill-support.php';
    require_once VIABILL_DIR_PATH . '/includes/utilities/class-viabill-icon-shortcode.php';
    require_once VIABILL_DIR_PATH . '/includes/utilities/class-viabill-db-update.php';
    require_once VIABILL_DIR_PATH . '/includes/core/class-viabill-api.php';

    final class WC_Viabill_Blocks extends AbstractPaymentMethodType {
        /**
         * Payment method ID — must match frontend registration
         */
        protected $name = 'viabill_official';

        /**
         * @var Viabill_Payment_Gateway
         */
        private $gateway;

        /**
         * Initialize block integration
         */
        public function initialize() {
            $this->settings = get_option( 'woocommerce_viabill_settings', [] );
            $this->gateway  = new Viabill_Payment_Gateway();
        }

        /**
         * Return whether the payment method is active
         */
        public function is_active() {
            return $this->gateway->is_available();
        }

        /**
         * Return the handle(s) of the registered script(s)
         */
        public function get_payment_method_script_handles() {
			wp_register_script(
				'wc-viabill-blocks-integration',
				plugins_url( 'assets/block/checkout.js?v=3', dirname( __FILE__, 2 ) ),
				[ 'wc-blocks-registry', 'wp-element', 'wp-html-entities', 'wp-i18n', 'wc-settings' ],
				null,
				true
			);
			
			if ( function_exists( 'wp_set_script_translations' ) ) {
                wp_set_script_translations(
                    'wc-viabill-blocks-integration',
                    'viabill',
                    plugin_dir_path( dirname( __FILE__ ) ) . 'languages/'
                );
            }            
			
			return [ 'wc-viabill-blocks-integration' ];
		}

		public function get_payment_method_script_handles_for_admin() {
			return $this->get_payment_method_script_handles();
		}		
		
        /**
         * Pass dynamic data from PHP to JS block
         */		
        public function get_payment_method_data() {
			return [
				'title'       => $this->gateway->title,
				'description' => $this->gateway->description,
				'supports'    => [ 'products' ],
			];
		}
                        
    }
}