<?php

namespace Drupal\ajaxform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBuilder;
use Drupal\ajaxform\Form\FormBuilderInterface;

/**
 * Class AjaxModelPopupForm.
 */
class AjaxModelPopupForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'ajax_model_popup_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form['#attached']['library'][] = 'ajaxform/ajaxform';

        $form['title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
            // '#description' => $this->t('Title'),
            '#weight' => '0',
        ];
        $form['description'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Description'),
            '#weight' => '0',
        ];
        $form['date'] = [
            '#type' => 'date',
            '#title' => $this->t('Date'),
            '#weight' => '0',
        ];
        $form['author'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Author'),
            '#weight' => '0',
        ];
        $form['category'] = [
            '#type' => 'entity_autocomplete',
            '#title' => $this->t('Category'),
            '#target_type' => 'taxonomy_term',
            '#selection_settings' => [
                'target_bundles' => ['article_categories'],
            ],
            '#ajax' => [
                'callback' => [$this, 'submitModalFormAjax'],
                'wrapper' => 'CategorySelection',
                'event' => 'keyup',
                'url' => \Drupal\Core\Url::fromRoute('ajaxform.open_modal_form'),
                // 'options' => [
                //     'query' => [
                //         Drupal\ajaxform\Form\FormBuilderInterface::AJAX_FORM_REQUEST => TRUE,
                //     ],
                // ],
            ],
            '#weight' => '0',
        ];
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        foreach ($form_state->getValues() as $key => $value) {
            // @TODO: Validate fields.
            if($value == ""){
                $form_state->setError($form[$key], t('Please fill a valid data.'));
            }

        }
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $date = date('Y-m-d\TH:i:s', strtotime($form_state->getValue('date')));
        $entity = \Drupal::entityTypeManager()
                            ->getStorage('node')
                            ->create([
                                'type' => 'article',
                                'title' => $form_state->getValue('title'),
                                'body' => $form_state->getValue('description'),
                                'field_article_date' => $date,
                                'field_author' => $form_state->getValue('author'),
                                'field_category' => $form_state->getValue('category'),
                                ]);
        $entity->save();
        if(is_numeric($entity->id()) && $entity->id() > 0){
            $tempstore = \Drupal::service('tempstore.private');
            $store = $tempstore->get('ajaxform');
            $store->delete('categoryId');
            \Drupal::messenger()->addMessage("Article has been created successfully with the title " . $form_state->getValue('title'));
        }
    }

}
