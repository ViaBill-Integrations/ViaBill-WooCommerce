<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

return array(
  'enabled' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'    => __( 'Enable', 'viabill' ),
    'type'     => 'checkbox',
    'label'    => __( 'Enable ViaBill Payment Gateway', 'viabill' ),
    'default'  => 'no',
    'desc_tip' => false,
  ),
  'title' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Title', 'viabill' ),
    'type'        => 'text',
    'description' => __( 'This controls the title which the user sees during the checkout.', 'viabill' ),
    'default'     => __( 'ViaBill', 'viabill' ),
    'desc_tip'    => true,
  ),
  'description-msg' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Description', 'viabill' ),
    'type'        => 'textarea',
    'description' => __( 'Payment method description that the customer will see on the checkout page.', 'viabill' ),
    'default'     => '',
    'desc_tip'    => true,
  ),
  'confirmation-msg' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Confirmation', 'viabill' ),
    'type'        => 'textarea',
    'description' => __( 'Confirmation message that will be added to the "thank you" page.', 'viabill' ),
    'default'     => __( 'Your account has been charged and your transaction is successful.', 'viabill' ),
    'desc_tip'    => true,
  ),
  'receipt-redirect-msg' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Receipt', 'viabill' ),
    'type'        => 'textarea',
    'description' => __( 'Message that will be added to the "receipt" page. Shown if automatic redirect is enabled.', 'viabill' ),
    'default'     => __( 'Please click on the button below.', 'viabill' ),
    'desc_tip'    => true,
  ),
  'advanced-options' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Advanced options', 'viabill' ),
    'type'        => 'title',
    'description' => '',
  ),
  'in-test-mode' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'ViaBill Test Mode', 'viabill' ),
    'type'        => 'checkbox',
    'label'       => __( 'Enable ViaBill Test Mode', 'viabill' ),
    'description' => __( 'Mode used for testing purposes, disable this for live web shops.', 'viabill' ),
    'default'     => 'no',
    'desc_tip'    => true,
  ),
  'use-logger' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Debug log', 'viabill' ),
    'type'        => 'checkbox',
    'label'       => __( 'Enable logging', 'viabill' ),
    'description' => sprintf( __( 'Log gateway events, stored in %s. Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'viabill' ), '<code>' . WC_Log_Handler_File::get_log_file_path( 'viabill' ) . '</code>' ),
    'default'     => 'no',
  ),
  'auto-redirect' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Automatic redirect', 'viabill' ),
    'type'        => 'checkbox',
    'label'       => __( 'Enable automatic redirect to the ViaBill checkout form', 'viabill' ),
    'description' => __( 'With this option enabled your customers will be automatically redirected to ViaBill checkout form. If the option is disabled the customer will have one more step that they will need to confirm in order to go to ViaBill checkout form.', 'viabill' ),
    'default'     => 'yes',
  ),
  'pending-orders-hidden' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Hide pending orders', 'viabill' ),
    'type'        => 'checkbox',
    'label'       => __( 'Hide orders that have not been sent to ViaBill', 'viabill' ),
    'description' => __( 'With this option enabled orders chosen to be payed with ViaBill but not sent to ViaBill will be hidden from order list in the admin.', 'viabill' ),
    'default'     => 'no',
  ),
  'automatic-refund-status' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Automatic refund', 'viabill' ),
    'type'        => 'checkbox',
    'label'       => __( 'Refund automatically on changing order status to "Refunded"', 'viabill' ),
    'description' => __( 'With this option enabled changing order status to "Refunded" will automatically refund the order by ViaBill payment gateway.', 'viabill' ),
    'default'     => 'no',
  ),
  'auto-capture' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Auto-capture payments', 'viabill' ),
    'type'        => 'select',
    'class'       => 'wc-enhanced-select',
    'description' => __( 'Select this option to automatically capture all approved ViaBill orders. All automatically captured orders will be updated with an order status of, "Processing". Selecting this option will also disable the option to partially capture the order amount.', 'viabill' ),
    'default'     => 'no',
    'options'     => array(
      'no'  => __( 'No', 'viabill' ),
      'yes' => __( 'Yes', 'viabill' ),
    ),
  ),
  'automatic-capture-mail' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Auto-capture email', 'viabill' ),
    'type'        => 'checkbox',
    'label'       => __( 'Only send email for captured orders.', 'viabill' ),
    'description' => __( 'If the Auto-capture is enabled this setting will skip sending email for approved order and will only send mail when the order is captured.', 'viabill' ),
    'default'     => 'no',
  ),
  'capture-order-on-status-switch' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Capture order on status change', 'viabill' ),
    'type'        => 'select',
    'class'       => 'wc-enhanced-select',
    'description' => __( 'Select this option in order to capture the whole order amount by manually switching the order status from, "On Hold" to "Processing".', 'viabill' ),
    'default'     => 'no',
    'options'     => array(
      'no'  => __( 'No', 'viabill' ),
      'yes' => __( 'Yes', 'viabill' ),
    ),
  ),
  'update-db' => array( // phpcs:ignore WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
    'title'       => __( 'Database Update', 'viabill' ),
    'type'        => 'title',
    'description' => Viabill_DB_Update::show_update_field(),
  ),
);
