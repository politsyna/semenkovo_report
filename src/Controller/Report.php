<?php

namespace Drupal\report\Controller;

/**
 * @file
 * Contains \Drupal\node_orders\Controller\Page.
 */
use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page example routes.
 */
class Report extends ControllerBase {

  /**
   * Page Callback.
   */
  public static function report() {
    $start = strtotime("01-01-2017");
    $end = time();
    $output = '<ul>';
    for ($i = $start; $i <= $end;) {
      $month = format_date($i, 'custom', '01-m-Y');
      $i = strtotime($month);
      $j = $i - 20 * 24 * 3600;
      $month_last = format_date($j, 'custom', '26-m-Y');
      $month_next = format_date($i, 'custom', '25-m-Y');
      $output .= '<li class="list-item">';
      $output .= '<a href="/report/itog/' . $month_last . '/' . $month_next . '">';
      $output .= format_date($i, 'custom', 'Общий отчет за M Y');
      $output .= '</a><br>';
      $output .= '<a href="/report/exp/' . $month_last . '/' . $month_next . '">';
      $output .= format_date($i, 'custom', 'Экскурсионный отчет за M Y');
      $output .= '</a><br>';
      $output .= '<a href="/report/full/' . $month_last . '/' . $month_next . '">';
      $output .= format_date($i, 'custom', 'Полный отчет за M Y');
      $output .= '</a>';
    // $output .= ' ' . $month_last . '-- ' . $month_next;
      $output .= '</li>';
      $i = $i + 31 * 24 * 60 * 60;
    }
    $output .= '</ul>';
    return [
      'form' => \Drupal::formBuilder()->getForm('Drupal\report\Form\DateChoice'),
      // 'links' => ['#markup' => $output],
    ];
  }

}
