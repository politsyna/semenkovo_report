<?php

namespace Drupal\report\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node_orders\Controller\Group;
use Drupal\user\Entity\User;

/**
 * Controller routines for page example routes.
 */
class PageItog extends ControllerBase {

  /**
   * A more complex _controller callback that takes arguments.
   */
  public function report($start, $end) {
    $st = strtotime($start);
    $en = strtotime($end);
    if ($st == 0 || $en == 0 || $st > $en) {
      return [
        '#markup' => 'Введите корректные даты отчетного периода.',
      ];
    }
    $orders = Helper::getOrders($start, $end);
    $source = [];
    $all_programm = [];
    $vsego_programm = 0;
    $all_people = [];
    $kolvo_ekskurs = 0;
    $kolvo_meropr = 0;
    $kolvo_arenda = 0;
    $kolvo_ekskurs_long = 0;
    $kolvo_meropr_long = 0;
    $kolvo_arenda_long = 0;
    $vsego_people = 0;
    $vsego_people_long = 0;
    $fakt_cost = [];
    $summ_fakt_cost = 0;
    $vsego_peopl_vhod = 0;
    $people_free_vhod_mr = 0;
    $people_free_vhod_lg = 0;
    $people_free_vhod_gst = 0;
    $people_free_vhod = 0;
    $summ_cost_vhod = 0;
    $kolvo_people_ekskurs = 0;
    $kolvo_people_meropr = 0;
    $kolvo_people_arenda = 0;
    $kolvo_people_ekskurs_long = 0;
    $kolvo_people_meropr_long = 0;
    $kolvo_people_arenda_long = 0;
    $visitors_kateg = [];
    $visitors_reg = [];
    $visitors_vol = [];
    $visitors_volobl = [];
    $keytoname_1 = [
      'adult' => 'Взрослые',
      'student' => 'Студенты',
      'school' => 'Школьники',
      'baby' => 'Дошкольники',
      'old' => 'Пенсионеры',
      'military' => 'Военнослужащие',
      'museum' => 'Музейные работники',
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
    // Получаем все термины таксономии (типы услуг - 11 штук).
    $vid = 'type_activity';
    $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree($vid);
    $all_programm = [];
    // Делаем из списка всех терминов массив с нулями (Программа "ХХХ" - 0 шт.).
    foreach ($terms as $term) {
      $all_programm[$term->name] = [
        'name_programm' => $term->name,
        'summa' => 0,
      ];
    }
    // Самый главный foreach - он здесь делает почти все.
    foreach ($orders as $key => $node) {
      $fieldcollection = $node->field_orders_visitor;
      $fc = Group::collectionItems($fieldcollection);
      // Делаем из поля "ссылка на команду" массив людей.
      $team = Helper::getOrdersTeam($node->field_orders_team);
      $node_usluga = $node->field_orders_ref_activity->entity;
      $termin = $node_usluga->field_activity_type->entity;
      $activity_long = $node_usluga->field_activity_long_time->value;
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
      // 1. Работаем с участком массива $all_programm под названием $name_programm, например, Солнцеворот.
      // 2. Внутри этого участка берем значение из поля 'summa'.
      $current_summa = $all_programm[$name_programm]['summa'];
      // 3. Добавляем к нему 1 ($kolvo_programm).
      $new_summa = $current_summa + 1;
      // 4. Полученное значение кладем в 'summa'.
      $all_programm[$name_programm]['summa'] = $new_summa;
      // 5. Считаем сколько всего программ проведено (= сколько циклов прошло).
      $vsego_programm = $vsego_programm + $kolvo_programm;

      // Определяем общее количество экскурсий в штуках и в часах.
      if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
      $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
      $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
      $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Театральное представление') {
        $kolvo_ekskurs = $kolvo_ekskurs + $kolvo_programm;
        $kolvo_ekskurs_long = $kolvo_ekskurs_long + $kolvo_programm * $activity_long;
      }
      // Определяем общее количество массовых мероприятий в штуках и в часах.
      if ($name_programm == 'Массовое мероприятие') {
        $kolvo_meropr = $kolvo_meropr + $kolvo_programm;
        $kolvo_meropr_long = $kolvo_meropr_long + $kolvo_programm * $activity_long;
      }
      // Определяем общее количество аренды в штуках и в часах.
      if ($name_programm == 'Аренда') {
        $kolvo_arenda = $kolvo_arenda + $kolvo_programm;
        $kolvo_arenda_long = $kolvo_arenda_long + $kolvo_programm * $activity_long;
      }

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
      $vsego_people_long = $vsego_people_long + $all_people[$id]['количество людей'] * $activity_long;

      if (!isset($programm_vhod[$name_programm])) {
        $programm_vhod[$name_programm] = [
          'name_programm_vhod' => $name_programm,
          'summa_vhod' => $node->field_orders_group->value,
          'cost_vhod' => $node->field_orders_cost->value,
        ];
        if ($name_programm == "Входной билет") {
          $vsego_peopl_vhod = $vsego_peopl_vhod + $programm_vhod[$name_programm]['summa_vhod'];
          $summ_cost_vhod = $summ_cost_vhod + $programm_vhod[$name_programm]['cost_vhod'];
        }
      }

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

        // Сколько льготников пришло на услугу "Входной билет".
        if ($name_programm == 'Входной билет') {
          if ($kategoria == 'Музейные работники') {
            $people_free_vhod_mr = $people_free_vhod_mr + $kolvo_po_kateg;
          }
          if ($kategoria == 'Льготники') {
            $people_free_vhod_lg = $people_free_vhod_lg + $kolvo_po_kateg;
          }
          if ($kategoria == 'Гости') {
            $people_free_vhod_gst = $people_free_vhod_gst + $kolvo_po_kateg;
          }
          $people_free_vhod = $people_free_vhod_mr + $people_free_vhod_lg + $people_free_vhod_gst;
        }
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

        // Определяем общее количество экскурсантов.
        if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
        $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
        $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
        $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Театральное представление') {
          $kolvo_people_ekskurs = $kolvo_people_ekskurs + $kolvo_po_kateg;
          $kolvo_people_ekskurs_long = $kolvo_people_ekskurs_long + $kolvo_po_kateg * $activity_long;
        }
        // Определяем общее количество участников массовых мероприятий.
        if ($name_programm == 'Массовое мероприятие') {
          $kolvo_people_meropr = $kolvo_people_meropr + $kolvo_po_kateg;
          $kolvo_people_meropr_long = $kolvo_people_meropr_long + $kolvo_po_kateg * $activity_long;
        }
        // Определяем общее количество участников аренды.
        if ($name_programm == 'Аренда') {
          $kolvo_people_arenda = $kolvo_people_arenda + $kolvo_po_kateg;
          $kolvo_people_arenda_long = $kolvo_people_arenda_long + $kolvo_po_kateg * $activity_long;
        }

        // Определяем сколько посетителей каждой категории из каких регионов.
        if ($region == "Вологда") {
          $visitors_vol['all'][] = [
            'kto' => $kategoria,
            'skolko' => $kolvo_po_kateg,
            'otkuda' => $region,
          ];
          if (!isset($visitors_vol[$kategoria]['sum'])) {
            $visitors_vol[$kategoria]['sum'] = 0;
            $visitors_vol[$kategoria]['key'] = $kategoria;
            $visitors_vol[$kategoria]['ekskurs'] = 0;
            $visitors_vol[$kategoria]['ekskurs_long'] = 0;
            $visitors_vol[$kategoria]['meropr'] = 0;

          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Театральное представление') {
            $visitors_vol[$kategoria]['ekskurs'] = $visitors_vol[$kategoria]['ekskurs'] + $kolvo_po_kateg;
            $visitors_vol[$kategoria]['ekskurs_long'] = $visitors_vol[$kategoria]['ekskurs_long']
            + $kolvo_po_kateg * $activity_long;
          }
          elseif ($name_programm == 'Массовое мероприятие') {
            $visitors_vol[$kategoria]['meropr'] = $visitors_vol[$kategoria]['meropr'] + $kolvo_po_kateg;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Массовое мероприятие' ||
          $name_programm == 'Театральное представление') {
            $visitors_vol[$kategoria]['sum'] = $visitors_vol[$kategoria]['sum'] + $kolvo_po_kateg;
          }
        }
        if ($region == "Вологодская область") {
          $visitors_volobl['all'][] = [
            'kto' => $kategoria,
            'skolko' => $kolvo_po_kateg,
            'otkuda' => $region,
          ];
          if (!isset($visitors_volobl[$kategoria]['sum'])) {
            $visitors_volobl[$kategoria]['sum'] = 0;
            $visitors_volobl[$kategoria]['key'] = $kategoria;
            $visitors_volobl[$kategoria]['ekskurs'] = 0;
            $visitors_volobl[$kategoria]['ekskurs_long'] = 0;
            $visitors_volobl[$kategoria]['meropr'] = 0;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Театральное представление') {
            $visitors_volobl[$kategoria]['ekskurs'] = $visitors_volobl[$kategoria]['ekskurs'] + $kolvo_po_kateg;
            $visitors_volobl[$kategoria]['ekskurs_long'] = $visitors_volobl[$kategoria]['ekskurs_long']
            + $kolvo_po_kateg * $activity_long;
          }
          elseif ($name_programm == 'Массовое мероприятие') {
            $visitors_volobl[$kategoria]['meropr'] = $visitors_volobl[$kategoria]['meropr'] + $kolvo_po_kateg;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Массовое мероприятие' ||
          $name_programm == 'Театральное представление') {
            $visitors_volobl[$kategoria]['sum'] = $visitors_volobl[$kategoria]['sum'] + $kolvo_po_kateg;
          }
        }
        if ($region == "Другие регионы России") {
          $visitors_russia['all'][] = [
            'kto' => $kategoria,
            'skolko' => $kolvo_po_kateg,
            'otkuda' => $region,
          ];
          if (!isset($visitors_russia[$kategoria]['sum'])) {
            $visitors_russia[$kategoria]['sum'] = 0;
            $visitors_russia[$kategoria]['key'] = $kategoria;
            $visitors_russia[$kategoria]['ekskurs'] = 0;
            $visitors_russia[$kategoria]['ekskurs_long'] = 0;
            $visitors_russia[$kategoria]['meropr'] = 0;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Театральное представление') {
            $visitors_russia[$kategoria]['ekskurs'] = $visitors_russia[$kategoria]['ekskurs'] + $kolvo_po_kateg;
            $visitors_russia[$kategoria]['ekskurs_long'] = $visitors_russia[$kategoria]['ekskurs_long']
            + $kolvo_po_kateg * $activity_long;
          }
          elseif ($name_programm == 'Массовое мероприятие') {
            $visitors_russia[$kategoria]['meropr'] = $visitors_russia[$kategoria]['meropr'] + $kolvo_po_kateg;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Массовое мероприятие' ||
          $name_programm == 'Театральное представление') {
            $visitors_russia[$kategoria]['sum'] = $visitors_russia[$kategoria]['sum'] + $kolvo_po_kateg;
          }
        }
        if ($region == "Другие страны") {
          $visitors_another['all'][] = [
            'kto' => $kategoria,
            'skolko' => $kolvo_po_kateg,
            'otkuda' => $region,
          ];
          if (!isset($visitors_another[$kategoria]['sum'])) {
            $visitors_another[$kategoria]['sum'] = 0;
            $visitors_another[$kategoria]['key'] = $kategoria;
            $visitors_another[$kategoria]['ekskurs'] = 0;
            $visitors_another[$kategoria]['ekskurs_long'] = 0;
            $visitors_another[$kategoria]['meropr'] = 0;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Театральное представление') {
            $visitors_another[$kategoria]['ekskurs'] = $visitors_another[$kategoria]['ekskurs'] + $kolvo_po_kateg;
            $visitors_another[$kategoria]['ekskurs_long'] = $visitors_another[$kategoria]['ekskurs_long']
            + $kolvo_po_kateg * $activity_long;
          }
          elseif ($name_programm == 'Массовое мероприятие') {
            $visitors_another[$kategoria]['meropr'] = $visitors_another[$kategoria]['meropr'] + $kolvo_po_kateg;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Массовое мероприятие' ||
          $name_programm == 'Театральное представление') {
            $visitors_another[$kategoria]['sum'] = $visitors_another[$kategoria]['sum'] + $kolvo_po_kateg;
          }
        }
        if ($region == "Регион неизвестен") {
          $visitors_none['all'][] = [
            'kto' => $kategoria,
            'skolko' => $kolvo_po_kateg,
            'otkuda' => $region,
          ];
          if (!isset($visitors_none[$kategoria]['sum'])) {
            $visitors_none[$kategoria]['sum'] = 0;
            $visitors_none[$kategoria]['key'] = $kategoria;
            $visitors_none[$kategoria]['ekskurs'] = 0;
            $visitors_none[$kategoria]['ekskurs_long'] = 0;
            $visitors_none[$kategoria]['meropr'] = 0;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Театральное представление') {
            $visitors_none[$kategoria]['ekskurs'] = $visitors_none[$kategoria]['ekskurs'] + $kolvo_po_kateg;
            $visitors_none[$kategoria]['ekskurs_long'] = $visitors_none[$kategoria]['ekskurs_long']
            + $kolvo_po_kateg * $activity_long;
          }
          elseif ($name_programm == 'Массовое мероприятие') {
            $visitors_none[$kategoria]['meropr'] = $visitors_none[$kategoria]['meropr'] + $kolvo_po_kateg;
          }
          if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
          $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
          $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
          $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Массовое мероприятие' ||
          $name_programm == 'Театральное представление') {
            $visitors_none[$kategoria]['sum'] = $visitors_none[$kategoria]['sum'] + $kolvo_po_kateg;
          }
        }
      }
    }

    // Считаем общий доход по всем программам: сумма всех "оплачено".
    $nids = array_keys($orders);
    $payments = Helper::getPayment($nids);
    $payment = [];
    $vsego_oplacheno = 0;
    $oplacheno_ekskurs = 0;
    $oplacheno_meropr = 0;
    $oplacheno_arenda = 0;
    if ($payments) {
      foreach ($payments as $key => $node_payment) {
        $node_orders = $node_payment->field_payment_ref_orders->entity;
        $node_usluga = $node_orders->field_orders_ref_activity->entity;
        $termin = $node_usluga->field_activity_type->entity;
        $name_programm = $termin->name->value;
        $id = $node_payment->id();
        $payment[$id] = [
          'oplacheno' => $node_payment->field_payment_summa->value,
        ];
        $vsego_oplacheno = $vsego_oplacheno + $payment[$id]['oplacheno'];

        // Считаем доход по всем программам, относящимся к экскурсиям (сумма всех "оплачено").
        if ($name_programm == 'Игровые мероприятия' || $name_programm == 'Мастер-класс' ||
        $name_programm == 'Один день из жизни деревни' || $name_programm == 'Солнцеворот' ||
        $name_programm == 'Туристический поезд' || $name_programm == 'Экологическая программа' ||
        $name_programm == 'Экскурсионное обслуживание' || $name_programm == 'Театральное представление') {
          $oplacheno_ekskurs = $oplacheno_ekskurs + $payment[$id]['oplacheno'];
        }
        // Считаем доход по всем программам, относящимся к массовым мероприятиям (сумма всех "оплачено").
        if ($name_programm == 'Массовое мероприятие') {
          $oplacheno_meropr = $oplacheno_meropr + $payment[$id]['oplacheno'];
        }
        // Считаем доход по всем программам, относящимся к аренде (сумма всех "оплачено").
        if ($name_programm == 'Аренда') {
          $oplacheno_arenda = $oplacheno_arenda + $payment[$id]['oplacheno'];
        }
      }
    }

    // Считаем дебеторку: разницу между тем, что начислено за услуги и оплаченной суммой.
    $debet = $summ_fakt_cost - $vsego_oplacheno;

    // Получаем ФИО работника и отработанные им часы.
    $teams = Helper::getTeam();
    $team = [];
    foreach ($teams as $key => $node_team) {
      $id = $node_team->id();
      if ($node_team->field_team_status->value == 'active') {
        $team[$id] = [
          'title' => $node_team->title->value,
          'status' => "Штатный",
        ];
      }
    }
    $team['o'] = [
      'title' => "Внештатные сотрудники",
      'status' => "Внештатный",
    ];

    $exhour = Helper::getExhour($nids);
    $vsego_chasov = 0;
    if ($exhour) {
      foreach ($exhour as $key => $node_exhour) {
        $tid = $node_exhour->field_exhour_team->entity->id();
        $hours = $node_exhour->field_exhour_hours->value;
        if (isset($team[$tid]['status']) && $team[$tid]['status'] == "Штатный") {
        }
        else {
          $tid = 'o';
        }
        if (!isset($team[$tid]['chas_itogo'])) {
          $team[$tid]['chas'][] = $hours;
          $team[$tid]['chas_itogo'] = 0;
        }
        $team[$tid]['chas_itogo'] = $team[$tid]['chas_itogo'] + $hours;
        // ВСЕ часы в сумме.
        $vsego_chasov = $vsego_chasov + $hours;
      }
    }
    // ВСЕ посетители по всем категориям в сумме.
    $vse_visitors_sum = 0;
    foreach ($visitors_kateg as $key => $value) {
      $vse_visitors_sum = $vse_visitors_sum + $value['sum'];
    }
    // ВСЕ посетители по всем регионам в сумме.
    $vse_visitors_reg_sum = 0;
    foreach ($visitors_reg as $key => $value) {
      $vse_visitors_reg_sum = $vse_visitors_reg_sum + $value['sum'];
    }

    /*foreach ($team as $key => $value) {
      if (!isset($value['chas_itogo'])) {
        unset($team[$key]);
      }
    }*/

    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);

    // А вот и сам массив, данные из которого мы выводим на странице.
    $renderable = [];
    $renderable['info'] = [
      '#markup' => "Общий отчет с " . format_date(strtotime($start), 'custom', 'd-m-Y')
      . " по " . format_date(strtotime($end), 'custom', 'd-m-Y'),
    ];
    $data = [
      'team' => $team,
      'vsego_chasov' => number_format($vsego_chasov, 0, ",", " "),
      'all_programm' => $all_programm,
      'vsego_programm' => $vsego_programm,
      'kolvo_ekskurs' => number_format($kolvo_ekskurs, 0, ",", " ") . " шт.",
      'kolvo_meropr' => number_format($kolvo_meropr, 0, ",", " ") . " шт.",
      'kolvo_arenda' => number_format($kolvo_arenda, 0, ",", " ") . " шт.",
      'kolvo_ekskurs_long' => number_format($kolvo_ekskurs_long, 0, ",", " ") . " час.",
      'kolvo_meropr_long' => number_format($kolvo_meropr_long, 0, ",", " ") . " час.",
      'kolvo_arenda_long' => number_format($kolvo_arenda_long, 0, ",", " ") . " час.",
      'vsego_people' => number_format($vsego_people, 0, ",", " ") . " чел.",
      'vsego_people_long' => number_format($vsego_people_long, 0, ",", " ") . " чел · час.",
      'vsego_people_vhod' => number_format($vsego_peopl_vhod, 0, ",", " ") . " чел.",
      'people_free_vhod' => number_format($people_free_vhod, 0, ",", " ") . " чел.",
      'kolvo_people_ekskurs' => number_format($kolvo_people_ekskurs, 0, ",", " ") . " чел.",
      'kolvo_people_meropr' => number_format($kolvo_people_meropr, 0, ",", " ") . " чел.",
      'kolvo_people_arenda' => number_format($kolvo_people_arenda, 0, ",", " ") . " чел.",
      'kolvo_people_ekskurs_long' => number_format($kolvo_people_ekskurs_long, 0, ",", " ") . " чел · час.",
      'kolvo_people_meropr_long' => number_format($kolvo_people_meropr_long, 0, ",", " ") . " чел · час.",
      'kolvo_people_arenda_long' => number_format($kolvo_people_arenda_long, 0, ",", " ") . " чел · час.",
      'summ_cost_vhod' => number_format($summ_cost_vhod, 0, ",", " ") . " руб.",
      'summ_fakt_cost' => number_format($summ_fakt_cost, 0, ",", " ") . " руб.",
      'oplacheno' => number_format($vsego_oplacheno, 0, ",", " ") . " руб.",
      'oplacheno_ekskurs' => number_format($oplacheno_ekskurs, 0, ",", " ") . " руб.",
      'oplacheno_meropr' => number_format($oplacheno_meropr, 0, ",", " ") . " руб.",
      'oplacheno_arenda' => number_format($oplacheno_arenda, 0, ",", " ") . " руб.",
      'debet' => number_format($debet, 0, ",", " ") . " руб.",
      'all_kategory' => $visitors_kateg,
      'vse_visitors_sum' => $vse_visitors_sum,
      'all_region' => $visitors_reg,
      'vse_visitors_reg_sum' => $vse_visitors_reg_sum,
      'today' => format_date(time(), 'custom', 'd-m-Y'),
      'now' => format_date(time(), 'custom', 'H:i'),
      'user' => $user->name->value,
    ];
    // Строим три таблицы по разным категориям посетителей из разных регионов.
    // Шапка таблицы:
    $header = [
      '',
      'Вологда',
      'Вологодская область',
      'Регионы России',
      'Другие страны',
      'Регион неизвестен',
    ];
    // Левый столбец таблицы:
    $kat_visit = [
      'adult' => 'Взрослые',
      'student' => 'Студенты',
      'school' => 'Школьники',
      'baby' => 'Дошкольники',
      'old' => 'Пенсионеры',
      'military' => 'Военнослужащие',
      'museum' => 'Музейные работники',
      'lgotniki' => 'Льготники',
      'guest' => 'Гости',
    ];
    // Информация для таблицы-1 (посещаемость экскурсий в штуках).
    $rows1 = [];
    foreach ($kat_visit as $key) {
      $row1 = [];
      foreach ($header as $k => $v) {
        if ($k == 0) {
          $row1[] = $key;
        }
        elseif ($k == 1) {
          if (isset($visitors_vol[$key])) {
            $row1[] = $visitors_vol[$key]['ekskurs'];
          }
          else {
            $row1[] = 0;
          }
        }
        elseif ($k == 2) {
          if (isset($visitors_volobl[$key])) {
            $row1[] = $visitors_volobl[$key]['ekskurs'];
          }
          else {
            $row1[] = 0;
          }
        }
        elseif ($k == 3) {
          if (isset($visitors_russia[$key])) {
            $row1[] = $visitors_russia[$key]['ekskurs'];
          }
          else {
            $row1[] = 0;
          }
        }
        elseif ($k == 4) {
          if (isset($visitors_another[$key])) {
            $row1[] = $visitors_another[$key]['ekskurs'];
          }
          else {
            $row1[] = 0;
          }
        }
        elseif ($k == 5) {
          if (isset($visitors_none[$key])) {
            $row1[] = $visitors_none[$key]['ekskurs'];
          }
          else {
            $row1[] = 0;
          }
        }
        else {
          $row1[] = 0;
        }
      }
      $rows1[] = $row1;
    }
    // Сумма по столбикам:
    $vol1 = 0;
    $volobl1 = 0;
    $russia1 = 0;
    $another1 = 0;
    $none1 = 0;
    foreach ($rows1 as $key => $value) {
      $vol1 = $vol1 + $value[1];
      $volobl1 = $volobl1 + $value[2];
      $russia1 = $russia1 + $value[3];
      $another1 = $another1 + $value[4];
      $none1 = $none1 + $value[5];
    }
    // Информация для таблицы-2 (посещаемость экскурсий в часах).
    $rows11 = [];
    foreach ($kat_visit as $key) {
      $row11 = [];
      foreach ($header as $k => $v) {
        if ($k == 0) {
          $row11[] = $key;
        }
        elseif ($k == 1) {
          if (isset($visitors_vol[$key])) {
            $row11[] = $visitors_vol[$key]['ekskurs_long'];
          }
          else {
            $row11[] = 0;
          }
        }
        elseif ($k == 2) {
          if (isset($visitors_volobl[$key])) {
            $row11[] = $visitors_volobl[$key]['ekskurs_long'];
          }
          else {
            $row11[] = 0;
          }
        }
        elseif ($k == 3) {
          if (isset($visitors_russia[$key])) {
            $row11[] = $visitors_russia[$key]['ekskurs_long'];
          }
          else {
            $row11[] = 0;
          }
        }
        elseif ($k == 4) {
          if (isset($visitors_another[$key])) {
            $row11[] = $visitors_another[$key]['ekskurs_long'];
          }
          else {
            $row11[] = 0;
          }
        }
        elseif ($k == 5) {
          if (isset($visitors_none[$key])) {
            $row11[] = $visitors_none[$key]['ekskurs_long'];
          }
          else {
            $row11[] = 0;
          }
        }
        else {
          $row11[] = 0;
        }
      }
      $rows11[] = $row11;
    }
    // Сумма по столбикам:
    $vol11 = 0;
    $volobl11 = 0;
    $russia11 = 0;
    $another11 = 0;
    $none11 = 0;
    foreach ($rows11 as $key => $value) {
      $vol11 = $vol11 + $value[1];
      $volobl11 = $volobl11 + $value[2];
      $russia11 = $russia11 + $value[3];
      $another11 = $another11 + $value[4];
      $none11 = $none11 + $value[5];
    }
    // Информация для таблицы-3 (посещаемость мероприятий).
    $rows2 = [];
    foreach ($kat_visit as $key) {
      $row2 = [];
      foreach ($header as $k => $v) {
        if ($k == 0) {
          $row2[] = $key;
        }
        elseif ($k == 1) {
          if (isset($visitors_vol[$key])) {
            $row2[] = $visitors_vol[$key]['meropr'];
          }
          else {
            $row2[] = 0;
          }
        }
        elseif ($k == 2) {
          if (isset($visitors_volobl[$key])) {
            $row2[] = $visitors_volobl[$key]['meropr'];
          }
          else {
            $row2[] = 0;
          }
        }
        elseif ($k == 3) {
          if (isset($visitors_russia[$key])) {
            $row2[] = $visitors_russia[$key]['meropr'];
          }
          else {
            $row2[] = 0;
          }
        }
        elseif ($k == 4) {
          if (isset($visitors_another[$key])) {
            $row2[] = $visitors_another[$key]['meropr'];
          }
          else {
            $row2[] = 0;
          }
        }
        elseif ($k == 5) {
          if (isset($visitors_none[$key])) {
            $row2[] = $visitors_none[$key]['meropr'];
          }
          else {
            $row2[] = 0;
          }
        }
        else {
          $row2[] = 0;
        }
      }
      $rows2[] = $row2;
    }
    // Сумма по столбикам:
    $vol2 = 0;
    $volobl2 = 0;
    $russia2 = 0;
    $another2 = 0;
    $none2 = 0;
    foreach ($rows2 as $key => $value) {
      $vol2 = $vol2 + $value[1];
      $volobl2 = $volobl2 + $value[2];
      $russia2 = $russia2 + $value[3];
      $another2 = $another2 + $value[4];
      $none2 = $none2 + $value[5];
    }
    // Информация для таблицы-4 (посещаемость общая - суммарная).
    $rows3 = [];
    foreach ($kat_visit as $key) {
      $row3 = [];
      foreach ($header as $k => $v) {
        if ($k == 0) {
          $row3[] = $key;
        }
        elseif ($k == 1) {
          if (isset($visitors_vol[$key])) {
            $row3[] = $visitors_vol[$key]['sum'];
          }
          else {
            $row3[] = 0;
          }
        }
        elseif ($k == 2) {
          if (isset($visitors_volobl[$key])) {
            $row3[] = $visitors_volobl[$key]['sum'];
          }
          else {
            $row3[] = 0;
          }
        }
        elseif ($k == 3) {
          if (isset($visitors_russia[$key])) {
            $row3[] = $visitors_russia[$key]['sum'];
          }
          else {
            $row3[] = 0;
          }
        }
        elseif ($k == 4) {
          if (isset($visitors_another[$key])) {
            $row3[] = $visitors_another[$key]['sum'];
          }
          else {
            $row3[] = 0;
          }
        }
        elseif ($k == 5) {
          if (isset($visitors_none[$key])) {
            $row3[] = $visitors_none[$key]['sum'];
          }
          else {
            $row3[] = 0;
          }
        }
        else {
          $row3[] = 0;
        }
      }
      $rows3[] = $row3;
    }
    // Сумма по столбикам:
    $vol3 = 0;
    $volobl3 = 0;
    $russia3 = 0;
    $another3 = 0;
    $none3 = 0;
    foreach ($rows3 as $key => $value) {
      $vol3 = $vol3 + $value[1];
      $volobl3 = $volobl3 + $value[2];
      $russia3 = $russia3 + $value[3];
      $another3 = $another3 + $value[4];
      $none3 = $none3 + $value[5];
    }

    // Массив для построения таблиц:
    $data['ekskurs'] = [
      '#theme' => 'table',
      '#caption' => [
        '#markup' => 'Категории посетителей экскурсий в штуках <span type="button"
      class="badge badge-success" data-container="body" data-toggle="popover"
      data-placement="top" data-content="Показывает количество проведенных
      экскурсий-мероприятий. К экскурсиям относятся мероприятия из категорий
      «Игровые мероприятия», «Мастер-классы», «Один день из жизни деревни»,
      «Солнцеворот», «Театральное представление», «Экологическая программа»,
      «Экскурсионное обслуживание»">
        i
      </span>',
      ],
      '#attributes' => ['class' => ['tables-kateg-posetit']],
      '#header' => $header,
      '#rows' => $rows1,
      '#footer' => [
        ['ВСЕГО:', $vol1, $volobl1, $russia1, $another1, $none1],
      ],
    ];
    $data['ekskurs_long'] = [
      '#theme' => 'table',
      '#caption' => [
        '#markup' => 'Категории посетителей экскурсий в часах <span type="button"
    class="badge badge-success" data-container="body" data-toggle="popover"
    data-placement="top" data-content="Показывает количество проведенных
    экскурсий-мероприятий в экскурсионных часах – сумма произведений мероприятия
    на его продолжительность в академических часах. К экскурсиям относятся
    мероприятия из категорий «Игровые мероприятия», «Мастер-классы», «Один день
    из жизни деревни», «Солнцеворот», «Театральное представление», «Экологическая
    программа», «Экскурсионное обслуживание»">
      i
    </span>',
      ],
      '#attributes' => ['class' => ['tables-kateg-posetit']],
      '#header' => $header,
      '#rows' => $rows11,
      '#footer' => [
        ['ВСЕГО:', $vol11, $volobl11, $russia11, $another11, $none11],
      ],
    ];
    $data['meropr'] = [
      '#theme' => 'table',
      '#caption' => [
        '#markup' => 'Категории посетителей массовых мероприятий <span type="button"
    class="badge badge-success" data-container="body" data-toggle="popover"
    data-placement="top" data-content="Показывает количество массовых мероприятий.
    К массовым мероприятиям относятся мероприятия из категорий «Массовое
    мероприятие», «Туристический поезд»">
      i
    </span>',
      ],
      '#attributes' => ['class' => ['tables-kateg-posetit']],
      '#header' => $header,
      '#rows' => $rows2,
      '#footer' => [
        ['ВСЕГО:', $vol2, $volobl2, $russia2, $another2, $none2],
      ],
    ];
    $data['sum'] = [
      '#theme' => 'table',
      '#caption' => [
        '#markup' => 'Категории посетителей (сводная) в людях <span type="button"
  class="badge badge-success" data-container="body" data-toggle="popover"
  data-placement="top" data-content="Показывает сумму соответствующих значений
  таблицы «Категории посетителей экскурсий в штуках» и таблицы «Категории
  посетителей массовых мероприятий»">
    i
  </span>',
    ],
      '#attributes' => ['class' => ['tables-kateg-posetit']],
      '#header' => $header,
      '#rows' => $rows3,
      '#footer' => [
        ['ВСЕГО:', $vol3, $volobl3, $russia3, $another3, $none3],
      ],
    ];
    $renderable['h'] = [
      '#theme' => 'report-itog',
      '#data' => $data,
    ];
    return $renderable;
  }

}
