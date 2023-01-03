<?php

namespace Drupal\accession_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
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
        'placeholder1' => '',
        'placeholder2' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['placeholder1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Main Placeholder'),
      '#default_value' => $this->getSetting('placeholder1'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value.'),
    ];
    $element['placeholder2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sub Placeholder'),
      '#default_value' => $this->getSetting('placeholder2'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value.'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $placeholder1 = $this->getSetting('placeholder1');
    if (!empty($placeholder1)) {
      $summary[] = $this->t('Main Placeholder: @placeholder', ['@placeholder1' => $placeholder1]);
    }
    $placeholder2 = $this->getSetting('placeholder2');
    if (!empty($placeholder2)) {
      $summary[] = $this->t('Sub Placeholder: @placeholder', ['@placeholder2' => $placeholder2]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $value = $items[$delta]->value ?? NULL;
    $value_sub = $items[$delta]->value_sub ?? NULL;

    $settings = $this->getFieldSettings();

    $value_min = $value_sub_min = 0;
    $digits_min = $digits_sub_min = 1;
    $value_max = 9_999;
    $digits_max = 4;
    $value_sub_max = 9_999;
    $digits_sub_max = 4;

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

    $element['accession_reference'] = [
      '#type' => 'fieldset',
      '#title' => $element['#title'],
      '#required' => $element['#required'] ?? FALSE,
      '#description' => $element['#description'] ?? '',
      '#description__display' => $element['#description_display'] ?? 'after',
      '#attributes' => [
        'class' => ['container-inline', 'fieldset-no-legend']
      ],
      '#attached' => [
        'library' => [
          'accession_reference/accession_reference',
        ],
      ],
    ];

    $element['accession_reference']['value'] = [
      '#type' => 'textfield',
      '#default_value' => $value ?? '',
      '#alt' => "Range: $value_min - $value_max",
      '#size' => $digits_max+1,
      '#placeholder' => $this->getSetting('placeholder1') ?? '',
      '#attributes' => [
        'data-accession-value' => TRUE,
        'pattern' => '\d{'.$digits_min.','.$digits_max.'}',
        'spellcheck' => 'false',
      ],
    ];

    $element['accession_reference']['separator'] = [
      '#markup' => '<span class="sep">'.$settings['separator'].'</span>',
    ];

    $element['accession_reference']['value_sub'] = [
      '#type' => 'textfield',
      '#default_value' => $value_sub ?? '',
      '#alt' => "Range: $value_sub_min - $value_sub_max",
      '#size' => $digits_sub_max+1,
      '#placeholder' => $this->getSetting('placeholder2') ?? '',
      '#attributes' => [
        'data-accession-subvalue' => TRUE,
        'pattern' => '\d{'.$digits_sub_min.','.$digits_sub_max.'}',
        'spellcheck' => 'false',
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
