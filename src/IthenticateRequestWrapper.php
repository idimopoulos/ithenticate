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
   * @param object $node
   *   The paper node object that will be submitted to the service.
   *
   * @return \Drupal\ithenticate\entities\IthenticateDocument $document
   *   The new document that has been saved in the database.
   */
  public function submitDocument($node) {
    $submission_document = IthenticateDocument::loadByEntityData('node', 'paper', $node->nid, $node->vid);
    if (empty($submission_document)) {
      $submission_document = new IthenticateDocument('node', $node->type, $node->nid, $node->vid);
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

    $first_name = NULL;
    $last_name = NULL;
    if (isset($node->field_paper_authors) && module_exists('field_collection')) {
      // For CMS. When JMS also upgrades to explicit authors, this should be
      // universal.
      foreach ($node->field_paper_authors[LANGUAGE_NONE] as $item_data) {
        $author = field_collection_item_load($item_data['value']);
        if (empty($author)) {
          continue;
        }
        foreach ($author->field_author_type[LANGUAGE_NONE] as $author_type) {
          if ($author_type['value'] === 'corresponding') {
            $first_name = $author->field_author_first_name[LANGUAGE_NONE][0]['value'];
            $last_name = $author->field_author_last_name[LANGUAGE_NONE][0]['value'];
            break 2;
          }
        }
      }
    }
    else {
      $user = user_load($node->uid);
      $first_name = $user->field_user_name[LANGUAGE_NONE][0]['value'];
      $last_name = $user->field_user_surname[LANGUAGE_NONE][0]['value'];
    }

    $title = $node->title;
    $filename = $file->filename;
    $file_contents = file_get_contents(drupal_realpath($file->uri));
    $folder_number = variable_get('ithenticate_submission_folder_number');

    $document_id = $this->service->submitDocument($title, $first_name, $last_name, $filename, $file_contents, $folder_number);
    $submission_document->setIthenticateDocumentId($document_id);
    $submission_document->save();
    return $submission_document;
  }

  /**
   * Tries to retrieve the report data for the given document.
   *
   * @param \Drupal\ithenticate\entities\IthenticateDocument $document
   *   The document object.
   */
  public function getReportData(IthenticateDocument $document) {
    if ($document->getIthenticateDocumentId()) {
      $response = $this->service->documentGetRequest($document->getIthenticateDocumentId());
      if (isset($response['errors'])) {
        foreach ($response['errors'] as $name => $errors) {
          drupal_set_message(t('Errors found for element :name: :errors', [
            ':name' => $name,
            ':errors' => implode('<br />', $errors),
          ]), 'error');
        }
        return;
      }
      $response_document = reset($response['documents']);
      if ($response_document['is_pending'] === 1) {
        drupal_set_message('The document is not ready yet.', 'warning');
      }
      else {
        $document->setPercentMatch($response_document['percent_match']);
        if (empty($document->getIthenticateReportId())) {
          $this->fetchDocumentReportId($document);
        }
        $this->fetchDocumentReportUrl($document);
      }
    }
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
    $response = $this->service->documentGetRequest($document_id);
    if (isset($response['errors'])) {
      foreach ($response['errors'] as $name => $errors) {
        drupal_set_message(t('Errors found for element :name: :errors', [
          ':name' => $name,
          ':errors' => implode('<br />', $errors),
        ]), 'error');
      }
      return NULL;
    }

    if ($response['documents'][0]['is_pending'] === 0) {
      return FALSE;
    }

    return TRUE;
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
      $document->setIthenticateReportId($document_report_id);
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
    $response = $this->service->reportGetRequest($document->getIthenticateReportId());
    if (!isset($response['report_url'])) {
      drupal_set_message(t('Failed to retrieve the document URL. Is the report still pending?'), 'error');
    }
    else {
      $document->setIthenticateReportUrl($response['report_url']);
    }
    return $document;
  }

}
