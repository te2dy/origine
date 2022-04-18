<?php
/**
 * Origine, an ultra minimalist theme for Dotclear
 *
 * Note: all template tags found in html files
 * and whose name starts with {{tpl:origineConfig…
 * are handled by the plugin origineConfig.
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */

namespace themes\origine;

if (!defined('DC_RC_PATH')) {
  return;
}

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

// Behaviors
$core->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineTheme', 'publicHeadContent']);

// Blocks
$core->tpl->addBlock('origineCommentLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineCommentLinks']);
$core->tpl->addBlock('origineSidebar', [__NAMESPACE__ . '\tplOrigineTheme', 'origineSidebar']);
$core->tpl->addBlock('origineFooter', [__NAMESPACE__ . '\tplOrigineTheme', 'origineFooter']);

// Values
$core->tpl->addValue('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
$core->tpl->addValue('origineScreenReaderLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineScreenReaderLinks']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
$core->tpl->addValue('origineEntryPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPrintURL']);
$core->tpl->addValue('origineEntryCommentFeedLink', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryCommentFeedLink']);
$core->tpl->addValue('origineEntryPingURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPingURL']);

/**
 * Template tags used in combination with origineConfig plugin.
 */

// Values
$core->tpl->addValue('origineConfigActivationStatus', [__NAMESPACE__ . '\tplOrigineTheme', 'origineConfigActivationStatus']);
$core->tpl->addValue('originePostListType', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostListType']);
$core->tpl->addValue('origineFooterCredits', [__NAMESPACE__ . '\tplOrigineTheme', 'origineFooterCredits']);
$core->tpl->addValue('origineInlineStyles', [__NAMESPACE__ . '\tplOrigineTheme', 'origineInlineStyles']);

class tplOrigineTheme
{
  /**
   * Adds some meta tags in the <head> section
   * depending on the blog settings.
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
   * Displays a link to the comment feed and trackbacks,
   * except if the plugin tells no.
   */
  public static function origineCommentLinks($attr, $content)
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    // If the plugin is installed and activated.
    if ($plugin_activated === false
      || ($plugin_activated === true && $core->blog->settings->origineConfig->origine_settings['content_comment_links'] === true)) {
      return $content;
    }
  }

  /**
   * Displays the sidebar except if the plugin tells no.
   */
  public static function origineSidebar($attr, $content)
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if ($plugin_activated === false
      || ($plugin_activated === true && $core->blog->settings->origineConfig->origine_settings['widgets_enabled'] === true)
    ) {
      return $content;
    }
  }

  /**
   * Displays the footer except if the plugin tells no.
   */
  public static function origineFooter($attr, $content)
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if ($plugin_activated === false
      || ($plugin_activated === true && $core->blog->settings->origineConfig->origine_settings['footer_enabled'] === true)
    ) {
      return $content;
    }
  }

  /**
   * Displays a defined content when the current post is selected.
   */
  public static function origineEntryIfSelected()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if ($plugin_activated === false) {
      $tpl = 'standard';
    } else {
      $tpl = $core->blog->settings->origineConfig->origine_settings['content_post_list_type'];
    }

    if ($tpl === 'standard' || $tpl === 'full') {
      $label = __('Selected post');
    } else {
      $label = __('Selected');
    }

    return "<?php if (\$_ctx->posts->post_selected === '0') {"
         . "echo '';"
         . "} else {"
         . "echo '<div class=\"label label-selected\">';"
         . "echo '" . $label . "';"
         . "echo '</div>';"
         . "} ?>";
  }

  /**
   * Displays navigation link for screen readers.
   */
  public static function origineScreenReaderLinks()
  {
    global $core;

    $links  = '<a id="skip-content" class="skip-links" href="#site-content">';
    $links .= __('Skip to content');
    $links .= '</a>';

    if ($core->plugins->moduleExists('simpleMenu') // simpleMenu exists.
      && $core->blog->settings->system->simpleMenu_active // simpleMenu is activated.
      && $core->blog->settings->system->simpleMenu // A menu is set.
    ) {
      $links .= '<a id="skip-menu" class="skip-links" href="#main-menu">';
      $links .= __('Skip to menu');
      $links .= '</a>';
    }

    return $links;
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
   * Creates a link to the comment feed of current post.
   */
  public static function origineEntryCommentFeedLink($attr)
  {
    // Checks the type of the set feed.
    $types = ['atom', 'rss2'];

    if (isset($attr['type']) && in_array($attr['type'], $types)) {
      $type = $attr['type'];
    } else {
      $type = 'atom';
    }

    return '<?php echo "<a href=\"" . $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id . "\" rel=\"nofollow\" type=\"application/' . $type . '+xml\">" . str_replace(array("http://", "https://"), "", $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id) . "</a>"; ?>';
  }

  /**
   * Creates a link to the trackbacks of the current post.
   */
  public static function origineEntryPingURL()
  {
    return '<?php if ($_ctx->posts->trackbacksActive()) {
      echo "<span data-trackback-url=\"" . $_ctx->posts->getTrackbackLink() . "\" id=\"trackback-url\" onclick=\"origineTrackbackURLCopy();this.onclick=null;\">" . str_replace([\'http://\', \'https://\'], \'\', $_ctx->posts->getTrackbackLink()) . "</span>";
    } ?>';
  }

  // Template tags used in combination with origineConfig plugin:

  /**
   * Returns true if the plugin origineConfig
   * is installed and activated.
   *
   * To support the user’s settings, the version of the plugin must be superior to 0.6.3.
   */
  public static function origineConfigActivationStatus()
  {
    global $core;

    if ($core->plugins->moduleExists('origineConfig') === true
      // Needed to support the new parameter origine_settings that contains all the plugin settings.
      && version_compare('0.6.3', $core->plugins->moduleInfo('origineConfig', 'version'), '<')
      && $core->blog->settings->origineConfig->origine_settings['activation'] === true
    ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Loads the right entry list template based on origineConfig settings.
   * Default: standard.
   */
  public static function originePostListType()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if ($plugin_activated === false) {
      $tpl = $core->tpl->includeFile(['src' => '_entry-list-standard.html']);
    } elseif ($plugin_activated === true) {
      $tpl = $core->tpl->includeFile(['src' => '_entry-list-' . $core->blog->settings->origineConfig->origine_settings['content_post_list_type'] . '.html']);
    }

    return $tpl;
  }

  /**
   * Displays credits except if the plugin tells no.
   */
  public static function origineFooterCredits()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();
    $the_footer       = '';

    if ($plugin_activated === false
      || ($plugin_activated === true && $core->blog->settings->origineConfig->origine_settings['footer_credits'] === true)
    ) {
      $the_footer .= '<div class="widget" id="site-footer-ad">';

      $url_dotclear  = __('https://dotclear.org/');
      $text_dotclear = __('Dotclear');
      $text_origine  = __('Origine');

      if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
        $url_origine   = __('https://themes.dotaddict.org/galerie-dc2/details/origine');
      } else {
        $url_origine   = __('https://github.com/te2dy/origine');
        $text_origine .= ' ' . $core->themes->moduleInfo('origine', 'version');
      }

      $the_footer .= sprintf(
        __('Powered by <a href="%1$s">%2$s</a> and <a href="%3$s">%4$s</a>'),
        $url_dotclear,
        $text_dotclear,
        $url_origine,
        $text_origine
      );

      $the_footer .= '</div>';
    }

    return $the_footer;
  }

  /**
   * Adds styles in the head section.
   *
   * If the plugin origineConfig is not installed nor activated,
   * it will load default styles; otherwise, it will load
   * the custom styles set in the plugin page.
   *
   * @see styles.css
   */
  public static function origineInlineStyles()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if ($plugin_activated === false) {
      $styles = ':root{--color-background:#fff;--color-text-primary:#000;--color-text-secondary:#595959;--color-link:#de0000;--color-border:#aaa;--color-input-text:#000;--color-input-text-hover:#fff;--color-input-background:#eaeaea;--color-input-background-hover:#000}@media (prefers-color-scheme:dark){:root{--color-background:#16161D;--color-text-primary:#d9d9d9;--color-text-secondary:#8c8c8c;--color-link:#f14646;--color-border:#aaa;--color-input-text:#d9d9d9;--color-input-text-hover:#16161D;--color-input-background:#333;--color-input-background-hover:#d9d9d9}}body{font-family:"Iowan Old Style","Apple Garamond",Baskerville,"Times New Roman","Droid Serif",Times,"Source Serif Pro",serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";font-size:1em}.post-list-standard .post-link{display:block}.post-list-standard .post-meta{margin-bottom:.25em}.post-list-standard .post-title{font-size:1.1em}.post-list-standard .label-selected{border-left:none;margin-left:-1rem;margin-bottom:.5em}.post-list-standard .post-list-selected-content{border-left:.063rem solid var(--color-border);padding-left:1rem}.post-list-standard .label-page{margin-bottom:.5em}.post-list-standard .post-list-comment{display:inline-block;margin-left:.25em}.post-list-standard .post-footer{font-size:.9em;margin-top:.5em}.post-list-standard .read-more{border:none}';
    } else {
      $styles = $core->blog->settings->origineConfig->origine_settings['styles'] ? $core->blog->settings->origineConfig->origine_settings['styles'] : '';
    }

    return '<style>' . $styles . '</style>';
  }
}
