<?php

namespace Drupal\ithenticate;

use bsobbe\ithenticate\Ithenticate;
use Drupal\ithenticate\entities\IthenticateDocument;

/**
 * A wrapper class for the \bsobbe\ithenticate\Ithenticate class.
 *
 * Prepares and integrates the Ithenticate class with Drupal and stored
 * settings.
 */
class IthenticateRequestWrapper {

  /**
   * The URL of the endpoint.
   *
   * @var \bsobbe\ithenticate\Ithenticate
   */
  protected $service;

  /**
   * Constructs an instance of the iThenticate class.
   *
   * @param string $username
   *   The user name.
   * @param string $password
   *   The user password.
   * @param string $url
   *   The API endpoint.
   */
  public function __construct($username = NULL, $password = NULL, $url = NULL) {
    $this->service = new Ithenticate(
      empty($username) ? variable_get('ithenticate_user_name') : $username,
      empty($password) ? variable_get('ithenticate_user_pass') : $password
    );
    $this->service->setUrl(empty($url) ? variable_get('ithenticate_api_url') : $url);
  }

  /**
   * Returns the session ID (SID).
   *
   * @return string
   *   The session ID.
   */
  public function getSid() {
    return $this->service->getSid();
  }

  /**
   * Submits a document to the service.
   *
   * @param object|null $node
   *   The paper node object that will be submitted to the service.
   *
   * @return \Drupal\ithenticate\entities\IthenticateDocument $document
   *   The new document that has been saved in the database.
   */
  public function submitDocument($node) {
    $submission_document = IthenticateDocument::loadByEntityData('node', 'paper', $node->nid);
    if (empty($submission_document)) {
      $submission_document = new IthenticateDocument('node', $node->type, $node->nid);
    }
    if (!empty($submission_document) && !empty($submission_document->getIthenticateDocumentId())) {
      drupal_set_message("Node with ID {$node->nid} seems to already have a submitted document in iThenticate.");
      return $submission_document;
    }

    if (empty($node->field_paper_file[LANGUAGE_NONE][0]['fid'])) {
      throw new \RuntimeException('Cannot submit a paper without a file.');
    }
    $file = file_load($node->field_paper_file[LANGUAGE_NONE][0]['fid']);
    if (empty($file)) {
      throw new \RuntimeException('Failed to load the file for node ' . $node->nid);
    }

    // @todo Use the author data instead of the user data when available.
    $user = user_load($node->uid);
    $title = $node->title;
    $first_name = $user->field_user_name[LANGUAGE_NONE][0]['value'];
    $last_name = $user->field_user_surname[LANGUAGE_NONE][0]['value'];
    $filename = $file->filename;
    $file_contents = file_get_contents(drupal_realpath($file->uri));
    $folder_number = variable_get('ithenticate_submission_folder_number');

    $document_id = $this->service->submitDocument($title, $first_name, $last_name, $filename, $file_contents, $folder_number);
    $submission_document->setIthenticateDocumentId($document_id);
    $submission_document->save();
    return $submission_document;
  }

  /**
   * Checks whether the report for the document has been created.
   *
   * @param int $document_id
   *   The document ID.
   *
   * @return bool
   *   Whether a document report is still being generated or not.
   */
  public function checkIsDocumentReportPending($document_id) {
    $state = $this->service->fetchDocumentReportState($document_id);
    if ($state === FALSE) {
      throw new \RuntimeException('An unknown error occurred when decoding the response for the ' . __METHOD__ . '.');
    }

    if ($state['is_pending'] == 0) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Retrieves the report ID for a document.
   *
   * @param \Drupal\ithenticate\entities\IthenticateDocument $document
   *   The document related to the report.
   *
   * @return \Drupal\ithenticate\entities\IthenticateDocument
   *   The updated document.
   */
  public function fetchDocumentReportId($document) {
    $document_report_id = $this->service->fetchDocumentReportId($document->getIthenticateDocumentId());
    if ($document_report_id === FALSE) {
      drupal_set_message(t('Failed to retrieve the document ID. Is the report still pending?'), 'error');
    }
    else {
      $document->setIthenticateDocumentId($document_report_id);
      $document->save();
    }
    return $document;
  }

  /**
   * Retrieves the report read-only URL for a document.
   *
   * @param \Drupal\ithenticate\entities\IthenticateDocument $document
   *   The document related to the report.
   *
   * @return \Drupal\ithenticate\entities\IthenticateDocument
   *   The updated document.
   */
  public function fetchDocumentReportUrl($document) {
    $document_report_url = $this->service->fetchDocumentReportUrl($document->getIthenticateDocumentId());
    if ($document_report_url === FALSE) {
      drupal_set_message(t('Failed to retrieve the document URL. Is the report still pending?'), 'error');
    }
    else {
      $document->setIthenticateReportUrl($document_report_url);
      $document->save();
    }
    return $document;
  }

}
