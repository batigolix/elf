<?php

namespace Drupal\inside_job\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides get_translation_source.
 *
 * This is a slightly fake plugin in order to retrieve the title of the
 * source node that will be translated during the migration. In reality you
 * would fetch such a value from a CSV or something a like.
 *
 * Example:
 *
 * @code
 * source:
 *   plugin: get_translation_source
 *   source: title/0/value
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "get_translation_source",
 *   handle_multiples = FALSE
 * )
 */
class GetTranslationSource extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $nodes = [
      "Ruimtepakken vervormen met houding in de bewolkte kosmos" => 'Space suits warp with attitude at the cloudy cosmos',
      "Ubi est alter onus?" => 'Countless tribbles place boldly, calm klingons',
      "Mori callide ducunt ad raptus species." => "Collectives resist on metamorphosis at hyperspace!",
    ];
    if (array_key_exists($value, $nodes)) {
      $result = $nodes[$value];
      $nids = \Drupal::entityQuery('node')
        ->accessCheck()
        ->condition('title', $result)
        ->sort('nid', 'DESC')
        ->execute();
      $nid = reset($nids);
      return $nid;
    }
    return FALSE;
  }

}
