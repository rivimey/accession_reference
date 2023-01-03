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
        'main_min' => '1',
        'main_max' => '9999',
        'main_placeholder' => '',
        'sub_min' => '1',
        'sub_max' => '9999',
        'sub_placeholder' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['main_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Main minimum'),
      '#default_value' => $this->getSetting('main_min'),
      '#description' => $this->t('The minimum value that should be allowed in the main field.'),
    ];
    $element['main_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Main maximum'),
      '#default_value' => $this->getSetting('main_max'),
      '#description' => $this->t('The maximum value that should be allowed in the main field.'),
    ];
    $element['sub_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Sub Minimum'),
      '#default_value' => $this->getSetting('sub_min'),
      '#description' => $this->t('The minimum value that should be allowed in the sub field. Leave blank for no minimum.'),
    ];
    $element['sub_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Sub Maximum'),
      '#default_value' => $this->getSetting('sub_max'),
      '#description' => $this->t('The maximum value that should be allowed in the sub field. Leave blank for no maximum.'),
    ];

    $element['main_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Main Placeholder'),
      '#default_value' => $this->getSetting('main_placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value.'),
    ];
    $element['sub_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sub Placeholder'),
      '#default_value' => $this->getSetting('sub_placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $main_min = $this->getSetting('main_min');
    $summary[] = $this->t('Main minimum: @main_min', ['@main_min' => $main_min]);
    $main_max = $this->getSetting('main_max');
    $summary[] = $this->t('Main maximum: @main_max', ['@main_max' => $main_max]);
    $sub_min = $this->getSetting('sub_min');
    $summary[] = $this->t('Sub minimum: @sub_min', ['@sub_min' => $sub_min]);
    $sub_max = $this->getSetting('sub_max');
    $summary[] = $this->t('Sub maximum: @sub_max', ['@sub_max' => $sub_max]);

    $placeholder1 = $this->getSetting('main_placeholder');
    $summary[] = $this->t('Main Placeholder: @main_placeholder', ['@main_placeholder' => $placeholder1]);
    $placeholder2 = $this->getSetting('sub_placeholder');
    $summary[] = $this->t('Sub Placeholder: @sub_placeholder', ['@sub_placeholder' => $placeholder2]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $value = $items[$delta]->value ?? NULL;
    $value_sub = $items[$delta]->value_sub ?? NULL;

    $storagesettings = $this->fieldDefinition->getFieldStorageDefinition();
    $settings = $this->getFieldSettings();

    $value_min = $value_sub_min = 0;
    $digits_min = $digits_sub_min = 1;
    $value_max = 9_999;
    $digits_max = 4;
    $value_sub_max = 9_999;
    $digits_sub_max = 4;

    $settings += [
      'main_min' => '1',
      'main_max' => '9999',
      'main_placeholder' => '',
      'sub_min' => '1',
      'sub_max' => '9999',
      'sub_placeholder' => '',
    ];

    if (is_numeric($settings['main_min'])) {
      $value_min = $settings['main_min'];
      $digits_min = strlen((string) $settings['main_min']);
    }
    if (is_numeric($settings['main_max'])) {
      $value_max = $settings['main_max'];
      $digits_max = strlen((string) $settings['main_max']);
    }
    if (is_numeric($settings['sub_min'])) {
      $value_sub_min = $settings['sub_min'];
      $digits_sub_min = strlen((string) $settings['sub_min']);
    }
    if (is_numeric($settings['sub_max'])) {
      $value_sub_max = $settings['sub_max'];
      $digits_sub_max = strlen((string) $settings['sub_max']);
    }

    $element['value'] = [
      '#type' => 'accession_reference_widget',

      '#title' => $element['#title'] ?? '',
      '#required' => $element['#required'] ?? FALSE,
      '#description' => $element['#description'] ?? '',
      '#description__display' => $element['#description_display'] ?? 'after',

      '#main_value' => $value ?? '',
      '#main_tip' => $this->t("Range: @min - @max", ['@min' => $value_min, '@max' => $value_max]),
      '#main_size' => $digits_max,
      '#main_placeholder' => $this->getSetting('main_placeholder') ?? '',
      '#main_pattern' => '\d{'.$digits_min.','.$digits_max.'}',

      '#separator' => $storagesettings->getSetting('separator'),

      '#sub_value' => $value_sub ?? '',
      '#sub_tip' => "Range: $value_sub_min - $value_sub_max",
      '#sub_size' => $digits_sub_max,
      '#sub_placeholder' => $this->getSetting('sub_placeholder') ?? '',
      '#sub_pattern' => '\d{'.$digits_sub_min.','.$digits_sub_max.'}',

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
