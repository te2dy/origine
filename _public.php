<?php
namespace themes\origine;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/main');

$core->tpl->addBlock('EntryIfContentIsCut', [__NAMESPACE__ . '\tplOrigineTheme', 'EntryIfContentIsCut']);

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
}
