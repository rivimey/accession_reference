<?php

namespace Drupal\Tests\accession_reference\Functional;

use Behat\Mink\Exception\ExpectationException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\field\Entity\FieldConfig;
use Drupal\accession_reference\Plugin\Field\FieldType\AccessionReferenceItem;
use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the creation of accession_reference fields.
 *
 * @group accession_reference
 */
class AccessionReferenceFieldTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'field',
    'node',
    'accession_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permission to create articles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * {@inheritdoc}
   * @throws EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'article']);
    $this->webUser = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
    ]);
    $this->drupalLogin($this->webUser);

    // Add the accession_reference field to the article content type.
    FieldStorageConfig::create([
      'field_name' => 'field_accession_reference',
      'entity_type' => 'node',
      'type' => 'accession_reference',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_accession_reference',
      'label' => 'AccessionReference Number',
      'entity_type' => 'node',
      'bundle' => 'article',
    ])->save();

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');
    $display_repository->getFormDisplay('node', 'article')
      ->setComponent('field_accession_reference', [
        'type' => 'accession_reference',
        'settings' => [
          'groupref_placeholder' => '1234',
          'itemref_placeholder' => '4321',
        ],
      ])
      ->save();

    $display_repository->getViewDisplay('node', 'article')
      ->setComponent('field_accession_reference', [
        'type' => 'accession_reference',
        'weight' => 1,
      ])
      ->save();
  }

  /**
   * Tests to confirm the widget is setup.
   *
   * @covers \Drupal\accession_reference\Plugin\Field\FieldWidget\AccessionReferenceWidget::formElement
   */
  public function testAccessionReferenceWidget() {
    $this->drupalGet('node/add/article');
    $this->assertSession()->fieldValueEquals("field_accession_reference[0][value]", '');
    $this->assertSession()->responseContains('placeholder="1234"');
  }

  /**
   * Tests the accession_reference formatter.
   *
   * @covers       \Drupal\accession_reference\Plugin\Field\FieldFormatter\AccessionReferenceFormatter::viewElements
   *
   * @dataProvider providerAccessions
   * @throws ExpectationException
   */
  public function testAccessionReferenceFormatter($input, $expected) {
    [$val, $sub] = $input;

    // Test basic entry of accession_reference field.
    $edit = [
      'title[0][value]' => $this->randomMachineName(),
      'field_accession_reference[0][groupref]' => $val,
      'field_accession_reference[0][itemref]' => $sub,
    ];

    $this->drupalGet('node/add/article');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->responseContains($expected);
  }

  /**
   * Provides the Accessions to check and expected results.
   */
  public function providerAccessions() {
    return [
      'standard reference' => [['1234', '5678'], '1234/5678'],
    ];
  }

}
