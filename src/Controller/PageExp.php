<?php

namespace Drupal\report\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class PageExp extends ControllerBase {

  /**
   * A more complex _controller callback that takes arguments.
   */
  public function report($start, $end) {
    $render_array['page_example_arguments'] = array(
      '#markup' => "Отчет с $start по $end",
    );
    return $render_array;
  }

}
