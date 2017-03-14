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
    $all_programm = [];
    $vsego_programm = 0;
    $all_people = [];
    $vsego_people = 0;
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
      $node_usluga = $node->field_orders_ref_activity->entity;
      $termin = $node_usluga->field_activity_type->entity;
      $name_programm = $termin->name->value;
      $source = [
        'название заявки' => $node->title->value,
        'работники' => $team,
        'категории, регионы и кол-во посетителей' => $fc,
        'общее количество людей' => $node->field_orders_group->value,
        'фактическая стоимость услуги' => $node->field_orders_cost->value,
        'тип программы' => $node->field_orders_ref_activity->entity->field_activity_type->entity->name->value,
      ];
      // Считаем количество проведенных программ.
      $kolvo_programm = 1;
      if (!isset($all_programm[$name_programm])) {
        $all_programm[$name_programm] = [
          'name_programm' => $name_programm,
          'summa' => 0,
        ];
      }
      // 1. Работаем с участком массива $all_programm под названием $name_programm, например, Солнцеворот.
      // 2. Внутри этого участка берем значение из поля 'summa'.
      $current_summa = $all_programm[$name_programm]['summa'];
      // 3. Добавляем к нему 1 ($kolvo_programm).
      $new_summa = $current_summa + 1;
      // 4. Полученное значение кладем в 'summa'.
      $all_programm[$name_programm]['summa'] = $new_summa;
      // 5. Считаем сколько всего программ проведено (= сколько циклов прошло).
      $vsego_programm = $vsego_programm + $kolvo_programm;
      
      // Считаем посещаемость.
      $people = $source['общее количество людей'];
      $id = $node->id();
      $all_people[$id] = [
        'количество людей' => $source['общее количество людей'],
      ];
      $vsego_people = $vsego_people + $all_people[$id]['количество людей'];
    }

    $teams = $this->getTeam();
    $team = [];
    foreach ($teams as $key => $node_team) {
      $id = $node_team->id();
      $team[$id] = [
        'title' => $node_team->title->value,
      ];
    }
    $exhour = $this->getExhour();
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
      'vsego_chasov' => $vsego_chasov,
      'all_programm' => $all_programm,
      'vsego_programm' => $vsego_programm,
      'all_people' => $all_people,
      'vsego_people' => $vsego_people,
    ];

    $renderable['h'] = array(
      '#theme' => 'report-header',
      '#data' => $data,
    );
  //  dsm($renderable);
    return $renderable;
  }

  /**
   * Функциями getХххх() формируем из ноды объекты.
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
