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
    $elements['zeropad_groupref'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Zero Pad groupref'),
      '#description' => $this->t('Prefix groupref with zeroes up to max value digits.'),
      '#default_value' => $this->getSetting('zeropad_groupref'),
      '#weight' => 10,
    ];

    $elements['zeropad_itemref'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Zero Pad item ref'),
      '#description' => $this->t('Prefix item ref with zeroes up to max value digits.'),
      '#default_value' => $this->getSetting('zeropad_itemref'),
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
        "@pm" => $this->getSetting('zeropad_groupref') ? $this->t('Yes') :  $this->t('No'),
        "@ps" => $this->getSetting('zeropad_itemref') ? $this->t('Yes') :  $this->t('No'),
      ]);
    $summary[] = $this->t('Example for 12/34: @style.',
      [
        "@style" => $this->numberFormat(12,34),
      ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $field_settings = $this->getFieldSettings();
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      //$output = $this->numberFormat($item->groupref, $item->itemref);
      $elements[$delta] = [
        '#type' => 'accession_reference',
        '#groupref' => $item->groupref,
        '#itemref' => $item->itemref,
        '#sep' => $field_settings['separator'],
        '#digits_groupref' => (strlen((string)$field_settings['groupref_max']) ?: 4),
        '#digits_itemref' => (strlen((string)$field_settings['itemref_max']) ?: 4),
        '#zeropad_groupref' => $settings['zeropad_groupref'],
        '#zeropad_itemref' => $settings['zeropad_itemref'],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'zeropad_groupref' => FALSE,
      'zeropad_itemref' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number, $number2) {
    $field_settings = $this->getFieldSettings();
    $settings = $this->getSettings();
    $dgt_grp = strlen((string)($field_settings['groupref_max']) ?: 9999);
    $dgt_itm = strlen((string)($field_settings['itemref_max']) ?: 9999);
    $zp_grp = $settings['zeropad_groupref'] ? '0' : '';
    $zp_itm = $settings['zeropad_itemref'] ? '0' : '';

    $format = '%1$'.$zp_grp.$dgt_grp.'d'.'%3$s'.'%2$'.$zp_itm.$dgt_itm.'d';

    return sprintf($format, $number, $number2, $field_settings['separator']);
  }

}
