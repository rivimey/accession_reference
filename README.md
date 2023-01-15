# Accession Reference field type

A Drupal project implementing a field type for Accession References, a museum term describing 
the ID used to label a specific item in the museum's collection.

This particular field implements an accession reference with two text fields, both of which are
numeric, with a separator character (default '/') between them. While this could perhaps have
been done using e.g. the double_field project, a new type enables additional functionality
such as specifying min/max and, in general, a more complete understanding of the field.

The project provides:

- A field type 'accession_reference'.
- A form Element type 'accession_reference_widget'.
- A field widget.
- A field formatter.

## Compatibility
Developed on Drupal 9, will work on Drupal 10.

## Author
Written by R.Ivimey-Cook (c) 2023.
