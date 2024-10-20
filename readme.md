This contains the source files for the "*Omnipedia - Media*" Drupal module,
which provides media-related functionality for
[Omnipedia](https://omnipedia.app/).

⚠️ ***[Why open source? / Spoiler warning](https://omnipedia.app/open-source)***

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

----

# Requirements

* [Drupal 10](https://www.drupal.org/download)

* PHP 8.1

* [Composer](https://getcomposer.org/)

## Drupal dependencies

Before attempting to install this, you must add the Composer repositories as
described in the installation instructions for these dependencies:

* The [`ambientimpact_core`](https://github.com/Ambient-Impact/drupal-ambientimpact-core) and [`ambientimpact_media`](https://github.com/Ambient-Impact/drupal-ambientimpact-media) modules.

* The [`omnipedia_content`](https://github.com/neurocracy/drupal-omnipedia-content) and [`omnipedia_core`](https://github.com/neurocracy/drupal-omnipedia-core) modules.

## Front-end dependencies

To build front-end assets for this project, [Node.js](https://nodejs.org/) and
[Yarn](https://yarnpkg.com/) are required.

----

# Installation

## Composer

### Set up

Ensure that you have your Drupal installation set up with the correct Composer
installer types such as those provided by [the `drupal/recommended-project`
template](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates#s-drupalrecommended-project).
If you're starting from scratch, simply requiring that template and following
[the Drupal.org Composer
documentation](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates)
should get you up and running.

### Repository

In your root `composer.json`, add the following to the `"repositories"` section:

```json
"drupal/omnipedia_media": {
  "type": "vcs",
  "url": "https://github.com/neurocracy/drupal-omnipedia-media.git"
}
```

### Installing

Once you've completed all of the above, run `composer require
"drupal/omnipedia_media:^7.0@dev"` in the root of your project to have
Composer install this and its required dependencies for you.

## Front-end assets

To build front-end assets for this project, you'll need to install
[Node.js](https://nodejs.org/) and [Yarn](https://yarnpkg.com/).

This package makes use of [Yarn
Workspaces](https://yarnpkg.com/features/workspaces) and references other local
workspace dependencies. In the `package.json` in the root of your Drupal
project, you'll need to add the following:

```json
"workspaces": [
  "<web directory>/modules/custom/*"
],
```

where `<web directory>` is your public Drupal directory name, `web` by default.
Once those are defined, add the following to the `"dependencies"` section of
your top-level `package.json`:

```json
"drupal-omnipedia-media": "workspace:^7"
```

Then run `yarn install` and let Yarn do the rest.

### Optional: install yarn.BUILD

While not required, [yarn.BUILD](https://yarn.build/) is recommended to make
building all of the front-end assets even easier.

----

# Building front-end assets

This uses [Webpack](https://webpack.js.org/) and [Symfony Webpack
Encore](https://symfony.com/doc/current/frontend.html) to automate most of the
build process. These will have been installed for you if you followed the Yarn
installation instructions above.

If you have [yarn.BUILD](https://yarn.build/) installed, you can run:

```
yarn build
```

from the root of your Drupal site. If you want to build just this package, run:

```
yarn workspace drupal-omnipedia-media run build
```

----

# Major breaking changes

The following major version bumps indicate breaking changes:

* 4.x - Front-end package manager is now [Yarn](https://yarnpkg.com/); front-end build process ported to [Webpack](https://webpack.js.org/).

* 5.x:

  * Requires Drupal 9.5 or [Drupal 10](https://www.drupal.org/project/drupal/releases/10.0.0).

  * Increases minimum version of [Hook Event Dispatcher](https://www.drupal.org/project/hook_event_dispatcher) to 3.1, removes deprecated code, and adds support for 4.0 which supports Drupal 10.

* 6.x:

  * Requires [Drupal 10](https://www.drupal.org/project/drupal/releases/10.0.0).

  * Requires [Hook Event Dispatcher](https://www.drupal.org/project/hook_event_dispatcher) 4.0 which supports Drupal 10.

  * Requires [`drupal/ambientimpact_core` 2.x](https://github.com/Ambient-Impact/drupal-ambientimpact-core/tree/2.x) and [`drupal/ambientimpact_media` 2.x](https://github.com/Ambient-Impact/drupal-ambientimpact-media/tree/2.x) for Drupal 10 support.

  * Requires [`drupal/omnipedia_content` 6.x](https://github.com/neurocracy/drupal-omnipedia-content/tree/6.x) for Drupal 10 support.

* 7.x:

  * Removed WebP module patch: [Upper/lowercase checking on file extension causes image generation to fail due to token mismatch [#3161795]](https://www.drupal.org/project/webp/issues/3161795#comment-14096421); a major version bump is done to ensure older installs referencing the 6.x patch file still install correctly.
