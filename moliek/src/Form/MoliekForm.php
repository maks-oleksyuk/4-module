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
    // Add a wrapper div to update ajax.
    $form['#prefix'] = '<div id="moliek-form">';
    $form['#suffix'] = '</div>';
    // Attach style files.
    $form['#attached']['library'][] = 'moliek/style';
    // Start the function of creating tables.
    $this->createTable($form, $form_state);
    // Add a button to add a table.
    $form['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t("Add Table"),
      '#submit' => ['::addTable'],
      '#name' => 'add_table',
      '#attributes' => [
        'class' => [
          'btn-success',
        ],
      ],
      '#ajax' => [
        'event' => 'click',
        'progress' => 'none',
        'callback' => '::refreshAjax',
        'wrapper' => 'moliek-form',
      ],
    ];
    // Add a submit button.
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
    // Declare an array of table headers.
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
    // Create a specified number of tables.
    for ($t = 0; $t < $this->countTable; $t++) {
      // Add a button to add row in table.
      $form["button_$t"] = [
        '#type' => 'submit',
        '#name' => $t,
        '#value' => $this->t("Add Year"),
        '#submit' => ['::addRow'],
        '#attributes' => [
          'class' => [
            'btn-secondary',
          ],
        ],
        '#ajax' => [
          'event' => 'click',
          'progress' => 'none',
          'callback' => '::refreshAjax',
          'wrapper' => 'moliek-form',
        ],
      ];
      // Create a table with headings.
      $form["table_$t"] = [
        '#type' => 'table',
        '#header' => $header_title,
        '#empty' => t('No content available.'),
      ];
      // Create a specified number of row in table.
      for ($r = $this->countRow[$t]; $r > 0; $r--) {
        // Create cells in a row.
        foreach ($header_title as $c) {
          // Assign the required type to all cells.
          $form["table_$t"]["rows_$r"]["$c"] = [
            '#type' => 'number',
          ];
          // Turn off unnecessary cells.
          if (in_array("$c", ['Q1', 'Q2', 'Q3', 'Q4', 'YTD'])) {
            $form["table_$t"]["rows_$r"]["$c"] = [
              '#type' => 'number',
              '#disabled' => TRUE,
            ];
          }
        }
        // Adjust the cell with the year.
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
    // Increase the number of tables.
    $this->countTable++;
    // Set 1 row for the new table.
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
    // Get the ID of the pressed button.
    $t = $form_state->getTriggeringElement()['#name'];
    // Increase the number of rows for the desired table.
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
    // Validate only when press the button submit.
    if ($form_state->getTriggeringElement()['#name'] !== 'submit') {
      return;
    }
    // Obtain data from the table.
    $tables = $form_state->getValues();
    // The identifier of the table with the least number of rows.
    $m = array_search(min($this->countRow), $this->countRow);
    // Loop for all table.
    for ($t = 0; $t < $this->countTable; $t++) {
      // Variable whether an item is found in a row.
      $hasValue = 0;
      $hasEmpty = 0;
      for ($r = 1; $r <= $this->countRow[$t]; $r++) {
        foreach (array_reverse($tables["table_$t"]["rows_$r"]) as $key => $i) {
          if (in_array("$key", ['Year', 'Q1', 'Q2', 'Q3', 'Q4', 'YTD'])) {
            goto end;
          }
          if ($r <= $this->countRow[$m]) {
            if (!$hasValue && !$hasEmpty && $i !== "") {
              $hasValue = 1;
            }
            if ($hasValue && !$hasEmpty && $i == "") {
              $hasEmpty = 1;
            }
            if ($hasValue && $hasEmpty && $i !== "") {
              $form_state->setErrorByName("Empty cell", 'Invalid');
              break 3;
            }
            if ($tables["table_$m"]["rows_$r"][$key] == "" && $i !== "" || $tables["table_$m"]["rows_$r"][$key] !== "" && $i == "") {
              $form_state->setErrorByName("Not the same tables 1", 'Invalid');
              break 3;
            }
          }
          elseif ($i !== "") {
            $form_state->setErrorByName("Not the same tables 2", 'Invalid');
            break 3;
          }
          end:
        }
      }
      if (!$hasValue && !$hasEmpty) {
        $form_state->setErrorByName("Empty table", 'Invalid');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getErrors()) {
      $this->messenger()->addError("Invalid");
      $form_state->clearErrors();
    }
    else {
      for ($t = 0; $t < $this->countTable; $t++) {
        for ($r = 1; $r <= $this->countRow[$t]; $r++) {
          $rt = $form_state->getValue(["table_$t", "rows_$r"]);
          $q1 = $q2 = $q3 = $q4 = 0;
          if ($rt['Jan'] != "" || $rt['Feb'] != "" || $rt['Mar'] != "") {
            $q1 = (int) $rt['Jan'] + (int) $rt['Feb'] + (int) $rt['Mar'];
            $q1 = round(($q1 + 1) / 3, 2);
            $form["table_$t"]["rows_$r"]['Q1']['#value'] = $q1;
          }
          if ($rt['Apr'] != "" || $rt['May'] != "" || $rt['Jun'] != "") {
            $q2 = (int) $rt['Apr'] + (int) $rt['May'] + (int) $rt['Jun'];
            $q2 = round(($q2 + 1) / 3, 2);
            $form["table_$t"]["rows_$r"]['Q2']['#value'] = $q2;
          }
          if ($rt['Jul'] != "" || $rt['Aug'] != "" || $rt['Sep'] != "") {
            $q3 = (int) $rt['Jul'] + (int) $rt['Aug'] + (int) $rt['Sep'];
            $q3 = round(($q3 + 1) / 3, 2);
            $form["table_$t"]["rows_$r"]['Q3']['#value'] = $q3;
          }
          if ($rt['Oct'] != "" || $rt['Nov'] != "" || $rt['Dec'] != "") {
            $q4 = (int) $rt['Oct'] + (int) $rt['Nov'] + (int) $rt['Dec'];
            $q4 = round(($q4 + 1) / 3, 2);
            $form["table_$t"]["rows_$r"]['Q4']['#value'] = $q4;
          }
          if ($q1 || $q2 || $q3 || $q4) {
            $ytd = $q1 + $q2 + $q3 + $q4;
            $ytd = round(($ytd + 1) / 4, 2);
            $form["table_$t"]["rows_$r"]['YTD']['#value'] = $ytd;
          }
        }
      }

      $this->messenger()->addStatus("Valid");
    }
  }

}
