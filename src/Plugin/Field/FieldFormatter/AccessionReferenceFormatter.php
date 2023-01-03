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
 *   id = "accession_reference",
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
    $elements['zeropad_main'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Zero Pad main value'),
      '#description' => $this->t('Prefix main value with zeroes up to max value digits.'),
      '#default_value' => $this->getSetting('zeropad_main'),
      '#weight' => 10,
    ];

    $elements['zeropad_sub'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Zero Pad sub value'),
      '#description' => $this->t('Prefix sub value with zeroes up to max value digits.'),
      '#default_value' => $this->getSetting('zeropad_sub'),
      '#weight' => 10,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Ref padding (@pm, @ps).',
      [
        "@pm" => $this->getSetting('zeropad_main') ? $this->t('Yes') :  $this->t('No'),
        "@ps" => $this->getSetting('zeropad_sub') ? $this->t('Yes') :  $this->t('No'),
      ]);
    $summary[] = $this->numberFormat(1234,5678);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $separator = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('separator');
    $settings = $this->getFieldSettings();

    foreach ($items as $delta => $item) {
      //$output = $this->numberFormat($item->value, $item->sub_value);
      $elements[$delta] = [
        '#type' => 'accession_reference',
        '#main' => $item->value,
        '#sub' => $item->sub_value,
        '#sep' => $separator,
        '#digits_main' => (strlen((string)$settings['main_max']) ?: 9999),
        '#digits_sub' => (strlen((string)$settings['sub_max']) ?: 9999),
        '#zeropad_main' => $settings['zeropad_main'],
        '#zeropad_sub' => $settings['zeropad_sub'],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'zeropad_main' => FALSE,
      'zeropad_sub' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number, $number2) {
    $separator = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('separator');
    $settings = $this->getFieldSettings();
    $digits_main = strlen((string)$settings['main_max']) ?: 9999;
    $digits_sub = strlen((string)$settings['sub_max']) ?: 9999;
    $format = '%1$0'.$digits_main.'d'.'%3$s'.'%2$0'.$digits_sub.'d';
    return sprintf($format, $number, $number2, $separator);
  }

}
