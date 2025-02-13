<?php

namespace Drupal\side_migration\Plugin\migrate\source;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;

/**
 * Provides list of image fields.
 *
 * @MigrateSource(
 *   id = "image_field"
 * )
 *
 * Use as follows:
 *
 * @code
 * source:
 *   plugin: image_field
 *   field:
 *     - field_image
 *   type: article
 * @endcode
 */
class ImageField extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = [
      'id' => [
        'type' => 'string',
//        'unsigned' => FALSE,
//        'size' => 'big',
      ],
    ];
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'id' => $this->t('Image ID'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return "image_field";
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Fetches image data from the database.
   */
  private function fetchItems() {
    $connection = Database::getConnection('default', 'migrate');
    $field = $this->configuration['field'];
    $type = $this->configuration['type'];
    $items = [];
      $query = $connection->select('node__' . $field, 'n_fi');
      $query->fields('n_fi');
//    $query->addField('n_fi', 'entity_id');
//    $query->addField('n_fi', 'delta');
//    $query->addField('n_fi', 'delta');
//      $query->leftJoin('file_managed', 'fm', 'n_fi.' . $field . '_fid = fm.fid');
//      $query->leftJoin('field_data_field_file_image_alt_text', 'n_fifiat', 'n_fi.' . $field . '_fid = n_fifiat.entity_id');
//      $query->leftJoin('field_data_field_file_image_title_text', 'n_fifitt', 'n_fi.' . $field . '_fid = n_fifitt.entity_id');
//      $query->addField('fm', 'filename');
//      $query->addField('fm', 'uid');
//      $query->addField('n_fi', 'field_image_alt', 'alt');
//      $query->addField('n_fi', 'field_image_title', 'title');
        $query->condition('n_fi.bundle', $type);
      $results = $query->execute();
      foreach ($results as $record) {
        $items[] = $record;
      }

    return $items;
  }

  /**
   * Initializes the iterator with the source data.
   *
   * @return \Iterator
   *   An iterator containing the data for this source.
   */
  protected function initializeIterator() {
    $items = $this->fetchItems();
    $rows = [];
    if ($items) {
      foreach ($items as $item) {
        $id= $item->field_image_target_id;
        $rows[$id] = [
          'id' =>$id,
          'target_id'=>$id,
          'alt'=>$item->field_image_alt,
          'title'=>$item->field_image_title
        ];
      }
    }
    return new \ArrayIterator($rows);
  }

}
