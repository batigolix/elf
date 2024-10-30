<?php

namespace Drupal\sdv_highcharts_maps;

/**
 * Interface for MapManager.
 */
interface MapManagerInterface {

  /**
   * Provides list of map types.
   *
   * This can be for examples provinces, europe, etc.
   *
   * @return array
   *   List of map types containing key, name, description, library.
   */
  public function getMapTypes();

  /**
   * Provides list of map that can be used as select list options.
   *
   * @return array
   *   List of map options consisting of key and label.
   */
  public function getMapOptions();

  /**
   * Provides the map config.
   *
   * @param object $item
   *   One field item.
   *
   * @return string
   *   JSON containing map config.
   */
  public function getMapConfig($item);

  /**
   * Prepares the dataset for highchart map.
   *
   * @param string $series
   *   String containing CSV data.
   *
   * @return array
   *   Array containing map data in key value format.
   */
  public function getMapData($series);

  /**
   * Prepares the color axis dataclasses for map's legend.
   *
   * @param string $data
   *   Multiline string from textarea field.
   *
   * @return array
   *   Exploded lines.
   */
  public function getColorAxisDataClasses($data);

}
