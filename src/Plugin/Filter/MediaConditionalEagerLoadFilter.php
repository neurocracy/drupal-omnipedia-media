<?php

declare(strict_types=1);

namespace Drupal\omnipedia_media\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\Plugin\FilterInterface;
use Symfony\Component\DomCrawler\Crawler;

#[Filter(
  id: "omnipedia_media_conditional_eager_load",
  title: new TranslatableMarkup("Omnipedia: Media conditional eager loading"),
  description: new TranslatableMarkup("Alters embedded media to use eager loading if they're likely to be visible immediately on loading a page."),
  type: FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
)]
class MediaConditionalEagerLoadFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langCode) {

    /** @var \Symfony\Component\DomCrawler\Crawler */
    $crawler = new Crawler(
      // The <div> is to prevent the PHP DOM automatically wrapping any
      // top-level text content in a <p> element.
      '<div id="omnipedia-media-conditional-eager-load-filter-root">' .
        (string) $text .
      '</div>',
    );

    // These elements are always at the start of a page.
    //
    // @todo Additional checks that they are indeed at the start?
    $eagerImagesCrawler = $crawler->filter(
      '.omnipedia-infobox, .omnipedia-main-page',
    )->filter('img');

    try {

      $beforeTocCrawler = $crawler->filter(
        '.table-of-contents',
      )->previousAll();

    } catch (\Exception $exception) {}

    if (isset($beforeTocCrawler) && count($beforeTocCrawler) > 0) {

      $beforeTocImagesCrawler = $beforeTocCrawler->filter('img');

      foreach ($beforeTocImagesCrawler as $image) {

        $eagerImagesCrawler->add($image);

      }

    }

    // If we didn't find any images to mark as eager loading, just return the
    // text as-is instead of dumping the HTML to avoid any more work.
    if (count($eagerImagesCrawler) === 0) {

      return new FilterProcessResult($text);

    }

    foreach ($eagerImagesCrawler as $image) {

      $image->setAttribute('loading', 'eager');

    }

    return new FilterProcessResult(
      $crawler->filter(
        '#omnipedia-media-conditional-eager-load-filter-root',
      )->html(),
    );

  }

}
