<?php
namespace themes\origine;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineTheme', 'publicHeadContent']);
$core->tpl->addBlock('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
$core->tpl->addValue('originePostPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostPrintURL']);
$core->tpl->addValue('origineEntryCommentFeedLink', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryCommentFeedLink']);
$core->tpl->addValue('origineEntryPingURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPingURL']);

class tplOrigineTheme
{
  /**
   * Adds default inline styles
   * and puts the default stylesheet inside <head>
   * if the plugin origineConfig has not been activated.
   */
  public static function publicHeadContent()
  {
    global $core;

    if ($core->plugins->moduleExists('origineConfig') === false
      || ($core->plugins->moduleExists('origineConfig') === true
        && $core->blog->settings->origineConfig->activation === false
      )
    ) {
      echo '<style type="text/css">body{font-family:Iowan Old Style,Apple Garamond,Baskerville,Times New Roman,Droid Serif,Times,Source Serif Pro,serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;font-size:12pt;}</style>' . "\n";
    }

    echo '<link href="' . $core->blog->settings->system->themes_url . "/" . $core->blog->settings->system->theme . '/style.min.css" rel="stylesheet" type="text/css" />' . "\n";
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
