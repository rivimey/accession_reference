<?php

namespace Drupal\accession_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'accession_reference' widget.
 *
 * @FieldWidget(
 *   id = "accession_reference",
 *   label = @Translation("Accession Reference"),
 *   field_types = {
 *     "accession_reference",
 *   },
 *   description = "Add or Edit Accession References"
 * )
 */
class AccessionReferenceWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'groupref_min' => '1',
        'groupref_max' => '9999',
        'groupref_placeholder' => '',
        'itemref_min' => '1',
        'itemref_max' => '9999',
        'itemref_placeholder' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['groupref_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Groupref minimum'),
      '#default_value' => $this->getSetting('groupref_min'),
      '#description' => $this->t('The minimum value that should be allowed in the groupref field.'),
    ];
    $element['groupref_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Groupref maximum'),
      '#default_value' => $this->getSetting('groupref_max'),
      '#description' => $this->t('The maximum value that should be allowed in the groupref field.'),
    ];
    $element['itemref_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Itemref Minimum'),
      '#default_value' => $this->getSetting('itemref_min'),
      '#description' => $this->t('The minimum value that should be allowed in the itemref field.'),
    ];
    $element['itemref_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Itemref Maximum'),
      '#default_value' => $this->getSetting('itemref_max'),
      '#description' => $this->t('The maximum value that should be allowed in the itemref field.'),
    ];

    $element['groupref_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Groupref Placeholder'),
      '#default_value' => $this->getSetting('groupref_placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value.'),
    ];
    $element['itemref_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Itemref Placeholder'),
      '#default_value' => $this->getSetting('itemref_placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Groupref range: @groupref_min .. @groupref_max',
      ['@groupref_min' => $this->getSetting('groupref_min'),
        '@groupref_max' => $this->getSetting('groupref_max')]);

    $placeholder1 = $this->getSetting('groupref_placeholder');
    $summary[] = $this->t('Groupref Placeholder: @groupref_placeholder', ['@groupref_placeholder' => $placeholder1]);

    $summary[] = $this->t('Itemref range: @itemref_min .. @itemref_max',
      ['@itemref_min' => $this->getSetting('itemref_min'),
        '@itemref_max' => $this->getSetting('itemref_max')]);

    $placeholder2 = $this->getSetting('itemref_placeholder');
    $summary[] = $this->t('Itemref Placeholder: @itemref_placeholder', ['@itemref_placeholder' => $placeholder2]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $groupref = $items[$delta]->groupref ?? '';
    $itemref = $items[$delta]->itemref ?? '';

    $field_settings = $this->getFieldSettings();
    $settings = $this->getSettings();

    $groupref_min = $itemref_min = 0;
    $digits_groupref_min = $digits_itemref_min = 1;
    $groupref_max = 9_999;
    $digits_groupref_max = 4;
    $itemref_max = 9_999;
    $digits_itemref_max = 4;

    $field_settings += [
      'groupref_min' => '1',
      'groupref_max' => '9999',
      'groupref_placeholder' => '',
      'itemref_min' => '1',
      'itemref_max' => '9999',
      'itemref_placeholder' => '',
    ];

    if (is_numeric($field_settings['groupref_min'])) {
      $groupref_min = max($groupref_min, $field_settings['groupref_min']);
      $digits_groupref_min = strlen((string) $groupref_min);
    }
    if (is_numeric($field_settings['groupref_max'])) {
      $groupref_max = max($groupref_min, $field_settings['groupref_max']);
      $digits_groupref_max = strlen((string) $groupref_max);
    }
    if (is_numeric($field_settings['itemref_min'])) {
      $itemref_min = max($itemref_min, $field_settings['itemref_min']);
      $digits_itemref_min = strlen((string) $itemref_min);
    }
    if (is_numeric($field_settings['itemref_max'])) {
      $itemref_max = max($itemref_min, $field_settings['itemref_max']);
      $digits_itemref_max = strlen((string) $itemref_max);
    }

    $element['value'] = [
      '#type' => 'accession_reference_widget',
      '#settings' => $settings,
      '#field_settings' => $field_settings,

      '#title' => $element['#title'] ?? '',
      '#title_display' =>  $element['title_display'] ?? 'before',
      '#required' => $element['#required'] ?? FALSE,
      '#description' => $element['#description'] ?? '',
      '#description_display' => $element['#description_display'] ?? 'after',

      'groupref' => [
        '#groupref' => $groupref,
        '#tip' => $this->t("Range: @min - @max", ['@min' => $groupref_min, '@max' => $groupref_max]),
        '#size' => $digits_groupref_max,
        '#placeholder' => $this->getSetting('groupref_placeholder') ?? '',
        '#pattern' => '\d{'.$digits_groupref_min.','.$digits_groupref_max.'}',
        '#attributes' => new Attribute(),
      ],

      '#separator' => $field_settings['separator'],

      'itemref' => [
        '#itemref' => $itemref,
        '#tip' => $this->t("Range: @min - @max", ['@min' => $itemref_min, '@max' => $itemref_max]),
        '#size' => $digits_itemref_max,
        '#placeholder' => $this->getSetting('itemref_placeholder') ?? '',
        '#pattern' => '\d{'.$digits_itemref_min.','.$digits_itemref_max.'}',
        '#attributes' => new Attribute(),
      ],

      '#attributes' => new Attribute(),

      '#attached' => [
        'library' => [
          'accession_reference/accession_reference',
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return $element['value'];
  }

}
