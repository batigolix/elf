<?php

namespace Drupal\sdv_highcharts_maps\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\sdv_highcharts_maps\MapManagerInterface;

/**
 * Plugin implementation of the 'highcharts_map_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "highcharts_map_formatter",
 *   label = @Translation("Highcharts map formatter"),
 *   field_types = {
 *     "highcharts_map_item"
 *   }
 * )
 */
class HighchartsMapFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\sdv_highcharts_maps\MapManagerInterface
   */
  protected $mapManager;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * Constructs a new Maps Formatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\sdv_highcharts_maps\MapManagerInterface $mapManager
   *   The date formatter service.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, MapManagerInterface $mapManager, SerializerInterface $serializer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->mapManager = $mapManager;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('sdv_highcharts_maps.manager'),
      $container->get('serializer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        // Implement default settings.
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
        // Implement settings form.
      ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $elements['#attached']['library'][] = 'sdv_highcharts_maps/sdv_highcharts_maps';
    foreach ($items as $delta => $item) {
      $elements[$delta]['map'] = [
        '#theme' => 'map',
      ];

      // Provides map configuration for Highcharts. Majority of settings can be
      // sent straight to Javascript without need for tinkering.
      $elements[$delta]['#attached']['drupalSettings']['highcharts_maps']['config'] = $this->mapManager->getMapConfig($item);

      // Provides values of mapconfig field in array.
      $mapconfig = $this->serializer->decode($item->mapconfig_json, 'json');

      // Provides dataset for map. This needs to be converted from CSV into an
      // array that is readable by Highcharts.
      $elements[$delta]['#attached']['drupalSettings']['highcharts_maps']['dataset'] = $this->mapManager->getMapData($mapconfig['series']);

      // Provides ranges for map legend. This needs to be changed from string
      // to an array and will be further processed in map.js.
      if (!empty($mapconfig['legend']['ranges'])) {
        $elements[$delta]['#attached']['drupalSettings']['highcharts_maps']['ranges'] = $this->mapManager->getColorAxisDataClasses($mapconfig['legend']['ranges']);
      }

      // Provides map type definition. This contains some information that is
      // not available in the 'config' property that is defined before. Also
      // attaches the datamap library containing the geojson map features.
      $map_types = $this->mapManager->getMapTypes();
      $map_type = $map_types[$mapconfig['chart']['map']];
      $elements[$delta]['#attached']['drupalSettings']['highcharts_maps']['mapType'] = $map_type;
      $elements[$delta]['#attached']['library'][] = $map_type['library'];

    }
    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return nl2br(Html::escape($item->value));
  }

}
