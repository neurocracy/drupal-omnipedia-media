This contains the source files for the "*Omnipedia - Media*" Drupal module,
which provides media-related functionality for
[Omnipedia](https://omnipedia.app/).

⚠️⚠️⚠️ ***Here be potential spoilers. Proceed at your own risk.*** ⚠️⚠️⚠️

----

# Why open source?

We're dismayed by how much knowledge and technology is kept under lock and key
in the videogame industry, with years of work often never seeing the light of
day when projects are cancelled. We've gotten to where we are by building upon
the work of countless others, and we want to keep that going. We hope that some
part of this codebase is useful or will inspire someone out there.

----

# Description

This contains plug-ins for embedding
[media](/src/Plugin/Omnipedia/Element/Media.php) and [media
groups](/src/Plugin/Omnipedia/Element/MediaGroup.php) on Omnipedia, [default
configuration](/config) for media entities, [event
subscribers](/src/EventSubscriber) to handle poster fields for local video media
entities, and [some utilities](/src/Utility) for handling [`srcset`
attributes](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#attr-srcset)
and [WebP
images](https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types#webp_image).
It also contains an optional patch for [the WebP
module](https://www.drupal.org/project/webp) to [fix WebP image derivative
generation on
Windows](https://www.drupal.org/project/webp/issues/3161795#comment-14096421).

----

# Requirements

* [Drupal 9](https://www.drupal.org/download) ([Drupal 8 is end-of-life](https://www.drupal.org/psa-2021-11-30))

* PHP 8

* [Composer](https://getcomposer.org/)

## Drupal dependencies

* The [```ambientimpact_media``` module](https://github.com/Ambient-Impact/drupal-modules) must be present.
