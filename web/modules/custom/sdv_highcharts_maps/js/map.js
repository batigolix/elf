/**
 * Provides the highcharts map activation script.
 *
 * See documentation at https://api.highcharts.com/highmaps/
 */

(function ($, Drupal) {
  Drupal.behaviors.highcharts_maps = {
    attach: function attach(context) {

      console.log(drupalSettings.highcharts_maps);


      // Defines configuration variable that contains most of the
      // map settings.
      var config = JSON.parse(drupalSettings.highcharts_maps.config);

      // Defines dataset variable that contains the CSV data
      // for the map.
      var dataset = drupalSettings.highcharts_maps.dataset;




      // Defines map variable that contains some settings not
      // available in config variable.
      var mapType = drupalSettings.highcharts_maps.mapType;

      var mapConfig = {
        chart: {
          map: config.chart.map
        },
        title: {
          text: config.title // @todo variable
        },
        subtitle: {
          text: config.subtitle // @todo variable
        },
        colorAxis: {
          min: 0
        },
        series: [{
          data: dataset,
          joinBy: [mapType.join, 'hc-key'],
          name: config.series.name,
          states: {
            hover: {
              color: config.series.color
            }
          },
          tooltip: {
            pointFormatter: function () {
              return this.properties[mapType.name] + ': ' + this.value;
            }
          },
          dataLabels: {
            enabled: config.series.dataLabels.enabled,
            formatter: function () {
              if (typeof this.point.properties !== 'undefined' && typeof this.point.properties[mapType.name] !== 'undefined') {
                return this.point.properties[mapType.name];
              }
            }
          },
        }],
      };

      // Sets height of map.
      if(config.chart.height.length > 0){
        mapConfig.chart.height = config.chart.height;
      }

      // Adds map navigation.
      if(config.mapNavigation.enabled) {
        mapConfig.mapNavigation = config.mapNavigation
      }

      // Adds legend.
      if(config.legend.enabled){
        mapConfig.legend = config.legend;
      }
      else {
        mapConfig.legend = { enabled: false };
      }

      // Adds tooltip.
      if(config.tooltip.enabled == 0 ){
        mapConfig.tooltip = { enabled: false };
      }

      // Adds credits.
      if(config.credits.enabled){
        mapConfig.credits = config.credits;
      }
      else {
        mapConfig.credits = false;
      }

      // Sets colorAxis
      mapConfig.colorAxis = config.colorAxis;
      if(config.colorAxis.min.length === 0){
        mapConfig.colorAxis.min = 0;
      }
      if(config.colorAxis.max.length === 0){
        delete mapConfig.colorAxis.max;
      }

      // Converts list of legend items provided by Drupal to highcharts
      // legend items.
      if (typeof drupalSettings.highcharts_maps.ranges !== 'undefined') {
        var ranges = drupalSettings.highcharts_maps.ranges;
        var dataClasses = [];
        var last = ranges.length - 1;
        Highcharts.each(ranges, function (item, i) {
          var items = {};
          if (i === 0) {
            items.to = parseFloat(item[0]);
          }
          else if (i === last) {
            items.from = parseFloat(item[0]);
          }
          else {
            items.from = parseFloat(item[0]);
            items.to = parseFloat(item[1]);
          }
          dataClasses.push(items);
        });
        mapConfig.colorAxis.dataClasses = dataClasses;
      }

      // Creates the map.
      Highcharts.mapChart('map_container', mapConfig);
    }
  };
})(jQuery, Drupal);
