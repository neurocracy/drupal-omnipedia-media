<?php

declare(strict_types=1);

namespace Drupal\omnipedia_media_csp\EventSubscriber\Csp;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\csp\CspEvents;
use Drupal\csp\Event\PolicyAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter Content-Security-Policy header to add configured S3 hostname.
 */
class S3fsPolicyEventSubscriber implements EventSubscriberInterface {

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   */
  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // @see https://www.drupal.org/docs/extending-drupal/contributed-modules/contributed-module-documentation/content-security-policy/altering-a-sites-policy#s-default-policy-subscribers
      CspEvents::POLICY_ALTER => ['onCspPolicyAlter', 254],
    ];
  }

  /**
   * Automagically add the configured S3 URL to CSP directives.
   *
   * @param \Drupal\csp\Event\PolicyAlterEvent $alterEvent
   *   The Policy Alter event.
   */
  public function onCspPolicyAlter(PolicyAlterEvent $alterEvent): void {

    /** @var \Drupal\Core\Config\ImmutableConfig The S3 File System module configuration. */
    $s3fsConfig = $this->configFactory->get('s3fs.settings');

    // Don't do anything if not set to use a custom hostname.
    if ($s3fsConfig->get('use_customhost') !== true){
      return;
    }

    /** @var string|null */
    $hostname = $s3fsConfig->get('hostname');

    // Don't do anything if the hostname isn't set.
    //
    // @todo Is this necessary or does the S3 File System module validate this
    //   on form submit?
    if (empty($hostname)) {
      return;
    }

    /** @var string|null */
    $customDomain = $s3fsConfig->get('domain');

    // Set the policy to a custom domain name if set.
    if ($s3fsConfig->get('use_cname') === true && !empty($customDomain)) {

      $this->addPolicyUrl('https://' . $customDomain, $alterEvent);

    // Otherwise fall back to using the hostname.
    } else {

      $this->addPolicyUrl($hostname, $alterEvent);

    }

  }

  /**
   * Add media CSP policy directives using the provided URL.
   *
   * This appends the provided $url to the 'img-src' and 'media-src' directives.
   * Note that this doesn't use Csp::fallbackAwareAppendIfEnabled() as we don't
   * want these to fall back to 'default-src' if they're not enabled as that's
   * far too broad a directive in terms of security.
   *
   * @param string $url
   *   The URL to add to the policy directives. Must start with 'https://'.
   *
   * @param \Drupal\csp\Event\PolicyAlterEvent $alterEvent
   *   The Policy Alter event.
   */
  protected function addPolicyUrl(
    string $url, PolicyAlterEvent $alterEvent
  ): void {

    /** @var \Drupal\csp\Csp */
    $policy = $alterEvent->getPolicy();

    $policy->appendDirective('img-src', [$url]);

    $policy->appendDirective('media-src', [$url]);

  }

}
