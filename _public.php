<?php
/**
 * Origine, a Dotclear theme
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */

namespace themes\origine;

if (!defined('DC_RC_PATH')) {
  return;
}

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineTheme', 'origineHeadMeta']);
$core->tpl->addValue('origineScreenReaderLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineScreenReaderLinks']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
$core->tpl->addValue('origineEntryPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPrintURL']);

// Template tags used in combination with origineConfig plugin.
$core->tpl->addValue('origineConfigActivationStatus', [__NAMESPACE__ . '\tplOrigineTheme', 'origineConfigActivationStatus']);
$core->tpl->addValue('origineSeparator', [__NAMESPACE__ . '\tplOrigineTheme', 'origineSeparator']);
$core->tpl->addValue('originePostListType', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostListType']);
$core->tpl->addValue('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
$core->tpl->addValue('origineFooterCredits', [__NAMESPACE__ . '\tplOrigineTheme', 'origineFooterCredits']);
$core->addBehavior('publicCommentFormAfterContent', [__NAMESPACE__ . '\tplOrigineTheme', 'origineCommentsMarkdown']);
$core->tpl->addBlock('origineCommentLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineCommentLinks']);
$core->tpl->addBlock('origineWidgetsNav', [__NAMESPACE__ . '\tplOrigineTheme', 'origineWidgetsNav']);
$core->tpl->addBlock('origineWidgetsExtra', [__NAMESPACE__ . '\tplOrigineTheme', 'origineWidgetsExtra']);
$core->tpl->addBlock('origineFooter', [__NAMESPACE__ . '\tplOrigineTheme', 'origineFooter']);
$core->tpl->addValue('origineInlineStyles', [__NAMESPACE__ . '\tplOrigineTheme', 'origineInlineStyles']);

class tplOrigineTheme
{
  /**
   * Adds meta tags in the <head> section depending on the blog settings.
   */
  public static function origineHeadMeta()
  {
    global $core;

    // Adds the name of the editor.
    if ($core->blog->settings->system->editor) {
      echo '<meta name="author" content="' . $core->blog->settings->system->editor . '" />' . "\n";
    }

    // Adds the content of the copyright notice.
    if ($core->blog->settings->system->copyright_notice) {
      echo '<meta name="copyright" content="' . $core->blog->settings->system->copyright_notice . '" />' . "\n";
    }
  }

  /**
   * Displays navigation links for screen readers.
   */
  public static function origineScreenReaderLinks()
  {
    global $core;

    $links  = '<a id="skip-content" class="skip-links" href="#site-content">';
    $links .= __('Skip to content');
    $links .= '</a>';

    // If simpleMenu exists, is activated and a menu has been set.
    if (
      $core->plugins->moduleExists('simpleMenu')
      && $core->blog->settings->system->simpleMenu_active === true
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
    return '
    <?php if ($_ctx->posts->post_lang !== $core->blog->settings->system->lang) {
      echo " lang=\"" . $_ctx->posts->post_lang . "\"";
    } ?>';
  }

  /**
   * Creates an URL of the current post without http or https
   * to be displayed on printing only.
   */
  public static function origineEntryPrintURL()
  {
    return '<?php echo str_replace([\'http://\', \'https://\'], "", $_ctx->posts->getURL()); ?>';
  }

  // Template tags used in combination with origineConfig plugin.

  /**
   * Returns true if the plugin origineConfig
   * is installed and activated.
   *
   * To support the user???s settings, the version of the plugin must be superior to 0.6.3.
   */
  public static function origineConfigActivationStatus()
  {
    global $core;

    if (
      $core->plugins->moduleExists('origineConfig') === true
      && version_compare('0.6.3', $core->plugins->moduleInfo('origineConfig', 'version'), '<')
      && $core->blog->settings->origineConfig->origine_settings['activation'] === true
    ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Displays a character to separate theme elements on a same line.
   * Default : /
   */
  public static function origineSeparator()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if (
      $plugin_activated === false
      || (
        $plugin_activated === true
        && $core->blog->settings->origineConfig->origine_settings['global_separator'] === "/"
      )
    ) {
      return "/";
    } else {
      return $core->blog->settings->origineConfig->origine_settings['global_separator'];
    }
  }

  /**
   * Loads the right entry list template based on origineConfig settings.
   * Default: standard
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
   * Displays a text when the current post is selected.
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

    return '
    <?php
    if ($_ctx->posts->post_selected === "1") {
      echo "<div class=\"label label-selected\">";
      echo "' . $label . '";
      echo "</div>";
    } ?>';
  }

  /**
   * Displays credits in the footer.
   */
  public static function origineFooterCredits()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();
    $the_footer       = '';

    if (
      $plugin_activated === false
      || (
        $plugin_activated === true
        && $core->blog->settings->origineConfig->origine_settings['footer_credits'] === true
      )
    ) {
      $the_footer .= '<div class="widget" id="site-footer-ad">';

      $url_dotclear  = __('https://dotclear.org/');
      $text_dotclear = __('Dotclear');
      $text_origine  = __('Origine');

      if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
        $url_origine   = __('https://themes.dotaddict.org/galerie-dc2/details/origine');
      } else {
        $dotclear_version        = $core->getVersion('core');
        $dotclear_version_parts  = explode('-', $dotclear_version);
        $dotclear_version_simple = $dotclear_version_parts[0] ? $dotclear_version_parts[0] : $dotclear_version;

        $text_dotclear .= ' ' . $dotclear_version_simple;
        $url_origine    = __('https://github.com/te2dy/origine');
        $text_origine  .= ' ' . $core->themes->moduleInfo('origine', 'version');
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
   * Displays a link to the comment feed and trackbacks.
   */
  public static function origineCommentsMarkdown()
  {
    global $core;

    if ($core->blog->settings->system->markdown_comments === true) {
      echo '<div class="form-entry text-italic text-small">';
      echo __('Markdown language allowed (<a href="https://commonmark.org/help/" rel="nofollow">help</a>).');
      echo '</div>';
    }
  }

  /**
   * Displays a link to the comment feed and trackbacks.
   */
  public static function origineCommentLinks($attr, $content)
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if (
      $plugin_activated === false
      || (
        $plugin_activated === true
        && $core->blog->settings->origineConfig->origine_settings['content_comment_links'] === true
      )
    ) {
      return $content;
    }
  }

  /**
   * Displays navigation widgets.
   */
  public static function origineWidgetsNav($attr, $content)
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if (
      $plugin_activated === false
      || (
        $plugin_activated === true
        && $core->blog->settings->origineConfig->origine_settings['widgets_nav_position'] !== 'disabled'
      )
    ) {
      return $content;
    }
  }

  /**
   * Displays extra widgets.
   */
  public static function origineWidgetsExtra($attr, $content)
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if (
      $plugin_activated === false
      || (
        $plugin_activated === true
        && $core->blog->settings->origineConfig->origine_settings['widgets_extra_enabled'] === true
      )
    ) {
      return $content;
    }
  }

  /**
   * Displays the footer.
   */
  public static function origineFooter($attr, $content)
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if (
      $plugin_activated === false
      || (
        $plugin_activated === true
        && $core->blog->settings->origineConfig->origine_settings['footer_enabled'] === true
      )
    ) {
      return $content;
    }
  }

  /**
   * Adds styles in the head section.
   *
   * If the plugin origineConfig is not installed nor activated,
   * it will load default styles; otherwise, it will load
   * the custom styles set in the plugin page.
   *
   * @see origine/styles.css
   */
  public static function origineInlineStyles()
  {
    global $core;

    $plugin_activated = self::origineConfigActivationStatus();

    if ($plugin_activated === false) {
      $styles = ':root{--content-order:2;--widgets-nav-order:3;--widgets-extra-order:4;--footer-order:5;--color-background:#fff;--color-text-primary:#000;--color-text-secondary:#595959;--color-link:#de0000;--color-border:#aaa;--color-input-text:#000;--color-input-text-hover:#fff;--color-input-background:#eaeaea;--color-input-background-hover:#000}@media (prefers-color-scheme:dark){:root{--color-background:#16161D;--color-text-primary:#d9d9d9;--color-text-secondary:#8c8c8c;--color-link:#f14646;--color-border:#aaa;--color-input-text:#d9d9d9;--color-input-text-hover:#16161D;--color-input-background:#333;--color-input-background-hover:#d9d9d9}}body{font-family:"Iowan Old Style","Apple Garamond",Baskerville,"Times New Roman","Droid Serif",Times,"Source Serif Pro",serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";font-size:1em}.post-list-standard .post-link{display:block}.post-list-standard .post-meta{margin-bottom:.25em}.post-list-standard .post-title{font-size:1.1em}.post-list-standard .label-selected{border-left:none;margin-left:-1rem;margin-bottom:.5em}.post-list-standard .post-list-selected-content{border-left:.063rem solid var(--color-border);padding-left:1rem}.post-list-standard .label-page{margin-bottom:.5em}.post-list-standard .post-list-reactions{display:inline-block;margin-left:.25em}.post-list-standard .post-footer{font-size:.9em;margin-top:.5em}.post-list-standard .read-more{border:none}';
    } else {
      $styles = $core->blog->settings->origineConfig->origine_settings['styles'] ? $core->blog->settings->origineConfig->origine_settings['styles'] : '';
    }

    return '<style>' . $styles . '</style>';
  }
}
