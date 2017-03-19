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
    $fakt_cost = [];
    $summ_fakt_cost = 0;
    $visitors_kateg = [];
    $visitors_reg = [];
    $keytoname_1 = [
      'adult' => 'Взрослые',
      'student' => 'Студенты',
      'school' => 'Школьники',
      'baby' => 'Дошкольники',
      'old' => 'Пенсионеры',
      'military' => 'Военнослужащие',
      'museum' => 'Музейные',
      'lgotniki' => 'Льготники',
      'guest' => 'Гости',
      'none' => 'Неизвестно',
    ];
    $keytoname_2 = [
      'vologda' => 'Вологда',
      'volobl' => 'Вологодская область',
      'anrussia' => 'Другие регионы России',
      'ancountry' => 'Другие страны',
      'none' => 'Регион неизвестен',
    ];
    $summa_ludey = 0;
    foreach ($orders as $key => $node) {
      $fieldcollection = $node->field_orders_visitor;
      $fc = Group::collectionItems($fieldcollection);
      // Делаем из поля "ссылка на команду" массив людейж.
      $team = $this->getOrdersTeam($node->field_orders_team);
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

      // Считаем общий доход по всем программам: сумма всех "фактическая стоимость".
      $id = $node->id();
      $fakt_cost[$id] = [
        'начислено за все услуги' => $source['фактическая стоимость услуги'],
      ];
      $summ_fakt_cost = $summ_fakt_cost + $fakt_cost[$id]['начислено за все услуги'];
      // Считаем посещаемость.
      $people = $source['общее количество людей'];
      $all_people[$id] = [
        'количество людей' => $source['общее количество людей'],
      ];
      $vsego_people = $vsego_people + $all_people[$id]['количество людей'];

      // Определяем исходные данные для расчета кол-ва посетителей в каждой категории и из каких регионов.
      foreach ($fc as $k => $v) {
        $kategoria = $v['kategory'];
        $kolvo_po_kateg = $v['visitor'];
        $region = $v['region'];
        // Эти условия нужны на случай, если категория посетителя или его регион не определены.
        if (!array_key_exists($kategoria, $keytoname_1)) {
          $kategoria = "none";
        }
        $kategoria = $keytoname_1[$kategoria];
        if (!array_key_exists($region, $keytoname_2)) {
          $region = "none";
        }
        $region = $keytoname_2[$region];
        // Сколько посетителей в каждой категории.
        $visitors_kateg[$kategoria]['all'][] = [
          'kto' => $kategoria,
          'skolko' => $kolvo_po_kateg,
        ];
        if (!isset($visitors_kateg[$kategoria]['sum'])) {
          $visitors_kateg[$kategoria]['sum'] = 0;
          $visitors_kateg[$kategoria]['key'] = $kategoria;
        }
        $current_visitors_kateg = $visitors_kateg[$kategoria]['sum'] + $kolvo_po_kateg;
        $visitors_kateg[$kategoria]['sum'] = $current_visitors_kateg;

        // Определяем сколько посетителей из каких регионов.
        $visitors_reg[$region]['all'][] = [
          'skolko' => $kolvo_po_kateg,
          'otkuda' => $region,
        ];
        if (!isset($visitors_reg[$region]['sum'])) {
          $visitors_reg[$region]['sum'] = 0;
          $visitors_reg[$region]['key'] = $region;
        }
        $current_visitors_reg = $visitors_reg[$region]['sum'] + $kolvo_po_kateg;
        $visitors_reg[$region]['sum'] = $current_visitors_reg;
        // Сумма всех людей.
        $summa_ludey = $summa_ludey + $kolvo_po_kateg;
      }
    }

    // Считаем общий доход по всем программам: сумма всех "оплачено".
    $payments = $this->getPayment();
    $payment = [];
    $vsego_oplacheno = 0;
    foreach ($payments as $key => $node_payment) {
      $id = $node_payment->id();
      $payment[$id] = [
        'oplacheno' => $node_payment->field_payment_summa->value,
      ];
      $vsego_oplacheno = $vsego_oplacheno + $payment[$id]['oplacheno'];
    }
    // Считаем дебеторку: разницу между тем, что начислено за услуги и оплаченной суммой.
    $debet = $summ_fakt_cost - $vsego_oplacheno;

    // Получаем ФИО работника и отработанные им часы.
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
      // ВСЕ часы в сумме.
      $vsego_chasov = $vsego_chasov + $hours;
      /*
      $chas[] = [
        'часы работника' => $hours,
        'id' => $node_exhour->field_exhour_team->entity->id(),
      ]; */
    }

    // А вот и сам массив, данные из которого мы выводим на странице.
    $renderable = [];
    $renderable['info'] = [
      '#markup' => "Отчет с $start по $end",
    ];
    $data = [
      'reportmonth' => format_date(strtotime($end), 'custom', 'M Y'),
      'team' => $team,
      'vsego_chasov' => number_format($vsego_chasov, 0, ",", " "),
      'all_programm' => $all_programm,
      'vsego_programm' => $vsego_programm,
      'vsego_people' => number_format($vsego_people, 0, ",", " ") . " чел.",
      'summ_fakt_cost' => number_format($summ_fakt_cost, 0, ",", " ") . " руб.",
      'oplacheno' => number_format($vsego_oplacheno, 0, ",", " ") . " руб.",
      'debet' => number_format($debet, 0, ",", " ") . " руб.",
      'all_kategory' => $visitors_kateg,
      'all_region' => $visitors_reg,
    ];

    $renderable['h'] = array(
      '#theme' => 'report-header',
      '#data' => $data,
    );
  //  dsm($renderable);
    return $renderable;
  }

  /**
   * Делаем из поля "ссылка на команду" массив людей.
   */
  public function getOrdersTeam($field_orders_team) {
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

  /**
   * A getOgders.
   */
  public function getPayment() {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'payment');
    $entity_ids = $query->execute();
    $payments = Node::loadMultiple($entity_ids);
    return $payments;
  }

}
