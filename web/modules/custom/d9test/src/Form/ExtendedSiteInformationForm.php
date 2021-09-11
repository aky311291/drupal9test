<?php

namespace Drupal\d9test\Form;

#use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
# To extend the SiteInformationForm incluse the class here
use Drupal\system\Form\SiteInformationForm;

/**
 * Class ExtendedSiteInformationForm.
 */
class ExtendedSiteInformationForm extends SiteInformationForm
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'extended_site_information_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        # Get the parent form config data
        $site_config = $this->config('system.site');

        # Load the parent form build structures
        $form =  parent::buildForm($form, $form_state);

        # Add new element to the above loaded form build object
        $form['site_information']['siteapikey'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Site API Key'),
            '#description' => $this->t('Set the API Key to system config form.'),
            '#maxlength' => 64,
            '#size' => 64,
            '#default_value' => $site_config->get('siteapikey') ?: 'No API Key yet',
            '#weight' => '0',
        ];

        #Override the submit text on site configuration form page
        $form['actions']['submit']['#value'] = t('Update configuration');

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        foreach ($form_state->getValues() as $key => $value) {
            // @TODO: Validate fields.
        }
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        $siteapikey = $form_state->getValues()['siteapikey'];
        # Add message to be printed after form submit
        \Drupal::messenger()->addMessage("Site API Key has been saved  with value: " . $siteapikey);

        # Set user entered value to siteapikey field
        $this->config('system.site')
            ->set('siteapikey', $form_state->getValue('siteapikey'))
            ->save();

        # Execute parent form submit method
        parent::submitForm($form, $form_state);
    }
}
