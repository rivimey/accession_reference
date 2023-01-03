<?php

namespace Drupal\accession_reference\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the 'accession referende' field type.
 *
 * @FieldType(
 *   id = "accession_reference",
 *   label = @Translation("Accession Reference"),
 *   description = @Translation("This field stores an accession reference in the database as an integer pair."),
 *   category = @Translation("Number"),
 *   default_widget = "accession_reference",
 *   default_formatter = "number_accession"
 * )
 */
class AccessionReferenceItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'main_min' => '',
        'main_max' => '',
        'sub_min' => '',
        'sub_max' => '',
        'separator' => '/',
      ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
//  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data): array {
//
//    $settings = $this->getSettings();
//
//    return $element;
//  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();

    $element['main_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Main minimum'),
      '#default_value' => $settings['main_min'],
      '#description' => $this->t('The minimum value that should be allowed in the main field.'),
    ];
    $element['main_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Main maximum'),
      '#default_value' => $settings['main_max'],
      '#description' => $this->t('The maximum value that should be allowed in the main field.'),
    ];

    $element['sub_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Sub Minimum'),
      '#default_value' => $settings['sub_min'],
      '#description' => $this->t('The minimum value that should be allowed in the sub field. Leave blank for no minimum.'),
    ];
    $element['sub_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Sub Maximum'),
      '#default_value' => $settings['sub_max'],
      '#description' => $this->t('The maximum value that should be allowed in the sub field. Leave blank for no maximum.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if (empty($this->value) && empty($this->sub_value)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $settings = $this->getSettings();
    $label = $this->getFieldDefinition()->getLabel();

    if (isset($settings['main_min']) && $settings['main_min'] !== '') {
      $main_min = $settings['main_min'];
      $constraints[] = $constraint_manager->create('ComplexData', [
        'value' => [
          'Range' => [
            'min' => $main_min,
            'minMessage' => $this->t('%name: the value may be no less than %min.',
              ['%name' => $label, '%min' => $main_min]),
          ],
        ],
      ]);
    }

    if (isset($settings['sub_min']) && $settings['sub_min'] !== '') {
      $sub_min = $settings['sub_min'];
      $constraints[] = $constraint_manager->create('ComplexData', [
        'sub_value' => [
          'Range' => [
            'min' => $sub_min,
            'minMessage' => $this->t('%name: the value may be no less than %min.',
              ['%name' => $label, '%min' => $sub_min]),
          ],
        ],
      ]);
    }

    if (isset($settings['main_max']) && $settings['main_max'] !== '') {
      $main_max = $settings['main_max'];
      $constraints[] = $constraint_manager->create('ComplexData', [
        'main_value' => [
          'Range' => [
            'max' => $main_max,
            'maxMessage' => $this->t('%name: the value may be no greater than %max.',
              ['%name' => $label, '%max' => $main_max]),
          ],
        ],
      ]);
    }

    if (isset($settings['sub_max']) && $settings['sub_max'] !== '') {
      $sub_max = $settings['sub_max'];
      $constraints[] = $constraint_manager->create('ComplexData', [
        'sub_value' => [
          'Range' => [
            'max' => $sub_max,
            'maxMessage' => $this->t('%name: the sub value may be no greater than %max.',
              ['%name' => $label, '%max' => $sub_max ]),
          ],
        ],
      ]);
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      // Valid size values: 'tiny', 'small', 'medium', 'normal' and 'big'.
      'size' => 'normal',
      'sub_size' => 'normal',
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Main'))
      ->setRequired(TRUE);

    $properties['sub_value'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Sub'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'int',
          'unsigned' => TRUE,
          // Valid size values: 'tiny', 'small', 'medium', 'normal' and 'big'.
          // For instance, supply 'big' as a value to produce a 'bigint' type.
          'size' => 'normal',
        ],
        'sub_value' => [
          'type' => 'int',
          'unsigned' => TRUE,
          // Valid size values: 'tiny', 'small', 'medium', 'normal' and 'big'.
          // For instance, supply 'big' as a value to produce a 'bigint' type.
          'size' => 'normal',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $min = $field_definition->getSetting('main_min') ?: 1000;
    $max = $field_definition->getSetting('main_max') ?: 9000;
    $values['value'] = mt_rand($min, $max);

    $min = $field_definition->getSetting('sub_min') ?: 1;
    $max = $field_definition->getSetting('sub_max') ?: 999;
    $values['sub_value'] = mt_rand($min, $max);

    return $values;
  }

}
