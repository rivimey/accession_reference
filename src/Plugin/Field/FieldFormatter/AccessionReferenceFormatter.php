<?php

namespace Drupal\accession_reference\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'accession_reference' formatter.
 *
 * The 'Default' formatter is different for integer fields on the one hand, and
 * for decimal and float fields on the other hand, in order to be able to use
 * different settings.
 *
 * @FieldFormatter(
 *   id = "number_accession",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "accession_reference"
 *   }
 * )
 */
class AccessionReferenceFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = [
      '.' => $this->t('Decimal point'),
      ',' => $this->t('Comma'),
      '/' => $this->t('Slash'),
      chr(8201) => $this->t('Thin space'),
      "'" => $this->t('Apostrophe'),
    ];
    $elements['separator'] = [
      '#type' => 'select',
      '#title' => $this->t('Separator'),
      '#options' => $options,
      '#default_value' => $this->getSetting('separator'),
      '#weight' => 0,
    ];

    $elements['prefix_suffix'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display prefix and suffix'),
      '#default_value' => $this->getSetting('prefix_suffix'),
      '#weight' => 10,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Value separator @sep.',
      ["@sep" => $this->getSetting('separator')]);
    $summary[] = $this->numberFormat(1234,5678);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $output = $this->numberFormat($item->value, $item->sub_value);
      if (isset($item->_attributes) && $item->value != $output) {
        $item->_attributes += ['value' => $item->value, 'sub_value' => $item->sub_value];
      }

      $elements[$delta] = ['#markup' => $output];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'separator' => '/',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number, $number2) {
    $settings = $this->getFieldSettings();
    $digits_main = ((int)log($settings['main_max'] ?: 9999)) + 1;
    $digits_sub = ((int)log($settings['sub_max'] ?: 9999)) + 1;
    $format = '%1$0'.$digits_main.'d'.'%3$'.'s'.'%2$0'.$digits_sub.'d';
    return sprintf($format, $number, $number2, $this->getSetting('separator'));
  }

}
