<?php
// $Id$

/**
 * @file
 * page callback for add book for the bookLibrary module
 */

/**
 * add isbn number form
 */
 
function bookLibrary_add_form() {
  $form['isbn'] = array(
    '#title' => t('Isbn Book number'),
  	'#type' => 'textfield',
    '#description' => t('Add the isbn number for the book that will be displayed in the recommended reading'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Enter'),
  );
}
