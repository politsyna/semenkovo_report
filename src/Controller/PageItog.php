<?php

namespace Drupal\report\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\node_orders\Controller\Group;

/**
 * Controller routines for page example routes.
 */
class PageItog extends ControllerBase {

  /**
   * A more complex _controller callback that takes arguments.
   */
  public function report($start, $end) {
    $orders = $this->getOrders();
    $source = [];
    foreach ($orders as $key => $node) {
      $fieldcollection = $node->field_orders_visitor;
      $fc = Group::collectionItems($fieldcollection);
      $team = [];
      foreach ($node->field_orders_team as $key => $man) {
        $node_team = $man;
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
      //dsm($team);
      $source[] = [
        'название заявки' => $node->title->value,
        'работники' => $team,
        'категории, регионы и кол-во посетителей' => $fc,
        'общее количество людей' => $node->field_orders_group->value,
        'фактическая стоимость услуги' => $node->field_orders_cost->value,
      ];
    }
    //dsm($source);

    $teams = $this->getTeam();
    $team = [];
    foreach ($teams as $key => $node_team) {
      $id = $node_team->id();
      $team[$id] = [
        'title' => $node_team->title->value,
      ];
    }
    $exhour = $this->getExhour();
    // chas = [];
    $vsego_chasov = 0;
    foreach ($exhour as $key => $node_exhour) {
      $tid = $node_exhour->field_exhour_team->entity->id();
      $hours = $node_exhour->field_exhour_hours->value;
      $team[$tid]['chas'][] = $hours;
      if (!isset($team[$tid]['chas_itogo'])) {
        $team[$tid]['chas_itogo'] = 0;
      }
      $team[$tid]['chas_itogo'] = $team[$tid]['chas_itogo'] + $hours;
      $vsego_chasov = $vsego_chasov + $hours;
      /*
      $chas[] = [
        'часы работника' => $hours,
        'id' => $node_exhour->field_exhour_team->entity->id(),
      ]; */
    }
    $renderable = [];
    $renderable['info'] = [
      '#markup' => "Отчет с $start по $end",
    ];
    $data = [
      'reportmonth' => format_date(strtotime($end), 'custom', 'M Y'),
      'team' => $team,
      'vsego' => $vsego_chasov,
    ];

    $renderable['h'] = array(
      '#theme' => 'report-header',
      '#data' => $data,
    );
  //  dsm($renderable);
    return $renderable;
  }

  /**
   * A getOgders.
   */
  public function getOrders() {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'orders');
    $entity_ids = $query->execute();
    $orders = Node::loadMultiple($entity_ids);
    return $orders;
  }

  /**
   * A getOgders.
   */
  public function getExhour() {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'exhour');
    $entity_ids = $query->execute();
    $exhour = Node::loadMultiple($entity_ids);
    return $exhour;
  }

  /**
   * A getOgders.
   */
  public function getTeam() {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'team');
    $entity_ids = $query->execute();
    $teams = Node::loadMultiple($entity_ids);
    return $teams;
  }

}
