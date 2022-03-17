<?php declare(strict_types=1);

namespace Drupal\omnipedia_media\Utility;

/**
 * srcset attribute utility class.
 */
class SrcSet {

  /**
   * Parse a srcset string into an array containing URLs and descriptors.
   *
   * @param string $srcset
   *   The srcset string to attempt to parse.
   *
   * @return array[]
   *   An array containing zero or more arrays with 'url' and 'descriptor' keys.
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#attr-srcset
   *
   * @see https://github.com/sindresorhus/srcset
   *   JavaScript parser that may be of use in the future to port to PHP for
   *   more robust parsing.
   */
  public static function parse(string $srcset): array {

    /** @var array[] */
    $items = [];

    foreach (\explode(',', $srcset) as $value) {

      $split = \explode(' ', \trim($value), 2);

      $items[] = [
        'url'         => $split[0],
        'descriptor'  => $split[1],
      ];

    }

    return $items;

  }

}
