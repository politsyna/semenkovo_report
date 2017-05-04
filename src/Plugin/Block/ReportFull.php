<?php

namespace Drupal\report\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Report: full' block.
 *
 * @Block(
 *   id = "report_full",
 *   admin_label = @Translation("Report: full")
 * )
 */
class ReportFull extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $date_from = \Drupal::request()->get('field_orders_date_value');
    $date_to = \Drupal::request()->get('field_orders_date_value_1');
    if ($date_from && $date_to) {
      return array(
        '#type' => 'markup',
        '#markup' => "Полный отчет с " . format_date(strtotime($date_from), 'custom', 'd-m-Y')
        . " по " . format_date(strtotime($date_to), 'custom', 'd-m-Y'),
      );
    }
    else {
      return array(
        '#type' => 'markup',
        '#markup' => "Все заявки",
      );
    }
  }

}
