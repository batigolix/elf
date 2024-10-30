<?php

namespace Drupal\sdv_highcharts_maps;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Highcharts map service.
 */
class MapManager implements MapManagerInterface {

  use StringTranslationTrait;

  /**
   * Drupal\Core\Config\ConfigManagerInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new MapManager object.
   */
  public function __construct(ConfigManagerInterface $config_manager, ConfigFactoryInterface $config_factory, TranslationInterface $string_translation) {
    $this->configManager = $config_manager;
    $this->configFactory = $config_factory;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function getMapTypes() {
    return [
      'ec-europe' => [
        'label' => $this->t('Europe (European Commission)'),
        'library' => 'sdv_highcharts_maps/ec-europe',
        'join' => 'iso-a2',
        'name' => 'name',
        'example_dataset' => 'ec-europe.example.csv',
      ],
      'hc-nl-municipalities' => [
        'label' => $this->t('NL municipalities (Highcharts)'),
        'library' => 'sdv_highcharts_maps/hc-nl-municipalities',
        'join' => 'hc-key',
        'name' => 'name',
        'example_dataset' => 'hc-nl-municipalities.example.csv',
      ],
      'hc-nl-provinces' => [
        'label' => $this->t('NL provinces (by Highcharts)'),
        'library' => 'sdv_highcharts_maps/hc-nl-provinces',
        'join' => 'hc-key',
        'name' => 'name',
        'example_dataset' => 'hc-nl-provinces.example.csv',
      ],
      'hc-world' => [
        'label' => $this->t('World (by Highcharts)'),
        'library' => 'sdv_highcharts_maps/hc-world',
        'join' => 'hc-key',
        'name' => 'name',
        'example_dataset' => 'hc-world.example.csv',
      ],
      'rivm-nl-ggd-regions' => [
        'label' => $this->t('NL GGD regions (RIVM)'),
        'library' => 'sdv_highcharts_maps/rivm-nl-ggd-regions',
        'join' => 'GGDnr',
        'name' => 'GGDnaam',
        'example_dataset' => 'rivm-nl-ggd-regions.example.csv',
      ],
      'rivm-nl-municipalities' => [
        'label' => $this->t('NL municipalities (RIVM)'),
        'library' => 'sdv_highcharts_maps/rivm-nl-municipalities',
        'join' => 'gemnr',
        'name' => 'gemnaam',
        'example_dataset' => 'rivm-nl-municipalities.example.csv',
      ],

      // @todo find geojson that contains region names.
      'rivm-nl-provinces' => [
        'label' => $this->t('NL provinces (RIVM)'),
        'library' => 'sdv_highcharts_maps/rivm-nl-provinces',
        'join' => 'PROVNR',
        'name' => 'PROVNR',
        'example_dataset' => 'rivm-nl-provinces.example.csv',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMapOptions() {
    $map_options = [];
    foreach ($this->getMapTypes() as $key => $map) {
      $map_options[$key] = $map['label'];
    }
    return $map_options;
  }

  /**
   * {@inheritdoc}
   */
  public function getMapConfig($item) {

    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return $item->mapconfig_json;
  }

  /**
   * {@inheritdoc}
   */
  public function getMapData($series) {
    $lines = explode(PHP_EOL, $series['data']);

    // Sets key and value columns and substracts 1 to match php array.
    $key_column = $series['key_column'] - 1;
    $value_column = $series['value_column'] - 1;

    // Sets the tab delimiter.
    $delimiter = $series['delimiter'] == '\t' ? "\t" : $series['delimiter'];

    $dataset = [];
    foreach ($lines as $line) {
      $values = str_getcsv($line, $delimiter);

      // Assigns keys and values in the right order to the dataset so that
      // it is ready to be used by highcharts. User can switch key and value
      // column of the csv in the form.
      foreach ($values as $key => $value) {

        // If the key corresponds with the key column cast value as string.
        if ($key == $key_column) {
          $values[0] = $value;
        }

        // If the key corresponds with the value column cast value as integer.
        if ($key == $value_column) {
          $values[1] = (float) $value;
        }
      }

      // Removes redundant columns.
      $values = array_slice($values, 0, 2);
      $dataset[] = $values;
    }
    return $dataset;
  }

  /**
   * {@inheritdoc}
   */
  public function getColorAxisDataClasses($ranges) {
    $items = [];
    $lines = explode(',', $ranges);
    foreach ($lines as $line) {
      $items[] = explode('-', str_replace("\r", '', $line));
    }
    return $items;
  }

}
