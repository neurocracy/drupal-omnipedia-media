// -----------------------------------------------------------------------------
//   Omnipedia - Media - Media image component
// -----------------------------------------------------------------------------

// This finds all Omnipedia media that have a link with a 'data-photowipe-src'
// attribute that contain a thumbnail image whose 'currentSrc' property points
// to a WebP image (which indicates the browser has chosen and supports WebP),
// and alters the 'data-photoswipe-src' attribute to point to a WebP derivative
// image.
//
// @todo Generalize this so we don't have to hard code WebP or any other format
//   but have it set on the <source> elements via the backend and automatically
//   set the 'data-photoswipe-src' on the link to the value of a
//   'data-photoswipe-src' on the <source> that's been chosen by the browser as
//   detected from the currentSrc of the <img>?
//
// @see https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/currentSrc

AmbientImpact.addComponent(
  'OmnipediaMediaImage',
function(OmnipediaMediaImage, $) {

  'use strict';

  /**
   * Our event namespace.
   *
   * @type {String}
   */
  const eventNamespace = 'OmnipediaMediaImage';

  /**
   * The HTML attribute where PhotoSwipe looks for the full image to load.
   *
   * @type {String}
   */
  const dataAttributeName = 'data-photoswipe-src';

  /**
   * The camelized data name to read the PhotoSwipe full image from.
   *
   * @type {String}
   */
  const dataName = 'photoswipeSrc';

  /**
   * The back up data name to store the current PhotoSwipe full image.
   *
   * This is used in the detach to restore the previous value.
   *
   * @type {String}
   */
  const backupDataName = 'oldPhotoSwipeSrc';

  /**
   * Get the file extension from a file/path.
   *
   * @param {String} fileName
   *
   * @return {String}
   *
   * @see https://stackoverflow.com/a/12900504
   *   Based on this Stack Overflow answer.
   */
  function getFileExtension(fileName) {
    return fileName.slice((fileName.lastIndexOf('.') - 1 >>> 0) + 2);
  };

  /**
   * Alter a provided link with data from the provided image.
   *
   * @param {jQuery} $link
   *   The link element jQuery collection.
   *
   * @param {jQuery} $img
   *   The link element jQuery collection.
   */
  function alterLink($link, $img) {

    /**
     * The currentSrc property of the image in the link.
     *
     * @type {String|undefined}
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/currentSrc
     */
    let currentSrc = $img.prop('currentSrc');

    // Bail if 'currentSrc' is undefined or an empty string, the latter ocurring
    // if the image hasn't loaded yet.
    if (typeof currentSrc === 'undefined' || currentSrc === '') {
      return;
    }

    /**
     * A URL object containing the parsed 'currentSrc'.
     *
     * @type {URL}
     */
    let urlObject = new URL(currentSrc);

    // Skip this link if the thumbnail loaded/chosen by the browser isn't a WebP
    // image.
    if (getFileExtension(urlObject.pathname) !== 'webp') {
      return;
    }

    // Save the unaltered data-photoswipe-src to element data for detach to undo
    // the changes.
    $link.data(backupDataName, $link.data(dataName));

    /**
     * The URL object representing the 'data-photoswipe-src' link attribute.
     *
     * @type {URL}
     */
    let photoswipeSrcUrl = new URL($link.data(dataName));

    /**
     * The file extension of the 'data-photoswipe-src' link attribute.
     *
     * @type {String}
     */
    let photoswipeSrcExtension = getFileExtension(photoswipeSrcUrl.pathname);

    // Alter the original PhotoSwipe URL to use the WebP file extension.
    photoswipeSrcUrl.pathname = photoswipeSrcUrl.pathname.slice(
      0, photoswipeSrcExtension.length * -1
    ) + 'webp';

    // Save the altered URL for PhotoSwipe to load.
    $link.attr(dataAttributeName, photoswipeSrcUrl.toString());

  }

  this.addBehaviour(
    'OmnipediaMediaImage',
    'omnipedia-media-image',
    '.layout-container',
    function(context, settings) {

      /**
       * Media element links that point to a PhotoSwipe image.
       *
       * @type {jQuery}
       */
      let $mediaImageLinks = $(
        '.omnipedia-media a[' + dataAttributeName + ']', context
      );

      for (let i = $mediaImageLinks.length - 1; i >= 0; i--) {

        /**
         * The link element jQuery collection.
         *
         * @type {jQuery}
         */
        let $link = $mediaImageLinks.eq(i);

        /**
         * The image element jQuery collection.
         *
         * @type {jQuery}
         */
        let $img = $link.find('img').eq(0);

        // If the image has already loaded, try to alter the link now.
        if ($img.prop('complete') === true) {
          alterLink($link, $img);

        // Otherwise, this is likely a lazy loaded image so attach an event
        // handler for its 'load' event. This is necessary as the 'currentSrc'
        // property will be an empty string if the image has not loaded yet.
        } else {

          $img.on('load.' + eventNamespace, function(event) {
            alterLink($link, $img);
          });

        }

      }

    },
    function(context, settings, trigger) {

      /**
       * Media element links that point to a PhotoSwipe image.
       *
       * @type {jQuery}
       */
      let $mediaImageLinks = $(
        '.omnipedia-media a[' + dataAttributeName + ']', context
      );

      for (let i = $mediaImageLinks.length - 1; i >= 0; i--) {

        $mediaImageLinks.eq(i).find('img').eq(0).off('load.' + eventNamespace);

        // Restore the original data-photoswipe-src attribute and remove our
        // stored data.
        $mediaImageLinks.eq(i).attr(
          dataAttributeName, $mediaImageLinks.eq(i).data(backupDataName)
        ).removeData(backupDataName);

      }

    }
  );

});
