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
 *   table:
 *     - node__field_image
 *   bundle: article
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
   * Fetches image data from the image field table.
   */
  private function fetchItems() {
    $connection = Database::getConnection();
    $table_prefix = $this->configuration['table_prefix'];
    $field = $this->configuration['field'];
    $bundle = $this->configuration['bundle'];
    $items = [];
    $query = $connection->select("{$table_prefix}__{$field}", 'image_table');
    $query->addField('image_table', "{$field}_target_id", 'target_id');
    $query->addField('image_table', "{$field}_alt", 'alt');
    $query->addField('image_table', "{$field}_title", 'title');
    $query->condition('image_table.bundle', $bundle);
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
        $id= $item->target_id;
        $rows[$id] = [
          'id' =>$id,
          'target_id'=>$id,
          'alt'=>$item->alt,
          'title'=>$item->title
        ];
      }
    }
    return new \ArrayIterator($rows);
  }

}
