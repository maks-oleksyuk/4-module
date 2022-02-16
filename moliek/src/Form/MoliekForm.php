<?php

namespace Drupal\moliek\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the forms tables.
 */
class MoliekForm extends FormBase {

  /**
   * Variable for storing the number of tables.
   *
   * @var int
   */
  protected int $countTable = 1;

  /**
   * Variable to store the number of rows in tables.
   *
   * @var array
   */
  protected array $countRow = [1];

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'moliek_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#prefix'] = '<div id="moliek-form">';
    $form['#suffix'] = '</div>';
    $form['#attached']['library'][] = 'moliek/style';

    $this->createTable($form, $form_state);

    $form['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t("Add Table"),
      '#submit' => ['::addTable'],
      '#name' => 'add_table',
      '#ajax' => [
        'event' => 'click',
        'progress' => 'none',
        'callback' => '::refreshAjax',
        'wrapper' => 'moliek-form',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t("Submit"),
      '#ajax' => [
        'event' => 'click',
        'progress' => 'none',
        'callback' => '::refreshAjax',
        'wrapper' => 'moliek-form',
      ],
    ];

    return $form;
  }

  /**
   * Table constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structures.
   */
  public function createTable(array &$form, FormStateInterface $form_state): array {
    $header_title = [
      'year' => $this->t('Year'),
      'jan' => $this->t('Jan'),
      'feb' => $this->t('Feb'),
      'mar' => $this->t('Mar'),
      'q1' => $this->t('Q1'),
      'apr' => $this->t('Apr'),
      'may' => $this->t('May'),
      'jun' => $this->t('Jun'),
      'q2' => $this->t('Q2'),
      'jul' => $this->t('Jul'),
      'aug' => $this->t('Aug'),
      'sep' => $this->t('Sep'),
      'q3' => $this->t('Q3'),
      'oct' => $this->t('Oct'),
      'nov' => $this->t('Nov'),
      'dec' => $this->t('Dec'),
      'q4' => $this->t('Q4'),
      'ytd' => $this->t('YTD'),
    ];
    for ($t = 0; $t < $this->countTable; $t++) {
      $form["button_$t"] = [
        '#type' => 'submit',
        '#name' => $t,
        '#value' => $this->t("Add Year"),
        '#submit' => ['::addRow'],
        '#ajax' => [
          'event' => 'click',
          'progress' => 'none',
          'callback' => '::refreshAjax',
          'wrapper' => 'moliek-form',
        ],
      ];
      $form["table_$t"] = [
        '#type' => 'table',
        '#header' => $header_title,
        '#empty' => t('No content available.'),
      ];
      for ($r = $this->countRow[$t]; $r > 0; $r--) {
        foreach ($header_title as $c) {
          $form["table_$t"]["rows_$r"]["$c"] = [
            '#type' => 'number',
          ];
          if (in_array("$c", ['Q1', 'Q2', 'Q3', 'Q4', 'YTD'])) {
            $form["table_$t"]["rows_$r"]["$c"] = [
              '#type' => 'number',
              '#disabled' => TRUE,
            ];
          }
        }
        $form["table_$t"]["rows_$r"]['Year'] = [
          '#type' => 'number',
          '#disabled' => TRUE,
          '#default_value' => date('Y') - $r + 1,
        ];
      }
    }
    return $form;
  }

  /**
   * Add a table.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structures.
   */
  public function addTable(array $form, FormStateInterface $form_state): array {
    $this->countTable++;
    $this->countRow[] = 1;
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Add a row to the table.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structures.
   */
  public function addRow(array $form, FormStateInterface $form_state): array {
    $t = $form_state->getTriggeringElement()['#name'];
    $this->countRow[$t]++;
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Refresh Ajax.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structures.
   */
  public function refreshAjax(array $form, FormStateInterface $form_state): array {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] !== 'submit') {
      return;
    }
    $tables = $form_state->getValues();
    for ($t = 0; $t < $this->countTable; $t++) {
      $hasValue = 0;
      $hasEmpty = 0;
      foreach ($tables["table_$t"] as $r) {
        foreach ($r as $key => $i) {
          if (in_array("$key", ['Year', 'Q1', 'Q2', 'Q3', 'Q4', 'YTD'])) {
            continue;
          }
          if (!$hasValue && !$hasEmpty && $i !== "") {
            $hasValue = 1;
          }
          if ($hasValue && !$hasEmpty && $i == "") {
            $hasEmpty = 1;
          }
          if ($hasValue && $hasEmpty && $i !== "") {
            $form_state->setErrorByName("Empty", 'There should be no gaps in the table.');
            $this->messenger()->addError('Invalid');
            break 2;
          }
        }
      }
      if (!$hasValue && !$hasEmpty) {
        $this->messenger()->addError('Invalid');
        $form_state->setErrorByName("Empty table", 'The table should not be empty.');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getErrors()) {
      foreach ($form_state->getErrors() as $err) {
        $this->messenger()->addError($err);
      }
      $form_state->clearErrors();
    }
    else {
      $this->messenger()->addStatus("Valid");
    }
  }

}
