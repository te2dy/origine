<?php
namespace themes\origine;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->tpl->addBlock('EntryIfContentIsCut', [__NAMESPACE__ . '\tplOrigineTheme', 'EntryIfContentIsCut']);
$core->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);

class tplOrigineTheme
{
  public static function EntryIfContentIsCut($attr, $content)
  {
    global $core;

    if (empty($attr['cut_string']) || !empty($attr['full'])) {
        return '';
    }

    $urls = '0';
    if (!empty($attr['absolute_urls'])) {
        $urls = '1';
    }

    $short              = $core->tpl->getFilters($attr);
    $cut                = $attr['cut_string'];
    $attr['cut_string'] = 0;
    $full               = $core->tpl->getFilters($attr);
    $attr['cut_string'] = $cut;

    return '<?php if (strlen(' . sprintf($full, '$_ctx->posts->getContent(' . $urls . ')') . ') > ' .
    'strlen(' . sprintf($short, '$_ctx->posts->getContent(' . $urls . ')') . ')) : ?>' . $content . '<?php endif; ?>';
  }

  /**
   * Display a "lang" attribute and its value
   * when the language of the current post is different
   * of the language defined for the blog.
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
