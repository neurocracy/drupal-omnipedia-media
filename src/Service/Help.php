<?php declare(strict_types=1);

namespace Drupal\omnipedia_media\Service;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\omnipedia_core\Service\HelpInterface;

/**
 * The Omnipedia media help service.
 */
class Help implements HelpInterface {

  use StringTranslationTrait;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The Drupal string translation service.
   */
  public function __construct(TranslationInterface $stringTranslation) {
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public function help(
    string $routeName, RouteMatchInterface $routeMatch
  ): MarkupInterface|array|string {

    if ($routeName === 'entity.media.add_form') {
      return $this->getMediaAddFormHelp($routeMatch);
    }

    return [];

  }

  /**
   * Get help content for the media add form route.
   *
   * This provides a description of our naming conventions and a link to the
   * Wikipedia file naming conventions for reference.
   *
   * @return array
   *   A render array.
   */
  protected function getMediaAddFormHelp(
    RouteMatchInterface $routeMatch
  ): array {

    /** @var string */
    $mediaTypeId = $routeMatch->getParameter('media_type')->id();

    // Don't output help for remote video as authors can't directly edit the
    // title as it's pulled from the oEmbed data.
    if ($mediaTypeId === 'remote_video') {
      return [];
    }

    /** @var \Drupal\Core\Url */
    $wikipediaFileNamesUrl = Url::fromUri(
      'https://en.wikipedia.org/wiki/Wikipedia:File_names'
    );

    /** @var \Drupal\Core\Link */
    $wikipediaFileNamesLink = new Link(
      $this->t('the Wikipedia naming conventions'), $wikipediaFileNamesUrl
    );

    return [
      '#type'   => 'html_tag',
      '#tag'    => 'p',
      '#value'  => $this->t(
        'Local media names should be descriptive, meaningful, use title case, lower case file extensions, and avoid unnecessary numbers or versioning (as Drupal handles that for you). For example, "<code>Toronto_Ontario_waterfront.jpg</code>" is preferable over "<code>TOR73454.JPG</code>". This allows media names to be consistent and long-lived and thus easy to reference in wiki pages. Additionally, this mimics @wikipediaFileNamesLink for users who notice the detail.',
        [
          // Unfortunately, this needs to be rendered here or it'll cause a
          // fatal error when Drupal tries to pass it to \htmlspecialchars().
          '@wikipediaFileNamesLink' => $wikipediaFileNamesLink->toString(),
        ]
      ),
    ];

  }

}
