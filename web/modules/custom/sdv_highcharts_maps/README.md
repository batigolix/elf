# SDV Highcharts map

This module allows creating map using the [Highcharts](http://www.highcharts.com/) maps JS library.

## Paragraph
The module provides a paragraph type (Highcharts map) that can be attached to
various content types.

## Field
The module provides a field that is available in the paragrpah type Highcharts
map. The field consists of many sub fields that correspond with the options
provides by the [Highcharts maps API](https://api.highcharts.com/highmaps)

## Map types
Map types contain map MultiPolygon features in geojson format. Each feature
contains a key that must matched with the data in the dataset.
Available map types are:
1. Europe (by the European Commission).
1. Netherlands municipalities (by Highcharts).
1. Netherlands provinces (by Highcharts).
1. Netherlands GGD regions (by RIVM).
1. Netherlands municipalities (by RIVM).
1. Netherlands provinces (by RIVM).
Map types are hard coded (not configurable, yet) and can be found in
MapManager.php.

## Map type and dataset
To display data on the map, the data must be linked to the map using a key.
This key is hard coded in the map type definition. In the dataset field the
user must define in which column the key can be found.

See also
* [Highcharts maps](https://www.highcharts.com/products/maps/) [1]
* [Highcharts maps API documentation](https://api.highcharts.com/highmaps)
