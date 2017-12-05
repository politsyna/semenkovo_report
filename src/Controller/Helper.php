<?php

namespace Drupal\report\Controller;

/**
 * @file
 * Contains \Drupal\node_orders\Controller\Page.
 */
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Controller routines for page example routes.
 */
class Helper extends ControllerBase {

  /**
   * Делаем из поля "ссылка на команду" массив людей.
   */
  public static function getOrdersTeam($field_orders_team) {
    $team = [];
    foreach ($field_orders_team as $key => $man) {
      $node_team = $man->entity;
      $lastname = $node_team->field_team_name_last->value;
      $name = $node_team->field_team_name->value;
      $middlename = $node_team->field_team_name_middle->value;
      $team[] = [
        'Ф работника' => $lastname,
        'И работника' => $name,
        'О работника' => $middlename,
      ];
    }
    return $team;
  }

  /**
   * Функциями getХххх() формируем из ноды объекты.
   */
  public static function getOrders($start, $end, $usluga = FALSE) {
    $start = strtotime($start);
    $end = strtotime($end);
    if ($start > 0 && $end > 0 && $start < $end) {
      $start_norm = format_date($start, 'custom', "Y-m-d");
      $end_norm = format_date($end, 'custom', "Y-m-d");
      $query = \Drupal::entityQuery('node');
      $query->condition('status', 1);
      $query->condition('type', 'orders');
      $query->condition('field_orders_status', 'cancel', '<>');
      $query->condition('field_orders_date', $start_norm, '>');
      $query->condition('field_orders_date', $end_norm, '<');
      if (is_array($usluga)) {
        $query->condition('field_orders_ref_activity', $usluga, 'IN');
      }
      $entity_ids = $query->execute();
      $orders = Node::loadMultiple($entity_ids);
      return $orders;
    }
    else {
      return FALSE;
    }
  }

  /**
   * A getOgders.
   */
  public static function getExhour($nids = []) {
    if (!empty($nids)) {
      $query = \Drupal::entityQuery('node');
      $query->condition('status', 1);
      $query->condition('type', 'exhour');
      $query->condition('field_exhour_ref_orders', $nids, 'IN');
      $entity_ids = $query->execute();
      $exhour = Node::loadMultiple($entity_ids);
      return $exhour;
    }
    else {
      return FALSE;
    }
  }

  /**
   * A getOgders.
   */
  public static function getTeam() {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'team');
    $query->sort('title');
    $entity_ids = $query->execute();
    $teams = Node::loadMultiple($entity_ids);
    return $teams;
  }

  /**
   * A getOgders.
   */
  public static function getPayment($nids = []) {
    if (!empty($nids)) {
      $query = \Drupal::entityQuery('node');
      $query->condition('status', 1);
      $query->condition('type', 'payment');
      $query->condition('field_payment_ref_orders', $nids, 'IN');
      $entity_ids = $query->execute();
      $payments = Node::loadMultiple($entity_ids);
      return $payments;
    }
    else {
      return FALSE;
    }
  }

}
