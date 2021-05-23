<?php

/**
 * @file
 * Admin settings file for the iThenticate module.
 */

use bsobbe\ithenticate\Ithenticate;

/**
 * Form callback for the ithenticate settings.
 *
 * @param array $form
 *   The form array.
 * @param array $form_state
 *   The form state array.
 *
 * @return array
 *   The form array.
 */
function ithenticate_admin_settings(array $form, array &$form_state) {
  $form['ithenticate_user_name'] = [
    '#type' => 'textfield',
    '#title' => t('User name'),
    '#required' => TRUE,
    '#default_value' => variable_get('ithenticate_user_name', ''),
  ];

  $form['ithenticate_user_pass'] = [
    '#type' => 'textfield',
    '#title' => t('Password'),
    '#required' => TRUE,
    '#default_value' => variable_get('ithenticate_user_pass', ''),
  ];

  $form['ithenticate_submission_folder_number'] = [
    '#type' => 'textfield',
    '#title' => t('Folder number'),
    '#required' => TRUE,
    '#default_value' => variable_get('ithenticate_submission_folder_number', ''),
  ];

  $form['ithenticate_send_email_on_success'] = [
    '#type' => 'checkbox',
    '#title' => t('Send email'),
    '#description' => t('Send email when the report URL is fetched automatically.'),
    '#default_value' => variable_get('ithenticate_send_email_on_success'),
  ];

  $form['ithenticate_email_recipient'] = [
    '#type' => 'textfield',
    '#title' => 'Email recipient',
    '#default_value' => variable_get('ithenticate_email_recipient'),
  ];

  $form['test_api'] = [
    '#type' => 'submit',
    '#value' => t('Test API'),
    '#submit' => ['ithenticate_admin_settings_test_api'],
  ];

  return system_settings_form($form);
}

/**
 * Tests the API endpoint.
 *
 * @param array $form
 *   The form array.
 * @param array $form_state
 *   The form API.
 */
function ithenticate_admin_settings_test_api(array $form, array &$form_state) {
  $form_state['rebuild'] = TRUE;
  $service = new Ithenticate($form_state['values']['ithenticate_user_name'], $form_state['values']['ithenticate_user_pass']);

  if (!empty($service->getSid())) {
    drupal_set_message(t('Connection established successfully. SID: :sid', [
      ':sid' => $service->getSid(),
    ]));
  }
  else {
    drupal_set_message(t('Could not login to the service. No SID was retrieved.'));
  }
}
