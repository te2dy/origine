<?php
namespace themes\origine;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineTheme', 'publicHeadContent']);
$core->tpl->addBlock('origineInlineStyles', [__NAMESPACE__ . '\tplOrigineTheme', 'origineInlineStyles']);
$core->tpl->addBlock('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
$core->tpl->addValue('originePostPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostPrintURL']);
$core->tpl->addValue('origineEntryCommentFeedLink', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryCommentFeedLink']);
$core->tpl->addValue('origineEntryPingURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPingURL']);

class tplOrigineTheme
{
  public static function origineConfigArrayToCSS($rules, $rule_type = '')
  {
    $css = '';

    if ($rules) {
      foreach ($rules as $key => $value) {
        if (is_array($value) && !empty($value)) {
          $selector   = $key;
          $properties = $value;

          $css .= $selector . '{';

          foreach ($properties as $property => $rule) {
            $css .= $property . ':' . str_replace(', ', ',', $rule) . ';';
          }

          $css .= '}';
        }
      }
    }

    return $css;
  }

  /**
   * Adds some meta tags in head.
   */
  public static function publicHeadContent()
  {
    global $core;

    // Adds the name of the editor if set in the settings of Dotclear.
    if ($core->blog->settings->system->editor) {
      echo '<meta name="author" content="' . $core->blog->settings->system->editor . '" />' . "\n";
    }

    // Adds the name of the copyright notice if set in the settings of Dotclear.
    if ($core->blog->settings->system->copyright_notice) {
      echo '<meta name="copyright" content="' . $core->blog->settings->system->copyright_notice . '" />' . "\n";
    }
  }

  /**
   * Minifies inlined styles if the plugin origineConfig is not activated.
   */
  public static function origineInlineStyles($attr, $content)
  {
    global $core;

    // If the plugin origineConfig is not activated.
    if ($core->plugins->moduleExists('origineConfig') === false
      || ($core->plugins->moduleExists('origineConfig') === true
        && $core->blog->settings->origineConfig->activation === false
      )
    ) {
      // Removes HTML chars except quotes.
      $content = htmlspecialchars($content, ENT_NOQUOTES);

      // Removes carriage returns and new lines.
      $content = str_replace(array("\n", "\r"), '', $content);

      // Replaces multiple spaces by one space.
      $content = preg_replace('/\ +/', ' ', $content);

      // Removes unnecessary spaces.
      $to_replace  = [' { ', ' } ', ': ', ', ', '; '];
      $replaced_by = ['{', '}', ':', ',', ';'];
      $content     = str_replace($to_replace, $replaced_by, $content);

    // If the plugin origineConfig is activated.
    } else {
      if ($core->blog->settings->origineConfig->content_font_family !== 'sans-serif') {
        $css['body']['font-family'] = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
      } else {
        $css['body']['font-family'] = '-apple-system, BlinkMacSystemFont, "Avenir Next", Avenir, "Segoe UI", "Helvetica Neue", Helvetica, Ubuntu, Roboto, Noto, Arial, sans-serif';
      }

      if ($core->blog->settings->origineConfig->content_font_size) {
        $css['body']['font-size'] = abs((int) $core->blog->settings->origineConfig->content_font_size) . 'pt';
      }

      if ($core->blog->settings->origineConfig->content_text_align === 'justify') {
        $css['.content']['text-align'] = 'justify';
      }

      if ($core->blog->settings->origineConfig->content_hyphens == true ) {
        $css['.content']['-webkit-hyphens'] = 'auto';
        $css['.content']['-moz-hyphens']    = 'auto';
        $css['.content']['-ms-hyphens']     = 'auto';
        $css['.content']['hyphens']         = 'auto';

        $css['.content']['-webkit-hyphenate-limit-chars'] = '5 2 2';
        $css['.content']['-moz-hyphenate-limit-chars']    = '5 2 2';
        $css['.content']['-ms-hyphenate-limit-chars']     = '5 2 2';

        $css['.content']['-moz-hyphenate-limit-lines'] = '2';
        $css['.content']['-ms-hyphenate-limit-lines']  = '2';
        $css['.content']['hyphenate-limit-lines']      = '2';

        $css['.content']['-webkit-hyphenate-limit-last'] = 'always';
        $css['.content']['-moz-hyphenate-limit-last']    = 'always';
        $css['.content']['-ms-hyphenate-limit-last']     = 'always';
        $css['.content']['hyphenate-limit-last']         = 'always';

        $css['.content']['-webkit-hyphens'] = 'none';
        $css['.content']['-moz-hyphens']    = 'none';
        $css['.content']['-ms-hyphens']     = 'none';
        $css['.content']['hyphens']         = 'none';
      }

      $content = self::origineConfigArrayToCSS($css);
    }

    return '<style>' . trim($content) . '</style>';
  }

  /**
  * Displays some content when the post is selected.
  * Default: none.
  */
  public static function origineEntryIfSelected($attr, $content)
  {
    global $_ctx;

    if (!$_ctx->posts->post_selected) {
      $if_selected = '';
    } else {
      $if_selected = $content;
    }

    return $if_selected;
  }

  /**
   * Displays a "lang" attribute and its value
   * when the language of the current post is different
   * from the language defined for the blog.
   */
  public static function origineEntryLang()
  {
    return "<?php if (\$_ctx->posts->post_lang !== \$core->blog->settings->system->lang) { echo ' lang=\"' . \$_ctx->posts->post_lang . '\"'; } ?>";
  }

  /**
   * Displays an URL without http or https to show on posts to print.
   */
  public static function originePostPrintURL()
  {
    return "<?php echo str_replace(array('http://', 'https://'), '', \$_ctx->posts->getURL()); ?>";
  }

  /**
   * Display a link to the comment feed of current post.
   */
  public static function origineEntryCommentFeedLink($attr)
  {
    $type = !empty($attr['type']) ? $attr['type'] : 'atom';

    if (!preg_match('#^(rss2|atom)$#', $type)) {
        $type = 'atom';
    }

    if ( $type !== 'atom' ) {
      $mime_type = 'application/rss+xml';
    } else {
      $mime_type = 'application/atom+xml';
    }

    return '<?php echo "<a href=\"" . $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id . "\" rel=\"nofollow\" type=\"' . $mime_type . '\">" . str_replace(array("http://", "https://"), "", $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id) . "</a>"; ?>';
  }

  /**
   * Displays a link to trackbacks of the current post.
   */
  public static function origineEntryPingURL()
  {
    return "<?php if (\$_ctx->posts->trackbacksActive()) { echo '<a href=\"'.\$_ctx->posts->getTrackbackLink().'\" rel=\"nofollow\">' . str_replace(array('http://', 'https://'), '', \$_ctx->posts->getTrackbackLink()) . '</a>'; } ?>";
  }
}
