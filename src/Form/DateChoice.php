<?php

namespace Drupal\report\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\user\Entity\User;

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
        $response->addCommand(new RedirectCommand('/report/itog/' . $date_from . '/' . $date_to));
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
        $response->addCommand(new RedirectCommand('/report/exp/' . $date_from . '/' . $date_to));
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
      '#value' => 'Полный отчет',
      '#attributes' => ['class' => ['btn', 'btn-xs']],
      '#ajax' => [
        'callback' => '::ajaxDateChoice2',
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
