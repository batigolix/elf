<?php

namespace Drupal\file_content\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for file content routes.
 */
class FileContentController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
