<?php

namespace Drupal\dumdum\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for dumdum routes.
 */
class DumdumController extends ControllerBase {

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
