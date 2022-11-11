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

* The [```ambientimpact_core``` and ```ambientimpact_media``` modules](https://github.com/Ambient-Impact/drupal-modules) must be present.

## Front-end dependencies

To build front-end assets for this project, [Node.js](https://nodejs.org/) and
[Yarn](https://yarnpkg.com/) are required.

----

# Installation

## Composer

Ensure that you have your Drupal installation set up with the correct Composer
installer types such as those provided by [the ```drupal\recommended-project```
template](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates#s-drupalrecommended-project).
If you're starting from scratch, simply requiring that template and following
[the Drupal.org Composer
documentation](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates)
should get you up and running.

Then, in your root ```composer.json```, add the following to the
```"repositories"``` section:

```json
"drupal/omnipedia_media": {
  "type": "vcs",
  "url": "https://github.com/neurocracy/drupal-omnipedia-media.git"
}
```

Then, in your project's root, run ```composer require
"drupal/omnipedia_media:4.x-dev@dev"``` to have Composer install the module
and its required dependencies for you.

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
"drupal-omnipedia-media": "workspace:^4"
```

Then run `yarn install` and let Yarn do the rest.

### Optional: install yarn.BUILD

While not required, we recommend installing [yarn.BUILD](https://yarn.build/) to
make building all of the front-end assets even easier.

### Optional: use ```nvm```

If you want to be sure you're using the same Node.js version we're using, we
support using [Node Version Manager (```nvm```)](https://github.com/nvm-sh/nvm)
([Windows port](https://github.com/coreybutler/nvm-windows)). Once ```nvm``` is
installed, you can simply navigate to the project root and run ```nvm install```
to install the appropriate version contained in the ```.nvmrc``` file.

Note that if you're using the [Windows
port](https://github.com/coreybutler/nvm-windows), it [does not support
```.nvmrc```
files](https://github.com/coreybutler/nvm-windows/wiki/Common-Issues#why-isnt-nvmrc-supported-why-arent-some-nvm-for-macoslinux-features-supported),
so you'll have to provide the version contained in the ```.nvmrc``` as a
parameter: ```nvm install <version>``` (without the ```<``` and ```>```).

This step is not required, and may be dropped in the future as Node.js is fairly
mature and stable at this point.

----

# Building front-end assets

We use [Webpack](https://webpack.js.org/) and [Symfony Webpack
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
