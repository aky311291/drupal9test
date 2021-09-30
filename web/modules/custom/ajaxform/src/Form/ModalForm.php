<?php

namespace Drupal\ajaxform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * ModalForm class.
 */
class ModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_form_example_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['#prefix'] = '<div id="modal_example_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $vid = 'article_categories';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $term_data[0] = "Select Category";
    foreach ($terms as $term) {
        $term_data[$term->tid] = $term->name;
    }
    $tempstore = \Drupal::service('tempstore.private');
    $store = $tempstore->get('ajaxform');
    $cat_id = $store->get('categoryId');
    $form['categorylist'] = array(
        '#type' => 'select',
        '#title' => t('Category list'),
        '#description' => t('Select category from the list.'),
        '#options' => $term_data,
        '#default_value' => $cat_id,
      );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'ajaxform/ajaxform';

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private');
    $store = $tempstore->get('ajaxform');
    $response = new AjaxResponse();
    $cat_id = $form_state->getValue('categorylist');
    if($cat_id == 0){
        $form_state->setError($form['categorylist'], t('Select a category option'));
        $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
    }
    // dpm($form_state->hasAnyErrors());
    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
    }
    else {
        $store->set('categoryId', $cat_id);
      $response->addCommand(new OpenModalDialogCommand("Success!", 'The modal form has been submitted.', ['width' => 800]));
      $selctedCategory = $form['categorylist']['#options'][$cat_id];
      $value = $selctedCategory . ' (' . $cat_id . ')';
      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new InvokeCommand('#edit-category', 'val', [$value]));
      $form_state->setResponse($response);
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.ajaxform'];
  }

}