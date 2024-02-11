<?php

namespace Olmec\OlmecNotepress\Util;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add here functions that are not context specific and
 * would not perticularly fit to a class or a trait
 */

/**
  * Use this to trim texts to a certain
  * number of characters, but without leaving
  * word breaks
  *
  * @param string $string
  * @param integer $limit
  * @return $string
  */
function excerptFromText($string, $limit = 120) {
    if (strlen($string) <= $limit) return $string;
    $cutMark = substr($string, 0, $limit);
    $lastSpace = strrpos($cutMark, ' ');
    return $lastSpace !== false ? substr($cutMark, 0, $lastSpace) : $cutMark;
}
