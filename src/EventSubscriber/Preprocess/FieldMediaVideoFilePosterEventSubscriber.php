<?php

namespace Drupal\omnipedia_media\EventSubscriber\Preprocess;

use Drupal\preprocess_event_dispatcher\Event\FieldPreprocessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to add the poster to media video file fields.
 *
 * This alters 'field_media_video_file' fields to include a 'poster' attribute,
 * if a poster field is found on the same media entity. If the media entity has
 * a 'poster' base field, this will do nothing as that could indicate Drupal
 * core now provides this functionality.
 *
 * @see https://www.drupal.org/project/drupal/issues/2954834
 *   Drupal core issue to add poster image support to video fields. Once this is
 *   released, our approach here will be redundant.
 */
class FieldMediaVideoFilePosterEventSubscriber implements EventSubscriberInterface {

  /**
   * The media entity poster field name.
   */
  protected const POSTER_FIELD_NAME = 'field_poster';

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FieldPreprocessEvent::name() => 'onPreprocessField',
    ];
  }

  /**
   * Field preprocess event handler.
   *
   * @param \Drupal\preprocess_event_dispatcher\Event\FieldPreprocessEvent $event
   *   The event object.
   */
  public function onPreprocessField(FieldPreprocessEvent $event): void {

    /* @var \Drupal\preprocess_event_dispatcher\Event\Variables\FieldEventVariables $variables */
    $variables = $event->getVariables();

    if ($variables->get('field_name') !== 'field_media_video_file') {
      return;
    }

    /** @var \Drupal\media\MediaInterface */
    $mediaEntity = $variables->getElement()['#object'];

    // Bail if the media entity has a 'poster' base field (which could be Drupal
    // core support for this), or it doesn't have our poster field, or it does
    // have our poster field but the field is empty.
    if (
      $mediaEntity->hasField('poster') ||
      !$mediaEntity->hasField(self::POSTER_FIELD_NAME) ||
      $mediaEntity->get(self::POSTER_FIELD_NAME)->isEmpty() ||
      $mediaEntity->get(self::POSTER_FIELD_NAME)->get(0)->isEmpty()
    ) {
      return;
    }

    /** @var array */
    $items = $variables->getItems();

    /** @var \Drupal\file\FileInterface */
    $posterFileEntity = $mediaEntity->get(self::POSTER_FIELD_NAME)
      ->referencedEntities()[0];

    /** @var string */
    $posterFileUrl = $posterFileEntity->createFileUrl();

    foreach ($items as $key => &$item) {

      // Skip items that already have a 'poster' attribute.
      if ($item['content']['#attributes']->hasAttribute('poster')) {
        continue;
      }

      $item['content']['#attributes']->setAttribute('poster', $posterFileUrl);

    }

  }

}
