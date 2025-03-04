<?php

declare(strict_types=1);

namespace Drupal\omnipedia_media\Plugin\EntityUsage\Track;

use Drupal\entity_usage\Plugin\EntityUsage\Track\TextFieldEmbedBase;
use function array_unique;
use function count;
use function reset;
use function trim;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tracks usage of embedded Omnipedia media.
 *
 * @EntityUsageTrack(
 *   id           = "omnipedia_media_embed",
 *   label        = @Translation("Omnipedia: Media Embed"),
 *   description  = @Translation(
 *     "Tracks relationships created with Omnipedia's <code>&lt;media&gt;</code> elements in formatted text fields.",
 *   ),
 *   field_types  = {"text", "text_long", "text_with_summary"},
 * )
 */
class OmnipediaMediaEmbed extends TextFieldEmbedBase {

  /**
   * {@inheritdoc}
   */
  public function parseEntitiesFromText($text) {

    if ($this->isEntityTypeTracked('media') === false) {
      return [];
    }

    /** @var \Symfony\Component\DomCrawler\Crawler */
    $rootCrawler = new Crawler(
      // The <div> is to prevent the PHP DOM automatically wrapping any
      // top-level text content in a <p> element.
      '<div id="omnipedia-media-embed-root">' . $text . '</div>',
    );

    $mediaNames = [];

    foreach ($rootCrawler->filter('media[name]') as $element) {

      $mediaNames[] = trim($element->getAttribute('name'));

    }

    // These reference media via attributes, so they need special handling.
    foreach ($rootCrawler->filter(
      'main-page featured-article[media], main-page news[media]',
    ) as $element) {

      $mediaNames[] = trim($element->getAttribute('media'));

    }

    if (empty($mediaNames)) {
      return [];
    }

    $entities = [];

    /** @var \Drupal\Core\Entity\EntityStorageInterface The Drupal media entity storage. */
    $mediaStorage = $this->entityTypeManager->getStorage('media');

    foreach (array_unique($mediaNames) as $name) {

      // Try to find any media with this name.
      /** @var string[] Zero or more media entity IDs, keyed by their most recent revision ID. */
      $queryResult = ($mediaStorage->getQuery())
        ->condition('name', $name)
        ->accessCheck(false) // @todo Does this need to restrict access?
        ->execute();

      if (count($queryResult) === 0) {
        continue;
      }

      // Load the media entity so we can retrieve its UUID. There doesn't seem
      // to be a simple way to get this without loading the entity, as entity
      // queries don't return anything other than entity IDs and their revision
      // IDs. We could probably resort to taking the entity query results and
      // performing a database query directly, but that would bypass the entity
      // system and thus might be a bad idea for relatively little performance
      // gains.
      /** @var \Drupal\media\MediaInterface */
      $mediaEntity = $mediaStorage->load(reset($queryResult));

      if ($mediaEntity === null) {
        continue;
      }

      $entities[$mediaEntity->uuid()] = 'media';

    }

    return $entities;

  }

}
