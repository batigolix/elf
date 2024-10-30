<?php

namespace Drupal\sdv_highcharts_maps\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\sdv_highcharts_maps\MapManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'highcharts_map_widget' widget.
 *
 * @FieldWidget(
 *   id = "highcharts_map_widget",
 *   module = "sdv_highcharts_maps",
 *   label = @Translation("Highcharts map widget"),
 *   field_types = {
 *     "highcharts_map_item"
 *   }
 * )
 */
class HighchartsMapWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * The map manager.
   *
   * @var \Drupal\sdv_highcharts_maps\MapManager
   */
  protected $mapManger;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, MapManagerInterface $mapManager, SerializerInterface $serializer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
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
      'size' => 60,
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = $this->t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#title'] = $this->t('Name');
    $element['#description'] = $this->t('Name of the map. This will be used in the admin interface.');
    $element['#size'] = 48;
    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#maxlength' => $this->getFieldSetting('max_length'),
    ];
    $map_config = isset($items[$delta]->mapconfig_json) ? $this->serializer->decode($items[$delta]->mapconfig_json, 'json') : [];
    $element['mapconfig'] = [
      '#type' => 'container',
      '#title' => $this->t('Map configuration'),
      '#element_validate' => [
        [static::class, 'validate'],
      ],
      'title' => [
        '#type' => 'textfield',
        '#title' => $this->t('Map title'),
        '#default_value' => $map_config['title'] ?? '',
        '#size' => 48,
        '#description' => $this->t('Map title will be printed in the top of the map.'),
        '#maxlength' => 128,
      ],
      'subtitle' => [
        '#type' => 'textfield',
        '#title' => $this->t('Map subtitle'),
        '#default_value' => $map_config['subtitle'] ?? '',
        '#size' => 48,
        '#description' => $this->t('Map subtitle will be printed in the top of the map under the title.'),
        '#maxlength' => 256,
      ],
      'tooltip' => [
        '#type' => 'container',
        'enabled' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Show tooltip'),
          '#description' => $this->t('Enable or disable the tooltip.'),
          '#default_value' => $map_config['tooltip']['enabled'] ?? TRUE,
        ],
      ],
      'chart' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Map'),
        'map' => [
          '#type' => 'select',
          '#title' => $this->t('Type'),
          '#options' => $this->mapManager->getMapOptions(),
          '#default_value' => $map_config['chart']['map'] ?? 'hc-nl-provinces',
          '#description' => $this->t('The type of map to present data. This must be mapped with the data set using a key.'),
          '#ajax' => [
            'callback' => [$this, 'handleAjaxCommand'],
          ],
        ],
        'height' => [
          '#type' => 'number',
          '#title' => $this->t('Height'),
          '#description' => $this->t("Height of the map. Leave empty for automatic height."),
          '#size' => 3,
          '#default_value' => $map_config['chart']['height'] ?? NULL,
        ],
      ],
      'series' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Series'),
        'data' => [
          '#type' => 'textarea',
          '#rows' => 4,
          '#cols' => 5,
          '#title' => $this->t('Dataset'),
          '#description' => $this->t("Dataset in CSV format. Use comma's to separate columns or change the delimiter below. Use column 1 for the key and column 2 for the value. If necessary the order can be changed using the key and value column fields below."),
          '#default_value' => $map_config['series']['data'] ?? [],
          '#suffix' => '<div id="example-dataset"></div>',

        ],
        'delimiter' => [
          '#type' => 'textfield',
          '#title' => $this->t('Delimiter'),
          '#default_value' => $map_config['series']['delimiter'] ?? ',',
          '#size' => 3,
          '#description' => $this->t("Delimiter of the CSV columns. Default value is ',' (comma). For tabs enter '\\t'"),
          '#maxlength' => 8,
        ],
        'key_column' => [
          '#type' => 'number',
          '#title' => $this->t('Key column'),
          '#description' => $this->t("Column in the dataset that holds the key that will be used to map the data with the geojson definition."),
          '#default_value' => $map_config['series']['key_column'] ?? 1,
          '#min' => 1,
          '#size' => 3,
        ],
        'value_column' => [
          '#type' => 'number',
          '#title' => $this->t('Value column'),
          '#description' => $this->t("Column in the dataset that holds the values that will be shown in the map."),
          '#min' => 1,
          '#size' => 3,
          '#default_value' => $map_config['series']['value_column'] ?? 2,
        ],
        'name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Name'),
          '#default_value' => $map_config['series']['name'] ?? '',
          '#size' => 48,
          '#description' => $this->t('The name of the series as shown in the legend, tooltip etc. If this is emtpy a default value will be shown.'),
          '#maxlength' => 256,
        ],
        'dataLabels' => [
          '#type' => 'container',
          'enabled' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Show region labels'),
            '#description' => $this->t('Show labels for regions on the map.'),
            '#default_value' => $map_config['series']['dataLabels']['enabled'] ?? TRUE,
          ],
        ],
        'color' => [
          '#type' => 'color',
          '#title' => $this->t('Hover color'),
          '#default_value' => $map_config['series']['color'] ?? '#BADA55',
        ],
      ],
      'mapNavigation' => [
        '#type' => 'details',
        '#open' => FALSE,
        '#title' => $this->t('Map navigation'),
        'enabled' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#description' => $this->t('Whether to enable map navigation.'),
          '#default_value' => $map_config['mapNavigation']['enabled'] ?? TRUE,
        ],
        'buttonOptions' => [
          '#type' => 'container',
          'verticalAlign' => [
            '#type' => 'select',
            '#options' => [
              'top' => $this->t('Top'),
              'middle' => $this->t('Middle'),
              'bottom' => $this->t('Bottom'),
            ],
            '#title' => $this->t('Vertical align'),
            '#default_value' => $map_config['mapNavigation']['buttonOptions']['verticalAlign'] ?? 'bottom',
            '#description' => $this->t('The vertical alignment of navigation buttons.'),
          ],

        ],
      ],
      'colorAxis' => [
        '#type' => 'details',
        '#open' => FALSE,
        '#title' => $this->t('Color axis'),
        'minColor' => [
          '#type' => 'color',
          '#title' => $this->t('Minimum color'),
          '#description' => $this->t('The color to represent the minimum of the color axis'),
          '#default_value' => $map_config['colorAxis']['minColor'] ?? '#e6ebf5',
        ],
        'maxColor' => [
          '#type' => 'color',
          '#title' => $this->t('Maximum color'),
          '#description' => $this->t('The color to represent the maximum of the color axis'),
          '#default_value' => $map_config['colorAxis']['maxColor'] ?? '#003399',
        ],
        'min' => [
          '#type' => 'number',
          '#title' => $this->t('Minimum'),
          '#description' => $this->t("The minimum value of the axis"),
          '#size' => 3,
          '#default_value' => $map_config['colorAxis']['min'] ?? NULL,
        ],
        'max' => [
          '#type' => 'number',
          '#title' => $this->t('Maximum'),
          '#description' => $this->t("The maximum value of the axis"),
          '#size' => 3,
          '#default_value' => $map_config['colorAxis']['max'] ?? NULL,
        ],
      ],
      'legend' => [
        '#type' => 'details',
        '#title' => $this->t('Legend'),
        '#open' => FALSE,
        'enabled' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#description' => $this->t('Enable or disable the legend.'),
          '#default_value' => $map_config['legend']['enabled'] ?? TRUE,
        ],
        'title' => [
          '#type' => 'container',
          'text' => [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
            '#default_value' => $map_config['legend']['title']['text'] ?? NULL,
            '#size' => 48,
            '#description' => $this->t('Title shown above the legend.'),
            '#maxlength' => 256,
          ],
        ],
        'ranges' => [
          '#type' => 'textfield',
          '#size' => 64,
          '#title' => $this->t('Ranges'),
          '#description' => $this->t("Ranges to show in the map legend. Separate by comma. E.g.'100, 100-200, 200-300, 300'"),
          '#default_value' => $map_config['legend']['ranges'] ?? NULL,
        ],
        'align' => [
          '#type' => 'select',
          '#options' => [
            'left' => $this->t('Left'),
            'right' => $this->t('Right'),
            'center' => $this->t('Center'),
          ],
          '#title' => $this->t('Horizontal alignment'),
          '#default_value' => $map_config['legend']['align'] ?? 'center',
          '#description' => $this->t('The horizontal alignment of the legend box within the chart area.'),
        ],
        'verticalAlign' => [
          '#type' => 'select',
          '#options' => [
            'middle' => $this->t('Middle'),
            'bottom' => $this->t('Bottom'),
          ],
          '#title' => $this->t('Vertical align'),
          '#default_value' => $map_config['legend']['verticalAlign'] ?? 'bottom',
          '#description' => $this->t('The vertical alignment of the legend box.'),
        ],
        'layout' => [
          '#type' => 'select',
          '#options' => [
            'horizontal' => $this->t('Horizontal'),
            'vertical' => $this->t('Vertical'),
          ],
          '#title' => $this->t('Layout'),
          '#default_value' => $map_config['legend']['layout'] ?? 'bottom',
          '#description' => $this->t('The layout of the legend items.'),
        ],
      ],
      'credits' => [
        '#type' => 'details',
        '#title' => $this->t('Credits'),
        '#open' => FALSE,
        'enabled' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#description' => $this->t('Whether to show credits.'),
          '#default_value' => $map_config['credits']['enabled'] ?? TRUE,
        ],
        'text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Text'),
          '#default_value' => $map_config['credits']['text'] ?? 'Copyright (c) 2020 Highsoft AS',
          '#size' => 48,
          '#description' => $this->t('Credits text.'),
          '#maxlength' => 256,
        ],
        'href' => [
          '#type' => 'url',
          '#title' => $this->t('The URL for the credits label'),
          '#size' => 48,
          '#default_value' => $map_config['credits']['href'] ?? 'http://highcharts.com',
        ],
        'position' => [
          '#type' => 'container',
          'align' => [
            '#type' => 'select',
            '#options' => [
              'left' => $this->t('Left'),
              'right' => $this->t('Right'),
            ],
            '#title' => $this->t('Align'),
            '#default_value' => $map_config['credits']['position']['align'] ?? 'right',
          ],
        ],
      ],
    ];
    // @todo form alter with checkobox to show hide json config
    $element['mapconfig_json'] = [
      '#type' => 'textarea',
      '#rows' => 4,
      '#cols' => 15,
      '#title' => $this->t('Map configuration'),
      '#description' => $this->t('Map configuration in JSON format'),
      '#default_value' => $items[$delta]->mapconfig_json ?? NULL,
    ];
    return $element;
  }

  /**
   * Tinkers with form element values before storing.
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => $value) {

      // Removes empty number values from config so that the javascript
      // does not get 0 as value.
      $this->removeEmptyConfig($value['mapconfig']);

      // Converts values to json before storing.
      $map_config = $this->serializer->encode($value['mapconfig'], 'json');
      $values[$key]['mapconfig_json'] = $map_config;
    }
    return $values;
  }

  /**
   * Removes empty values from config.
   *
   * Prevents sending 0 values to javascript.
   *
   * @todo fix and clean up.
   */
  private function removeEmptyConfig(&$array) {
    foreach ($array as $key => &$value) {
      if (is_string($value) && empty($value)) {
        // unset($array[$key]);.
      }
      if (is_array($value)) {
        $this->removeEmptyConfig($value);
      }
    }
  }

  /**
   * Validate the color text field.
   */
  public static function validate($element, FormStateInterface $form_state) {
    if ($element['series']['key_column']['#value'] === $element['series']['value_column']['#value']) {
      $form_state->setError($element['series']['key_column'], "Key and value column cannot be the same");
    }
  }

  /**
   * Ajax callback for the example csv download text.
   */
  public function handleAjaxCommand(array $form, FormStateInterface $form_state) {

    // Gets all map types.
    $map_manager = \Drupal::service('sdv_highcharts_maps.manager');
    $map_types = $map_manager->getMapTypes();

    // Gets the map type field's value.
    $triggering_element = $form_state->getTriggeringElement();
    $map = $triggering_element['#value'];
    $file_name = $map_types[$map]['example_dataset'];
    $map_type_name = $map_types[$map]['label'];

    // Constructs and returns the example csv download text.
    $response = new AjaxResponse();
    $url = Url::fromRoute('sdv_highcharts_maps.download.example_dataset', ['file_name' => $file_name])->toString();
    $value = $this->t('Download an <a href=":url">example dataset</a> for the <strong>:map</strong> map type.', [
      ':url' => $url,
      ':map' => $map_type_name,
    ]);
    $response->addCommand(new HtmlCommand('#example-dataset', $value));
    return $response;
  }

}
