<?php

namespace Drupal\report\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

/**
 * SimpleForm.
 */
class DateChoice extends FormBase {

  /**
   * F ajaxModeDev.
   */
  public function ajaxDateChoice(array &$form, &$form_state) {
    $response = new AjaxResponse();
    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);
    $date_from = $form_state->getValue('date_from');
    $date_to = $form_state->getValue('date_to');
    if ($user->hasPermission('access report-form')) {
      if ($date_from && $date_to) {
        if ($date_from < $date_to) {
          $response->addCommand(new RedirectCommand('/report/itog/' . $date_from . '/' . $date_to));
        }
        else {
          $response->addCommand(new HtmlCommand("#date-choice", "Начало отчетного
          периода должно быть раньше его окончания."));
        }
      }
      else {
        $response->addCommand(new HtmlCommand("#date-choice", "Введите даты отчетного периода"));
      }
    }
    else {
      $response->addCommand(new HtmlCommand("#date-choice", "Доступ запрещен"));
    }
    return $response;
  }

  /**
   * F ajaxModeDev.
   */
  public function ajaxDateChoice2(array &$form, &$form_state) {
    $response = new AjaxResponse();
    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);
    $date_from = $form_state->getValue('date_from');
    $date_to = $form_state->getValue('date_to');
    if ($user->hasPermission('access report-form')) {
      if ($date_from && $date_to) {
        if ($date_from < $date_to) {
          $response->addCommand(new RedirectCommand('/report/exkurs/' . $date_from . '/' . $date_to));
        }
        else {
          $response->addCommand(new HtmlCommand("#date-choice", "Начало отчетного
          периода должно быть раньше его окончания."));
        }
      }
      else {
        $response->addCommand(new HtmlCommand("#date-choice", "Введите даты отчетного периода"));
      }
    }
    else {
      $response->addCommand(new HtmlCommand("#date-choice", "Доступ запрещен"));
    }
    return $response;
  }

  /**
   * F ajaxModeDev.
   */
  public function ajaxDateChoice3(array &$form, &$form_state) {
    $response = new AjaxResponse();
    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);
    $date_from = $form_state->getValue('date_from');
    $date_to = $form_state->getValue('date_to');
    if ($user->hasPermission('access report-form')) {
      if ($date_from && $date_to) {
        if ($date_from < $date_to) {
          $response->addCommand(new RedirectCommand('report/full?field_orders_date_value=' .
          $date_from . '&field_orders_date_value_1=' . $date_to));
        }
        else {
          $response->addCommand(new HtmlCommand("#date-choice", "Начало отчетного
          периода должно быть раньше его окончания."));
        }
      }
      else {
        $response->addCommand(new HtmlCommand("#date-choice", "Введите даты отчетного периода"));
      }
    }
    else {
      $response->addCommand(new HtmlCommand("#date-choice", "Доступ запрещен"));
    }
    return $response;
  }

  /**
   * F ajaxModeDev.
   */
  public function ajaxDateChoice4(array &$form, &$form_state) {
    $response = new AjaxResponse();
    $usluga = $form_state->getValue('usluga');
    $uid = \Drupal::currentUser()->id();
    $user = User::load($uid);
    $params = [];
    foreach ($usluga as $key => $value) {
      if ($value > 0) {
        $params[] = $value;
      }
    }
    $date_from = $form_state->getValue('date_from');
    $date_to = $form_state->getValue('date_to');
    if ($user->hasPermission('access report-form')) {
      if ($date_from && $date_to) {
        if ($date_from < $date_to) {
          $response->addCommand(new RedirectCommand('/report/vyborka/' . $date_from . '/' . $date_to . '/' . implode("+", $params)));
        }
        else {
          $response->addCommand(new HtmlCommand("#date-choice", "Начало отчетного
          периода должно быть раньше его окончания."));
        }
      }
      else {
        $response->addCommand(new HtmlCommand("#date-choice", "Введите даты отчетного периода"));
      }
    }
    else {
      $response->addCommand(new HtmlCommand("#date-choice", "Доступ запрещен"));
    }
    return $response;
  }

  /**
   * Build the simple form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $extra = NULL) {
    $form_state->setCached(FALSE);
    $form["#suffix"] = '<div id="date-choice"></div>';
    $form['date_from'] = [
      '#type' => 'date',
      '#title' => 'Дата начала:',
      '#ajax' => [
        'callback' => '::ajaxDateChoice',
        'effect' => 'fade',
        'progress' => ['type' => 'throbber', 'message' => ""],
      ],
    ];
    $form['date_to'] = [
      '#type' => 'date',
      '#title' => 'Дата окончания:',
      '#ajax' => [
        'callback' => '::ajaxDateChoice',
        'effect' => 'fade',
        'progress' => ['type' => 'throbber', 'message' => ""],
      ],
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['button'] = [
      '#type' => 'submit',
      '#value' => 'Общий отчет',
      '#attributes' => ['class' => ['btn', 'btn-xs', 'btn-success']],
      '#ajax' => [
        'callback' => '::ajaxDateChoice',
        'effect' => 'fade',
        'progress' => ['type' => 'throbber', 'message' => ""],
      ],
    ];
    $form['actions']['otchet'] = [
      '#type' => 'submit',
      '#value' => 'Экскурсионный отчет',
      '#attributes' => ['class' => ['btn', 'btn-xs', 'btn-warning']],
      '#ajax' => [
        'callback' => '::ajaxDateChoice2',
        'effect' => 'fade',
        'progress' => ['type' => 'throbber', 'message' => ""],
      ],
    ];
    $form['actions']['full'] = [
      '#type' => 'submit',
      '#value' => 'Полный отчет',
      '#attributes' => ['class' => ['btn', 'btn-xs', 'btn-info']],
      '#ajax' => [
        'callback' => '::ajaxDateChoice3',
        'effect' => 'fade',
        'progress' => ['type' => 'throbber', 'message' => ""],
      ],
    ];
    $form['actions2'] = [
      '#type' => 'actions',
    ];
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'activity')
      ->sort('field_activity_type', 'ASC')
      ->sort('title', 'ASC');
    $ids = $query->execute();
    if (!empty($ids)) {
      foreach (Node::loadMultiple($ids) as $nid => $node) {
        $current_title = $node->title->value;
        $current_title .= " - <i>";
        $current_title .= $node->field_activity_type->entity->name->value;
        $current_title .= "</i>";
        $options[$nid] = $current_title;
      }
    }
    $form['actions2']['usluga'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => 'Услуга:',
      '#prefix' => '<br>',
    ];
    $form['actions2']['vyborka'] = [
      '#type' => 'submit',
      '#value' => 'Выборка',
      '#attributes' => ['class' => ['btn', 'btn-xs', 'btn-danger']],
      '#ajax' => [
        'callback' => '::ajaxDateChoice4',
        'effect' => 'fade',
        'progress' => ['type' => 'throbber', 'message' => ""],
      ],
    ];
    return $form;
  }

  /**
   * Getter method for Form ID.
   */
  public function getFormId() {
    return 'button_date_choice_form';
  }

  /**
   * Implements a form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

}
