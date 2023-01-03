<?php

namespace Drupal\accession_reference\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Defines the 'accession referende' field interface.
 **/
interface AccessionReferenceItemInterface extends FieldItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEmpty();

    /**
     * {@inheritdoc}
     */
    public function getConstraints();

    /**
     * {@inheritdoc}
     */
    public function setValue($values, $notify = TRUE);

    /**
     * {@inheritdoc}
     */
    public function getValue();
}
