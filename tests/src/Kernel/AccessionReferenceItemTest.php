<?php

namespace Drupal\Tests\accession_reference\Kernel;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the new entity API for the accession_reference field type.
 *
 * @group accession_reference
 */
class AccessionReferenceItemTest extends FieldKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['accession_reference', 'node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a accession_reference field storage and field for validation.
    FieldStorageConfig::create([
      'field_name' => 'field_test',
      'entity_type' => 'entity_test',
      'type' => 'accession_reference',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_test',
      'bundle' => 'entity_test',
      'default_value' => [0 => ['value' => '6789', 'sub_value' => '2333']],
    ])->save();
  }

  /**
   * Tests using entity fields of the accession_reference field type.
   */
  public function testTestItem() {
    // Verify entity creation.
    $entity = EntityTest::create();
    $value = 6789;
    $sub_value = 2333;
    $entity->field_test = $value;
    $entity->field_test->sub_value = $sub_value;
    $entity->name->value = $this->randomMachineName();
    $entity->save();

    // Verify entity has been created properly.
    $id = $entity->id();
    $entity = EntityTest::load($id);
    $this->assertInstanceOf(FieldItemListInterface::class, $entity->field_test);
    $this->assertInstanceOf(FieldItemInterface::class, $entity->field_test[0]);
    $this->assertEquals($value, $entity->field_test->value);
    $this->assertEquals($value, $entity->field_test[0]->value);
    $this->assertEquals($sub_value, $entity->field_test->sub_value);
    $this->assertEquals($sub_value, $entity->field_test[0]->sub_value);

    // Verify changing the field value.
    $new_value = rand(1000, 9999);
    $entity->field_test->value = $new_value;
    $this->assertEquals($new_value, $entity->field_test->value);

    // Read changed entity and assert changed values.
    $entity->save();
    $entity = EntityTest::load($id);
    $this->assertEquals($new_value, $entity->field_test->value);

    // Test sample item generation.
    $entity = EntityTest::create();
    $entity->field_test->generateSampleItems();
    $this->entityValidateAndSave($entity);
  }

}
