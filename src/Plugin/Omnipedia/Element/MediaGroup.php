<?php

declare(strict_types=1);

namespace Drupal\omnipedia_media\Plugin\Omnipedia\Element;

use Drupal\ambientimpact_core\Utility\AttributeHelper;
use Drupal\Core\Template\Attribute;
use Drupal\omnipedia_content\Plugin\Omnipedia\Element\OmnipediaElementBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Media group element.
 *
 * @OmnipediaElement(
 *   id               = "media_group",
 *   html_element     = "media-group",
 *   render_children  = false,
 *   title            = @Translation("Media group"),
 *   description      = @Translation("Media group element.")
 * )
 */
class MediaGroup extends OmnipediaElementBase {

  /**
   * The item scale custom property name.
   *
   * @var string
   */
  protected const ITEM_SCALE_PROPERTY_NAME = '--media-group-item-scale';

  /**
   * {@inheritdoc}
   */
  public static function getTheme(): array {
    return [
      'omnipedia_media_group' => [
        'variables' => [
          'items'     => [],
          'caption'   => '',
          'align'     => 'right',
          'view_mode' => 'omnipedia_embedded_group',
        ],
        'template'  => 'omnipedia-media-group',
      ],
    ];
  }

  /**
   * Build item scale custom properties for the provided items.
   *
   * This takes the rendered markup from each item and attempts to find the
   * width and height on the <img> element, which it then uses to generate a
   * custom property containing a width to height ratio as a float.
   *
   * This is completely atomic in the sense that if even a single item's width
   * or height can't be determined, none of the items will be given the custom
   * property. This is to avoid invalid or unexpected values from potentially
   * breaking the media group layout and making it look worse and/or making
   * the images invisible/inaccessible.
   *
   * @param array &$items
   *   Array of items, each with a 'content' key and an 'attributes' key, the
   *   latter which should already be an Attribute instance.
   */
  protected function buildItemScaleProperties(array &$items): void {

    /** @var array Item dimensions, keyed by their corresponding $items keys. */
    $itemDimensions = [];

    foreach ($items as $i => &$item) {

      /** @var \Symfony\Component\DomCrawler\Crawler */
      $renderedCrawler = new Crawler($item['content']['#markup']);

      try {

        $imageCrawler = $renderedCrawler->filter('img');

        $imageWidth   = (int) $imageCrawler->attr('width');
        $imageHeight  = (int) $imageCrawler->attr('height');

      } catch (\Exception $exception) {

        return;

      }

      // Some basic validation that we have positive integers for both
      // dimensions.
      if (
        !\is_int($imageWidth)   || $imageWidth < 1 ||
        !\is_int($imageHeight)  || $imageHeight < 1
      ) {

        return;

      }

      $itemDimensions[$i] = [
        'width'   => $imageWidth,
        'height'  => $imageHeight,
      ];

    }

    // If we got to this point, loop through all of the items and set the custom
    // properties.
    foreach ($itemDimensions as $i => $values) {

      // If the 'style' attribute already exists, use it.
      if ($items[$i]['attributes']->hasAttribute('style')) {

        $itemStyleArray = AttributeHelper::parseStyleAttribute(
          $items[$i]['attributes']->offsetGet('style')
        );

      } else {

        $itemStyleArray = [];

      }

      $itemStyleArray[self::ITEM_SCALE_PROPERTY_NAME] = (
        $values['width'] / $values['height']
      );

      $items[$i]['attributes']->setAttribute(
        'style', AttributeHelper::serializeStyleArray($itemStyleArray)
      );

    }

  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(): array {
    /** @var \Symfony\Component\DomCrawler\Crawler */
    $mediaElements = $this->elements->filter('media');

    /** @var array */
    $items = [];

    foreach ($mediaElements as $mediaElement) {
      // @todo Should these be made configurable, and should these only be
      //   applied if they aren't already set by the author?
      $mediaElement->setAttribute('align', 'none');
      $mediaElement->setAttribute('style', 'frameless');
      $mediaElement->setAttribute(
        'view-mode',
        self::getTheme()['omnipedia_media_group']['variables']['view_mode']
      );

      $items[] = [
        'content' => [
          // This bypasses any further rendering, including XSS filtering -
          // which strips 'style' attributes that are needed for inline
          // max-widths on image fields to function correctly.
          //
          // @todo Is this a security risk, given that the generated markup has
          //   already been rendered in the element mananger via Drupal's
          //   renderer?
          //
          // @see \Drupal\Component\Utility\Xss::attributes()
          //   Strips 'style' attributes.
          '#printed'  => true,
          // Recursively convert and render any elements contained in this item.
          '#markup'   => $this->elementManager->convertElements(
            $mediaElement->ownerDocument->saveHTML($mediaElement)
          ),
        ],
        'attributes' => new Attribute(),
      ];
    }

    $this->buildItemScaleProperties($items);

    /** @var string|null */
    $align = $this->elements->attr('align');

    // @todo Remove this when we have default options/attributes implemented.
    if ($align === null) {
      $align = self::getTheme()['omnipedia_media_group']['variables']['align'];
    }

    /** @var array */
    $renderArray = [
      '#theme'      => 'omnipedia_media_group',

      '#items'      => $items,
      '#align'      => $align,

      '#attached'   => [
        'library'     => ['omnipedia_media/component.media_group'],
      ],
    ];

    /** @var string|null */
    $caption = $this->elements->attr('caption');

    if (!empty($caption)) {
      $renderArray['#caption'] = $caption;
    }

    return $renderArray;
  }

}
