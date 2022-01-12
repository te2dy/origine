<?php
namespace themes\origine;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);

class tplOrigineTheme
{
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
}
