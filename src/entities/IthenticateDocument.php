<?php

namespace Drupal\ithenticate\entities;

use bsobbe\ithenticate\Ithenticate;

/**
 * The storage helper class for iThenticate database records.
 */
class IthenticateDocument {

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The entity bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The entity ID.
   *
   * @var int
   */
  protected $entityId;

  /**
   * The iThenticate document ID.
   *
   * @var int
   */
  protected $ithenticateDocumentId;

  /**
   * The iThenticate report ID.
   *
   * @var int
   */
  protected $ithenticateReportId;

  /**
   * The iThenticate report view only URL.
   *
   * @var string
   */
  protected $ithenticateReportUrl;

  /**
   * IthenticateDocument constructor.
   *
   * @param string|null $entity_type
   *   The entity type.
   * @param string|null $bundle
   *   The entity bundle.
   * @param int|null $entity_id
   *   The entity ID.
   * @param int|null $document_id
   *   The document ID.
   * @param int|null $report_id
   *   The report ID.
   * @param string|null $report_url
   *   The report URl.
   */
  public function __construct(string $entity_type = NULL, string $bundle = NULL, int $entity_id = NULL, int $document_id = NULL, int $report_id = NULL, string $report_url = NULL) {
    $this->entityType = $entity_type;
    $this->bundle = $bundle;
    $this->entityId = $entity_id;
    $this->ithenticateDocumentId = $document_id;
    $this->ithenticateReportId = $report_id;
    $this->ithenticateReportUrl = $report_url;
  }

  /**
   * Returns the entity type.
   *
   * @return string
   *   The entity type.
   */
  public function getEntityType(): ?string {
    return $this->entityType;
  }

  /**
   * Sets the entity type.
   *
   * @param string $entity_type
   *   The entity type.
   */
  public function setEntityType(string $entity_type): void {
    $this->entityType = $entity_type;
  }

  /**
   * Returns the bundle.
   *
   * @return string
   *   The entity bundle.
   */
  public function getBundle(): ?string {
    return $this->bundle;
  }

  /**
   * Sets the bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   */
  public function setBundle(string $bundle): void {
    $this->bundle = $bundle;
  }

  /**
   * Gets the entity ID.
   *
   * @return int
   *   The entity ID.
   */
  public function getEntityId(): ?int {
    return $this->entityId;
  }

  /**
   * Sets the entity ID.
   *
   * @param int $entity_id
   *   The entity ID.
   */
  public function setEntityId(int $entity_id): void {
    $this->entityId = $entity_id;
  }

  /**
   * Returns teh document ID.
   *
   * @return int
   *   The document ID.
   */
  public function getIthenticateDocumentId(): ?int {
    return $this->ithenticateDocumentId;
  }

  /**
   * Sets the document ID.
   *
   * @param int $document_id
   *   The document ID.
   */
  public function setIthenticateDocumentId(int $document_id): void {
    $this->ithenticateDocumentId = $document_id;
  }

  /**
   * Returns the report ID.
   *
   * @return int
   *   The report ID.
   */
  public function getIthenticateReportId(): ?int {
    return $this->ithenticateReportId;
  }

  /**
   * Sets the report ID.
   *
   * @param int $report_id
   *   The report ID.
   */
  public function setIthenticateReportId(int $report_id): void {
    $this->ithenticateReportId = $report_id;
  }

  /**
   * Returns the report URL.
   *
   * @return string
   *   The report URL.
   */
  public function getIthenticateReportUrl(): ?string {
    return $this->ithenticateReportUrl;
  }

  /**
   * Sets the report URL.
   *
   * @param string $report_url
   *   The report URL.
   */
  public function setIthenticateReportUrl(string $report_url): void {
    $this->ithenticateReportUrl = $report_url;
  }

  /**
   * Loads an IthenticateDocument by iThenticate document ID.
   *
   * @param int $document_id
   *   The document ID.
   *
   * @return \Drupal\ithenticate\entities\IthenticateDocument|null
   *   The loaded object or NULL if none are found.
   */
  public static function loadByIthenticateDocumentId(int $document_id) {
    $document = self::loadSingleByProperties(['ithenticate_document_id' => $document_id]);
    if (empty($document)) {
      return NULL;
    }

    return new static($document->entity_type, $document->bundle, $document->entity_id, $document->ithenticate_document_id, $document->ithenticate_report_id, $document->ithenticate_report_url);
  }

  /**
   * Loads an IthenticateDocument by iThenticate report ID.
   *
   * @param int $report_id
   *   The report ID.
   *
   * @return \Drupal\ithenticate\entities\IthenticateDocument|null
   *   The loaded object or NULL if none are found.
   */
  public static function loadByIthenticateReportId(int $report_id) {
    $document = self::loadSingleByProperties(['ithenticate_report_id' => $report_id]);
    if (empty($document)) {
      return NULL;
    }

    return new static($document->entity_type, $document->bundle, $document->entity_id, $document->ithenticate_document_id, $document->ithenticate_report_id, $document->ithenticate_report_url);
  }

  /**
   * Loads an IthenticateDocument by iThenticate entity ID.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   * @param int $entity_id
   *   The entity ID.
   *
   * @return \Drupal\ithenticate\entities\IthenticateDocument|null
   *   The loaded object or NULL if none are found.
   */
  public static function loadByEntityData(string $entity_type, string $bundle, int $entity_id) {
    $document = self::loadSingleByProperties([
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'entity_id' => $entity_id,
    ]);
    if (empty($document)) {
      return NULL;
    }

    return new static($document->entity_type, $document->bundle, $document->entity_id, $document->ithenticate_document_id, $document->ithenticate_report_id, $document->ithenticate_report_url);
  }

  /**
   * Loads one of the document entities by properties.
   *
   * This is meant to be used to load one document.
   *
   * @param array $properties
   *   An associative array of values to load by.
   *
   * @return object|null
   *   The loaded object or NULL if none are found.
   */
  protected static function loadSingleByProperties(array $properties) {
    $query = db_select('ithenticate_documents', 'i');
    $query->fields('i', []);
    foreach ($properties as $key => $value) {
      $query->condition($key, $value);
    }
    $results = $query->execute()->fetchAllAssoc('entity_id');

    return empty($results) ? NULL : reset($results);
  }

  /**
   * Stores the data in the database.
   *
   * @return int
   *   The result of the db_merge.
   */
  public function save() {
    if (empty($this->ithenticateDocumentId)) {
      throw new \RuntimeException('Cannot save an iThenticate document without a document ID.');
    }

    if (empty($this->entityType) || empty($this->bundle) || empty($this->entityId)) {
      throw new \RuntimeException('Cannot save an iThenticate document entity without an entity type, bundle and ID.');
    }

    return db_merge('ithenticate_documents')
      ->key([
        'entity_type' => $this->entityType,
        'bundle' => $this->bundle,
        'entity_id' => $this->entityId,
      ])
      ->fields([
        'ithenticate_document_id' => $this->ithenticateDocumentId,
        'ithenticate_report_id' => $this->ithenticateReportId,
        'ithenticate_report_url' => $this->ithenticateReportUrl,
      ])
      ->execute();
  }

}
