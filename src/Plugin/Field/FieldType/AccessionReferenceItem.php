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
    return empty($this->groupref) && empty($this->itemref);
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $settings = $this->getSettings();
    $label = $this->getFieldDefinition()->getLabel();

    if (isset($settings['groupref_min']) && $settings['groupref_min'] !== '') {
      $groupref_min = $settings['groupref_min'];
      $constraints[] = $constraint_manager->create('ComplexData', [
        'groupref' => [
          'Range' => [
            'min' => $groupref_min,
            'minMessage' => $this->t('%name: the group ref may be no less than %min.',
              ['%name' => $label, '%min' => $groupref_min]),
          ],
        ],
      ]);
    }

    if (isset($settings['itemref_min']) && $settings['itemref_min'] !== '') {
      $itemref_min = $settings['itemref_min'];
      $constraints[] = $constraint_manager->create('ComplexData', [
        'itemref' => [
          'Range' => [
            'min' => $itemref_min,
            'minMessage' => $this->t('%name: the item ref may be no less than %min.',
              ['%name' => $label, '%min' => $itemref_min]),
          ],
        ],
      ]);
    }

    if (isset($settings['groupref_max']) && $settings['groupref_max'] !== '') {
      $groupref_max = $settings['groupref_max'];
      $constraints[] = $constraint_manager->create('ComplexData', [
        'groupref' => [
          'Range' => [
            'max' => $groupref_max,
            'maxMessage' => $this->t('%name: the group ref may be no greater than %max.',
              ['%name' => $label, '%max' => $groupref_max]),
          ],
        ],
      ]);
    }

    if (isset($settings['itemref_max']) && $settings['itemref_max'] !== '') {
      $itemref_max = $settings['itemref_max'];
      $constraints[] = $constraint_manager->create('ComplexData', [
        'itemref' => [
          'Range' => [
            'max' => $itemref_max,
            'maxMessage' => $this->t('%name: the item ref may be no greater than %max.',
              ['%name' => $label, '%max' => $itemref_max ]),
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
        'groupref' => [
          'type' => 'int',
          'description' => 'The group number of the reference.',
          'unsigned' => TRUE,
          // Valid size values: 'tiny', 'small', 'medium', 'normal' and 'big'.
          // For instance, supply 'big' as a value to produce a 'bigint' type.
          'size' => 'normal',
          'default' => 0,
        ],
        'itemref' => [
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
        'value' => ['groupref', 'itemref'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::fieldSettingsForm($form, $form_state);

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
    $properties['groupref'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Main'))
      ->setRequired(TRUE);

    $properties['itemref'] = DataDefinition::create('integer')
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
    $min = $field_definition->getSetting('groupref_min') ?: 1000;
    $max = $field_definition->getSetting('groupref_max') ?: 9000;
    $values['groupref'] = random_int($min, $max);

    $min = $field_definition->getSetting('itemref_min') ?: 1;
    $max = $field_definition->getSetting('itemref_max') ?: 999;
    $values['itemref'] = random_int($min, $max);

    return $values;
  }

}
