<?php
namespace themes\origine;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->tpl->addBlock('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
$core->tpl->addValue('originePostPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostPrintURL']);
$core->tpl->addValue('origineEntryCommentFeedLink', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryCommentFeedLink']);
$core->tpl->addValue('origineEntryPingURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPingURL']);

class tplOrigineTheme
{
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
    global $core, $_ctx;

    $lang_attr = '';
    if ($_ctx->posts->post_lang != $core->blog->settings->system->lang) {
      $lang_attr = ' lang="' . $_ctx->posts->post_lang . '"';
    }

    return $lang_attr;
  }

  /**
   * Displays an URL without http or https to show on posts to print.
   */
  public static function originePostPrintURL()
  {
    return "<?php echo '<a href=\"'.\$_ctx->posts->getURL().'\">' . str_replace(array('http://', 'https://'), '', \$_ctx->posts->getURL()) . '</a>'; ?>";
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

    return '<?php echo "<a href=\"" . $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id . "\">" . str_replace(array(\'http://\', \'https://\'), \'\', $core->blog->url.$core->url->getURLFor("feed","' . $type . '") . "/comments/" . $_ctx->posts->post_id) . "</a>"; ?>';
  }

  /**
   * Displays a link to trackbacks of the current post.
   */
  public static function origineEntryPingURL()
  {
    return "<?php if (\$_ctx->posts->trackbacksActive()) { echo '<a href=\"'.\$_ctx->posts->getTrackbackLink().'\">' . str_replace(array('http://', 'https://'), '', \$_ctx->posts->getTrackbackLink()) . '</a>'; } ?>";
  }
}
