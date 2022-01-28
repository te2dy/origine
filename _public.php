<?php
/**
 * Origine, an ultra minimalist theme for Dotclear
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */
namespace themes\origine;

if (!defined('DC_RC_PATH')) {
  return;
}

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineTheme', 'publicHeadContent']);
$core->tpl->addBlock('origineConfigActivationStatus', [__NAMESPACE__ . '\tplOrigineTheme', 'origineConfigActivationStatus']);
$core->tpl->addBlock('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
$core->tpl->addValue('origineInlineStyles', [__NAMESPACE__ . '\tplOrigineTheme', 'origineInlineStyles']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
$core->tpl->addValue('origineEntryPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPrintURL']);
$core->tpl->addBlock('origineCommentLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineCommentLinks']);
$core->tpl->addValue('origineEntryCommentFeedLink', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryCommentFeedLink']);
$core->tpl->addValue('origineEntryPingURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPingURL']);
$core->tpl->addValue('origineEntryAuthorName', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryAuthorName']);

class tplOrigineTheme
{
  /**
   * Returns true if the plugin origineConfig
   * is installed and activated.
   */
  public static function origineConfigActivationStatus()
  {
    global $core;

    if ($core->plugins->moduleExists('origineConfig') === true
      && $core->blog->settings->origineConfig->activation === true
    ) {
      return true;
    } else {
      return false;
    }
  }
  /**
   * Adds some meta tags in the <head> section
   * depending of the blog settings.
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
   * Adds styles in the head section.
   *
   * If the plugin origineConfig is not installed nor activated,
   * it will load default styles; otherwise, it will load
   * the custom styles set in the plugin page.
   */
  public static function origineInlineStyles()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if ($plugin_activated === false) {
      $styles = ':root{--color-background:#fff;--color-text-primary:#000;--color-text-secondary:#595959;--color-link:#de0000;--color-border:#aaa;--color-input-text:#000;--color-input-text-hover:#fff;--color-input-background:#eaeaea;--color-input-background-hover:#000;}@media(prefers-color-scheme: dark){:root{--color-background:#16161D;--color-text-primary:#d9d9d9;--color-text-secondary:#8c8c8c;--color-link:#f14646;--color-border:#aaa;--color-input-text:#d9d9d9;--color-input-text-hover:#fff;--color-input-background:#333333;--color-input-background-hover:#262626;}}body{font-family:"Iowan Old Style","Apple Garamond",Baskerville,"Times New Roman","Droid Serif",Times,"Source Serif Pro",serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";font-size:12pt;}';
    } else {
      $styles = $core->blog->settings->origineConfig->origine_styles ? $core->blog->settings->origineConfig->origine_styles : '';
    }

    return '<style>' . $styles . '</style>';
  }

  /**
  * Displays a defined content when the current post is selected.
  */
  public static function origineEntryIfSelected($attr, $content)
  {
    global $_ctx;

    // If the post is selected, displays the content of the block.
    return ($_ctx->posts->post_selected === '0') ? '' : $content;
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
   * Creates an URL without http or https to display on posts to print.
   */
  public static function origineEntryPrintURL()
  {
    return '<?php echo str_replace([\'http://\', \'https://\'], "", $_ctx->posts->getURL()); ?>';
  }


  /**
   * Displays a link to the comment feed and trackbacks,
   * except if the plugin tells no.
   */
  public static function origineCommentLinks($attr, $content)
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    // If the plugin is installed and activated.
    if ($plugin_activated === false || ( $plugin_activated === true && $core->blog->settings->origineConfig->comment_links === true ) ) {
      return $content;
    }
  }

  /**
   * Creates a link to the comment feed of current post.
   */
  public static function origineEntryCommentFeedLink($attr)
  {
    // Checks the type of the set feed.
    $types = ['atom', 'rss2'];
    $type  = in_array($attr['type'], $types) ? $attr['type'] : 'atom';

    return '<?php echo "<a href=\"" . $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id . "\" rel=\"nofollow\" type=\"application/' . $type . '+xml\">" . str_replace(array("http://", "https://"), "", $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id) . "</a>"; ?>';
  }

  /**
   * Creates a link to the trackbacks of the current post.
   */
  public static function origineEntryPingURL()
  {
    return '<?php if ($_ctx->posts->trackbacksActive()) {
      echo "<a href=\"" . $_ctx->posts->getTrackbackLink() . "\" rel=\"nofollow\">" . str_replace([\'http://\', \'https://\'], \'\', $_ctx->posts->getTrackbackLink()) . "</a>";
    } ?>';
  }

  /**
   * Displays the author name.
   *
   * If a display name is not set in the preferences,
   * it will show the first and the last name.
   */
  public static function origineEntryAuthorName()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if ($plugin_activated === true) {
      // If a post is open and the post name is set to be displayed.
      if ($core->url->type === 'posts'
        && $core->blog->settings->origineConfig->post_author_name === true
      ) {
        return '<span class="post-author-name"><?php if ($_ctx->posts->user_displayname) { echo "· " . $_ctx->posts->user_displayname; } elseif ($_ctx->posts->user_firstname) { echo "· " . $_ctx->posts->user_firstname; if ($_ctx->posts->user_name) { echo " " . $_ctx->posts->user_name; } } else { echo $_ctx->posts->user_name ? "· " . $_ctx->posts->user_name : ""; } ?>';

      // Else (in the post list) and if the post name is set to be displayed.
      } elseif ($core->blog->settings->origineConfig->post_list_author_name === true) {
        return '<span class="post-author-name"><?php if ($_ctx->posts->user_displayname) { echo "· " . $_ctx->posts->user_displayname; } elseif ($_ctx->posts->user_firstname) { echo "· " . $_ctx->posts->user_firstname; if ($_ctx->posts->user_name) { echo " " . $_ctx->posts->user_name; } } else { echo $_ctx->posts->user_name ? "· " . $_ctx->posts->user_name : ""; } ?>';
      }
    }
  }
}
