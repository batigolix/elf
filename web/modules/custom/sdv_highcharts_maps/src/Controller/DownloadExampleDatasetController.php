<?php

namespace Drupal\sdv_highcharts_maps\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Download an example dataset.
 */
class DownloadExampleDatasetController extends ControllerBase {

  /**
   * Drupal\Core\Logger\LoggerChannelInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannelDefault;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->loggerChannelDefault = $container->get('logger.channel.default');
    $instance->moduleHandler = $container->get('module_handler');
    $instance->fileSystem = $container->get('file_system');
    return $instance;
  }

  /**
   * Provides downloadable file.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   CSV download
   */
  public function build($file_name) {
    $module_path = $this->moduleHandler->getModule('sdv_highcharts_maps')->getPath();
    $full_path = $this->fileSystem->realpath($module_path);
    $uri = "$full_path/js/map-types/$file_name";
    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Description' => 'File Download',
      'Content-Disposition' => 'attachment; filename=' . $file_name,
    ];
    return new BinaryFileResponse($uri, 200, $headers, TRUE);
  }

}
