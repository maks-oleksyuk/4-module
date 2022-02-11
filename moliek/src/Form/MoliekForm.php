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
    $this->createTable($form, $form_state);

    $form['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t("Add Table"),
      '#submit' => ['::addTable'],
      '#ajax' => [
        'event' => 'click',
        'progress' => 'none',
        'callback' => '::refreshAjax',
        'wrapper' => 'moliek-form',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t("Submit"),
    ];
    $form['#attached']['library'][] = 'moliek/style';

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
   *   The form structure.
   */
  public function createTable(array &$form, FormStateInterface $form_state) {
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
      $form["table_$t"] = [
        'actions' => [
          '#type' => 'actions',
          'button' => [
            '#type' => 'submit',
            '#name' => "add_row_$t",
            '#value' => $this->t("Add Year"),
            '#submit' => ['::addRow'],
            '#data_id' => $t,
            '#ajax' => [
              'event' => 'click',
              'progress' => 'none',
              'callback' => '::refreshAjax',
              'wrapper' => 'moliek-form',
            ],
          ],
        ],
        'table' => [
          '#type' => 'table',
          '#header' => $header_title,
          '#empty' => t('No content available.'),
        ],
      ];
      for ($r = 0; $r < $this->countRow[$t]; $r++) {
        foreach ($header_title as $c) {
          $form["table_$t"]['table']["rows_$r"]["$c"] = [
            '#type' => 'number',
          ];
          if (in_array("$c", ['Q1', 'Q2', 'Q3', 'Q4', 'YTD'])) {
            $form["table_$t"]['table']["rows_$r"]["$c"] = [
              '#type' => 'number',
              '#disabled' => TRUE,
            ];
          }
          if ("$c" == 'Year') {
            $form["table_$t"]['table']["rows_$r"]["$c"] = [
              '#type' => 'number',
              '#disabled' => TRUE,
              '#default_value' => date('Y') - $this->countRow[$t] + $r + 1,
            ];

          }
        }
      }
    }
    return $form;
  }

  /**
   *
   */
  public function addTable(array $form, FormStateInterface $form_state) {
    $this->countTable++;
    $this->countRow[] = 1;
    $form_state->setRebuild();
    return $form;
  }

  /**
   *
   */
  public function addRow(array $form, FormStateInterface $form_state) {
    $t = $form_state->getTriggeringElement()['#data_id'];
    $this->countRow[$t]++;
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Refresh Ajax.
   */
  public function refreshAjax(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
