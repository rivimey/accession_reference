<?php

namespace Drupal\accession_reference\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\CompositeFormElementTrait;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;


/**
 * Provides an accession reference widget element.
 *
 * @FormElement("accession_reference_widget")
 */
class AccessionReferenceWidget extends FormElement implements TrustedCallbackInterface {

  use CompositeFormElementTrait;

  public function getInfo() {
    $class = static::class;
    return [
//      '#pre_render' => [
//        [$class, 'preRenderAccession'],
//      ],
//      '#element_validate' => [
//        [$class, 'validateAccessionRef'],
//      ],

      'item' => [
        'groupref' => [],
        'itemref' => [],
      ],
      '#process' => [
        [$class, 'processAccession'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
//        [$class, 'groupElements'],
        [$class, 'preRenderGroup'],
      ],

      '#input' => TRUE,
      '#multiple' => FALSE,
      '#default_value' => ['groupref' => '', 'itemref' => ''],
      '#attached' => [],
      '#theme' => 'accession_reference_widget',
      '#theme_wrappers' => ['container'],
    ];
  }

  /**
   * Prepares 'accession_reference' element for accession_reference_widget twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *
   * @return array
   *   The $element with prepared variables ready for accession_reference_widget.html.twig.
   */
  public static function processAccession($element) {
    //$element = self::preRenderCompositeFormElement($element);
    $element['#tree']  = TRUE;

    $element['groupref'] = [
      '#type' => 'text',
      '#default_value' => $element['#default_value']['groupref'] ?? '',
      '#title' => $element['#groupref_tip'] ?? '',
      '#size' => 6,
      '#required' => self::propOrSubProp('required', 'groupref', $element),
      '#disabled' => self::propOrSubProp('disabled', 'groupref', $element),
      '#placeholder' => $element['#groupref_placeholder'] ?? '',
      '#pattern' => '\d+',
      '#attributes' => [],
    ];

    $element['separator'] = [
      '#value' => $element['#separator']
    ];

    $element['itemref'] = [
      '#type' => 'text',
      '#default_value' => $element['#default_value']['itemref'] ?? '',
      '#title' => $element['#itemref_tip'] ?? '',
      '#required' => self::propOrSubProp('required', 'itemref', $element),
      '#disabled' => self::propOrSubProp('disabled', 'itemref', $element),
      '#size' => 6,
      '#placeholder' => $element['#itemref_placeholder'] ?? '',
      '#pattern' => '\d+',
      '#attributes' => [],
    ];

    $map = [
      'id',
      'name',
      'type',
      'title',
      'size',
      'disabled',
      'pattern',
      'required',
    ];

    Element::setAttributes($element['groupref'], $map);
    Element::setAttributes($element['itemref'], $map);

    static::setAttributes($element['groupref'], ['form-accession--groupref']);
    static::setAttributes($element['itemref'], ['form-accession--itemref']);
    static::setAttributes($element, ['form-accession']);

    return $element;
  }

  protected static function propOrSubProp(string $prop, string $subfld, array $arr): bool {
    return (
         (isset($arr["#$prop"]) && $arr["#$prop"])
      || (isset($arr["#{$subfld}_$prop"]) && $arr["#{$subfld}_$prop"])
      );
  }

  /**
   * Validation callback for a accession_reference element.
   *
   * @param array $element
   *   The element being processed.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
//  public static function validateAccessionRef(&$element, FormStateInterface $form_state, &$complete_form) {
//  }

  /**
   * {@inheritdoc}
   *
   * If it is valid, the ref is set in the form.
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state){
    $submission = ['groupref' => '', 'itemref' => ''];
    if ($input === FALSE) {
      if (empty($element['#default_value'])) {
        $element['#default_value'] = [];
      }
      return $element['#default_value'] + ['groupref' => '', 'itemref' => ''];
    }

    // Throw out all invalid array keys.
    foreach ($submission as $allowed_key => $default) {
      if (isset($input[$allowed_key]) && is_scalar($input[$allowed_key])) {
        $submission[$allowed_key] = (string) $input[$allowed_key];
      }
    }
    return $submission;
  }

  /**
   * @return array|string[]
   */
  public static function trustedCallbacks()
  {
    $callbacks = [];
//    $callbacks[] = 'preRenderAccession';
//    $callbacks[] = 'validateAccessionRef';
    return $callbacks;
  }
}
