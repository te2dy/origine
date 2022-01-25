<?php
/**
 * Origine, an ultra minimalist theme for Dotclear
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */
namespace themes\origine;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineTheme', 'publicHeadContent']);
$core->tpl->addValue('origineInlineStyles', [__NAMESPACE__ . '\tplOrigineTheme', 'origineInlineStyles']);
$core->tpl->addBlock('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
$core->tpl->addValue('originePostPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostPrintURL']);
$core->tpl->addValue('origineEntryCommentFeedLink', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryCommentFeedLink']);
$core->tpl->addValue('origineEntryPingURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPingURL']);

class tplOrigineTheme
{
  /**
   * Inline CSS from an array.
   */
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
   * Inlines styles in the head of the document.
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
      $content = ':root{--color-background:#fff;--color-text-primary:#000;--color-text-secondary:#595959;--color-link:#de0000;--color-border:#aaa;--color-input-text:#000;--color-input-text-hover:#fff;--color-input-background:#eaeaea;--color-input-background-hover:#000;}@media(prefers-color-scheme: dark){:root{--color-background:#16161D;--color-text-primary:#d9d9d9;--color-text-secondary:#8c8c8c;--color-link:#f14646;--color-border:#aaa;--color-input-text:#d9d9d9;--color-input-text-hover:#fff;--color-input-background:#333333;--color-input-background-hover:#262626;}}body{font-family:"Iowan Old Style","Apple Garamond",Baskerville,"Times New Roman","Droid Serif",Times,"Source Serif Pro",serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";font-size:12pt;}';

    // If the plugin origineConfig is activated.
    } else {
      $content = $core->blog->settings->origineConfig->origine_styles;
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
    return '<?php if ($_ctx->posts->post_lang !== $core->blog->settings->system->lang) { echo " lang=\"" . $_ctx->posts->post_lang . "\""; } ?>';
  }

  /**
   * Displays an URL without http or https to show on posts to print.
   */
  public static function originePostPrintURL()
  {
    return '<?php echo str_replace([\'http://\', \'https://\'], "", $_ctx->posts->getURL()); ?>';
  }

  /**
   * Display a link to the comment feed of current post.
   */
  public static function origineEntryCommentFeedLink($attr)
  {
    // Checks the type of the set feed.
    $types = ['atom', 'rss2'];
    $type  = in_array($attr['type'], $feed_types) ? $attr['type'] : 'atom';

    return '<?php echo "<a href=\"" . $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id . "\" rel=\"nofollow\" type=\"application/' . $type . '+xml\">" . str_replace(array("http://", "https://"), "", $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id) . "</a>"; ?>';
  }

  /**
   * Displays a link to trackbacks of the current post.
   */
  public static function origineEntryPingURL()
  {
    return '<?php if ($_ctx->posts->trackbacksActive()) {
      echo "<a href=\"" . $_ctx->posts->getTrackbackLink() . "\" rel=\"nofollow\">" . str_replace([\'http://\', \'https://\'], \'\', $_ctx->posts->getTrackbackLink()) . "</a>";
    } ?>';
  }
}
