<?php

namespace Drupal\address_one_line\Plugin\Field\FieldFormatter;

use Drupal\address\AddressInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\address\Plugin\Field\FieldFormatter\AddressPlainFormatter;
use Drupal\address\LabelHelper;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'address_one_line' formatter.
 *
 * @FieldFormatter(
 *   id = "address_one_line",
 *   label = @Translation("One Line"),
 *   field_types = {
 *     "address",
 *   },
 * )
 */
class AddressOneLineFormatter extends AddressPlainFormatter implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();
    $options['hidden'] = [];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['hidden'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hidden Elements'),
      '#options' => LabelHelper::getGenericFieldLabels(),
      '#default_value' => $this->getSetting('hidden'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('hidden')) {
      // $entity_type = $this->entityTypeManager->getDefinition($this->fieldDefinition->getTargetEntityTypeId());
      // $summary[] = $this->t('Linked to the @entity_label', ['@entity_label' => $entity_type->getLabel()]);
    }
    return $summary;
  }

  /**
   * Builds a renderable array for a single address item.
   *
   * @param \Drupal\address\AddressInterface $address
   *   The address.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array.
   */
  protected function viewElement(AddressInterface $address, $langcode) {
    $country_code = $address->getCountryCode();
    $countries = $this->countryRepository->getList();
    $address_format = $this->addressFormatRepository->get($country_code);
    $values = $this->getValues($address, $address_format);
    $hidden = $this->getSetting('hidden');

    $element = [
      '#theme' => 'address_one_line',
      '#given_name' => $values['givenName'],
      '#additional_name' => $values['additionalName'],
      '#family_name' => $values['familyName'],
      '#organization' => $values['organization'],
      '#address_line1' => $values['addressLine1'],
      '#address_line2' => $values['addressLine2'],
      '#postal_code' => $values['postalCode'],
      '#sorting_code' => $values['sortingCode'],
      '#administrative_area' => $values['administrativeArea'],
      '#locality' => is_array($values['locality']) ? $values['locality']['code'] : $values['locality'],
      '#dependent_locality' => $values['dependentLocality'],
      '#country' => [
        'code' => $country_code,
        'name' => $countries[$country_code],
      ],
      '#cache' => [
        'contexts' => [
          'languages:' . LanguageInterface::TYPE_INTERFACE,
        ],
      ],
    ];

    foreach ($hidden as $key) {
      $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
      $element['#' . $key] = NULL;
    }

    return $element;
  }

}
