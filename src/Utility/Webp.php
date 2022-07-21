<?php

declare(strict_types=1);

namespace Drupal\omnipedia_media\Utility;

use Drupal\Component\Utility\UrlHelper;

/**
 * WebP utility class.
 */
class Webp {

  /**
   * Convert a provided image URL to a WebP URL.
   *
   * Note that this requires the WebP module to be installed and configured,
   * otherwise the URL will return a 404.
   *
   * Annoyingly, trying to build a \Drupal\Core\Url object with an image style
   * path seems to result in the path being cut off, either due to some
   * parsing or path segment limit. We have to assemble the new URL ourselves
   * instead.
   *
   * @param string $url
   *   The URL to convert. Must point to a file with a file extension.
   *
   * @return string|false
   *   A string URL or false if it couldn't be converted.
   */
  public static function imageUrlToWebp(string $url): string|bool {

    /** @var array */
    $urlParts = UrlHelper::parse($url);

    /** @var string[] The path split by a period. Variable needed to avoid PHP notice about needing to pass by reference to \end() */
    $pathSplit = \explode('.', \strtolower($urlParts['path']));

    /** @var string */
    $originalExtension = \end($pathSplit);

    // If \explode() doesn't find '.', it will return an array containing the
    // full original string, so bail here if so because that means this isn't
    // a valid file path.
    if ($originalExtension === \strtolower($urlParts['path'])) {
      return false;
    }

    /** @var string */
    $webpSrc = \substr(
      $urlParts['path'], 0, \mb_strlen($originalExtension) * -1
    ) . 'webp';

    if (!empty($urlParts['query'])) {
      $webpSrc .= '?' . UrlHelper::buildQuery($urlParts['query']);
    }

    if (!empty($urlParts['fragment'])) {
      $webpSrc .= '#' . $urlParts['fragment'];
    }

    return $webpSrc;

  }

}
