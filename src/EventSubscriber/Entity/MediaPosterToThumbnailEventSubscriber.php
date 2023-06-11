<?php

declare(strict_types=1);

namespace Drupal\omnipedia_media\EventSubscriber\Entity;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\file\FileInterface;
use Drupal\file\FileStorageInterface;
use Drupal\media\MediaInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to set a media entity's thumbnail to a provided poster.
 *
 * This assigns a media entity's thumbnail field the file entity of a provided
 * poster image, added via a poster field. If the poster is removed, the media
 * entity's thumbnail is reverted back to the default for its media source.
 *
 * This allows users to edit the thumbnail for media sources that don't provide
 * that directly, e.g. local video, without having to edit the media library
 * views to show the poster field. The poster image can also be output to the
 * <video> element as the 'poster' attribute.
 *
 * @see https://www.drupal.org/project/drupal/issues/2954834
 *   Drupal core issue to add poster image support to video fields. Once this is
 *   released, our approach here will be redundant.
 */
class MediaPosterToThumbnailEventSubscriber implements EventSubscriberInterface {

  /**
   * The media entity poster field name.
   */
  protected const POSTER_FIELD_NAME = 'field_poster';

  /**
   * The Drupal configuration factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The Drupal file entity storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected FileStorageInterface $fileStorage;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration factory service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
   */
  public function __construct(
    ConfigFactoryInterface      $configFactory,
    EntityTypeManagerInterface  $entityTypeManager
  ) {
    $this->configFactory  = $configFactory;
    $this->fileStorage    = $entityTypeManager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'onEntityPreSave',
    ];
  }

  /**
   * Get the default thumbnail for a provided media entity.
   *
   * This is essentially copied from several protected methods on the media
   * entity class to replicate its behaviour.
   *
   * @param \Drupal\media\MediaInterface $mediaEntity
   *   The media entity to get the default thumbnail for.
   *
   * @return \Drupal\file\FileInterface|null
   *   A file entity or null if one couldn't be created or loaded.
   *
   * @see \Drupal\media\Entity\Media::getDefaultThumbnailUri()
   * @see \Drupal\media\Entity\Media::loadThumbnail()
   * @see \Drupal\media\Entity\Media::preSave()
   *
   * @todo Move this to a service and expose it as a public method?
   */
  protected function getDefaultThumbnail(
    MediaInterface $mediaEntity
  ): ?FileInterface {

    /** @var string */
    $defaultThumbnailFilename = $mediaEntity->getSource()
      ->getPluginDefinition()['default_thumbnail_filename'];

    /** @var string */
    $defaultUri = $this->configFactory->get('media.settings')
      ->get('icon_base_uri') . '/' . $defaultThumbnailFilename;

    $values = [
      'uri' => $defaultUri,
    ];

      /** @var \Drupal\file\FileInterface[] */
    $existing = $this->fileStorage->loadByProperties($values);

    if ($existing) {
      /** @var \Drupal\file\FileInterface */
      $file = \reset($existing);

    } else {

      /** @var \Drupal\file\FileInterface */
      $file = $this->fileStorage->create($values);

      if ($owner = $mediaEntity->getOwner()) {
        $file->setOwner($owner);
      }

      $file->setPermanent();

      $file->save();

    }

    return $file;

  }

  /**
   * Entity presave event handler.
   *
   * @param Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event object.
   */
  public function onEntityPreSave(EntityPresaveEvent $event): void {

    /** @var \Drupal\Core\Entity\EntityInterface */
    $entity = $event->getEntity();

    // Bail if this is not a media entity, if it is but it has a 'poster' base
    // field (which could be Drupal core support for this), or it doesn't have
    // our poster field.
    if (
      $entity->getEntityTypeId() !== 'media' ||
      $entity->hasField('poster') ||
      !$entity->hasField(self::POSTER_FIELD_NAME)
    ) {
      return;
    }

    /** @var \Drupal\Core\Entity\EntityInterface|null */
    $originalEntity = $event->getOriginalEntity();

    // If the poster field isn't empty, get the first field item and save that
    // to the first thumbnail field item.
    if (
      !$entity->get(self::POSTER_FIELD_NAME)->isEmpty() &&
      !$entity->get(self::POSTER_FIELD_NAME)->get(0)->isEmpty()
    ) {
      $entity->get('thumbnail')->set(0, $entity->get('field_poster')->get(0));

    // Otherwise, if the original entity had a poster field but the poster was
    // removed, set the thumbnail to the media source's default.
    } else if (
      \is_object($originalEntity) &&
      !$originalEntity->get(self::POSTER_FIELD_NAME)->isEmpty() &&
      !$originalEntity->get(self::POSTER_FIELD_NAME)->get(0)->isEmpty()
    ) {

      $entity->get('thumbnail')->target_id =
        $this->getDefaultThumbnail($entity)->id();

    }

  }

}
