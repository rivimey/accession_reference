<?php

namespace Drupal\accession_reference\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Template\AttributeArray;


/**
 * Provides a datelist element.
 *
 * @FormElement("accession_reference_widget")
 */
class AccessionReference extends FormElement implements TrustedCallbackInterface {

  public function getInfo() {
    $class = static::class;
    return [
      '#pre_render' => [
        [$class, 'preRenderAccession'],
      ],
      '#element_validate' => [
        [$class, 'validateAccessionRef'],
      ],

      'item' => [
        'groupref' => [],
        'itemref' => [],
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
   * Prepares a #type 'accession_reference' render element for accession_reference_widget.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #main, #sub, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for accession_reference_widget.html.twig.
   */
  public static function preRenderAccession($element) {

    $element['groupref']['#attributes']['type'] = 'text';
    $element['itemref']['#attributes']['type'] = 'text';

    Element::setAttributes($element['groupref'], ['id', 'name', 'pattern', 'size', 'placeholder', '#title' => 'tip']);
    Element::setAttributes($element['itemref'], ['id', 'name', 'pattern', 'size', 'placeholder', '#title' => 'tip']);

    return $element;
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
  public static function validateAccessionRef(&$element, FormStateInterface $form_state, &$complete_form) {
  }

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
    $callbacks[] = 'preRenderAccession';
    $callbacks[] = 'validateAccessionRef';
    return $callbacks;
  }
}
