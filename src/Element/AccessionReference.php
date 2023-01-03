<?php

namespace Drupal\accession_reference\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a datelist element.
 *
 * @FormElement("accession_reference_widget")
 */
class AccessionReference extends FormElement {

  public function getInfo() {
    $class = static::class;
    return [
      '#pre_render' => [
        [$class, 'preRenderAccession'],
      ],
      '#element_validate' => [
        [$class, 'validateAccessionRef'],
      ],
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#default_value' => NULL,
      '#attached' => [
//        'library' => ['address/form'],
      ],
      '#theme' => 'accession_reference_widget',
      '#theme_wrappers' => ['container'],
    ];
  }

  /**
   * Prepares a #type 'accession_reference' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #main, #sub, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderAccession($element) {
    $element['#attributes']['type'] = 'range';
    Element::setAttributes($element, ['id', 'name', 'main', 'sub']);
    static::setAttributes($element, ['form-range']);

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
   */
  public static function setAttributes(&$element, $class = []) {
    parent::setAttributes($element, $class);
  }

  /**
   * {@inheritdoc}
   *
   * If it is valid, the ref is set in the form.
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state){
    // TODO: Implement valueCallback() method.
  }

}
