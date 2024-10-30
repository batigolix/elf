<?php

declare(strict_types = 1);

namespace Drupal\file_content\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Fetches the content from a text file.
 *
 * Example usage:
 * @code
 * file_content:
 *   source: /tmp/myfile.txt
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "file_content"
 * )
 */
class FileContent extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!file_exists($value)) {
      throw new \RuntimeException(sprintf('File "%s" was not found.', $value));
    }
    $csv = fopen($value, 'r');
    if (!$csv) {
      throw new \RuntimeException(sprintf('File "%s" could not be opened.', $value));
    }
    $file_content= file_get_contents($value);
    return $file_content;
  }

}
