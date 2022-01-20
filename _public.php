<?php
namespace themes\origine;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->tpl->addBlock('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
$core->tpl->addValue('originePostPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostPrintURL']);
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
   * Displays an URL without http or https
   * to show on posts to print.
   */
  public static function originePostPrintURL()
  {
    global $_ctx;

    $schemes  = array('http://', 'https://');
    $post_url = str_replace($schemes, '', $_ctx->posts->getURL());

    return $post_url;
  }

  /**
   * Displays a link to trackbacks
   */
  public static function origineEntryPingURL()
  {
    global $_ctx;

    $ping_link = '';

    if ($_ctx->posts->trackbacksActive()) {
      $ping_url = $_ctx->posts->getTrackbackLink();

      $ping_text = $_ctx->posts->getTrackbackLink();

      $schemes  = array('http://', 'https://');
      $ping_text = str_replace($schemes, '', $ping_text);

      $ping_link = '<a href="' . $ping_url . '">' . $ping_text . '</a>';
    }

    return $ping_link;
  }
}
