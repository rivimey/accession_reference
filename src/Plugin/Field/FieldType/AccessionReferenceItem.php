<?php

namespace Drupal\accession_reference\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Defines the 'accession reference' field type.
 *
 * @FieldType(
 *   id = "accession_reference",
 *   label = @Translation("Accession Reference"),
 *   description = @Translation("This field stores an accession reference in the database as an integer pair."),
 *   category = @Translation("Number"),
 *   default_widget = "accession_reference",
 *   default_formatter = "accession_reference"
 * )
 * // also list_class=, constraints=
 */
class AccessionReferenceItem extends FieldItemBase implements AccessionReferenceItemInterface
{

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'separator' => '/',
      ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->value) && empty($this->sub_value);
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
            'minMessage' => $this->t('%name: the sub value may be no less than %min.',
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
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'int',
          'description' => 'The group number of the reference.',
          'unsigned' => TRUE,
          // Valid size values: 'tiny', 'small', 'medium', 'normal' and 'big'.
          // For instance, supply 'big' as a value to produce a 'bigint' type.
          'size' => 'normal',
          'default' => 0,
        ],
        'sub_value' => [
          'type' => 'int',
          'description' => 'The item number of the reference.',
          'unsigned' => TRUE,
          // Valid size values: 'tiny', 'small', 'medium', 'normal' and 'big'.
          // For instance, supply 'big' as a value to produce a 'bigint' type.
          'size' => 'normal',
          'default' => 0,
        ],
        'options' => [
          'description' => 'Serialized array of options',
          'type' => 'blob',
          'size' => 'normal',
          'serialize' => TRUE,
        ],
      ],
      'indexes' => [
        'value_sub_value' => ['value', 'sub_value'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);

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

    return $elements;
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

    $properties['options'] = MapDataDefinition::create()
      ->setLabel(new TranslatableMarkup('Options'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   * @throws \Exception
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $min = $field_definition->getSetting('main_min') ?: 1000;
    $max = $field_definition->getSetting('main_max') ?: 9000;
    $values['value'] = random_int($min, $max);

    $min = $field_definition->getSetting('sub_min') ?: 1;
    $max = $field_definition->getSetting('sub_max') ?: 999;
    $values['sub_value'] = random_int($min, $max);

    return $values;
  }

}
