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

\l10n::set(__DIR__ . '/locales/' . \dcCore::app()->lang . '/main');

\dcCore::app()->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineTheme', 'origineHeadMeta']);
\dcCore::app()->tpl->addValue('origineScreenReaderLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineScreenReaderLinks']);
\dcCore::app()->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
\dcCore::app()->tpl->addValue('origineEntryPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPrintURL']);
\dcCore::app()->tpl->addValue('origineURIRelative', [__NAMESPACE__ . '\tplOrigineTheme', 'origineURIRelative']);

// Template tags used in combination with origineConfig plugin.
\dcCore::app()->tpl->addValue('origineConfigActive', [__NAMESPACE__ . '\tplOrigineTheme', 'origineConfigActive']);
\dcCore::app()->tpl->addValue('origineSeparator', [__NAMESPACE__ . '\tplOrigineTheme', 'origineSeparator']);
\dcCore::app()->tpl->addValue('originePostListType', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostListType']);
\dcCore::app()->tpl->addValue('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
\dcCore::app()->tpl->addValue('origineFooterCredits', [__NAMESPACE__ . '\tplOrigineTheme', 'origineFooterCredits']);
\dcCore::app()->addBehavior('publicCommentFormAfterContent', [__NAMESPACE__ . '\tplOrigineTheme', 'origineCommentsMarkdown']);
\dcCore::app()->tpl->addBlock('origineCommentLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineCommentLinks']);
\dcCore::app()->tpl->addBlock('origineWidgetsNav', [__NAMESPACE__ . '\tplOrigineTheme', 'origineWidgetsNav']);
\dcCore::app()->tpl->addBlock('origineWidgetsExtra', [__NAMESPACE__ . '\tplOrigineTheme', 'origineWidgetsExtra']);
\dcCore::app()->tpl->addBlock('origineFooter', [__NAMESPACE__ . '\tplOrigineTheme', 'origineFooter']);
\dcCore::app()->tpl->addValue('origineStylesInline', [__NAMESPACE__ . '\tplOrigineTheme', 'origineStylesInline']);

class tplOrigineTheme
{
    /**
     * Adds meta tags in the <head> section depending on the blog settings.
     *
     * @return void
     */
    public static function origineHeadMeta()
    {
        // Adds the name of the editor.
        if (\dcCore::app()->blog->settings->system->editor) {
            $editor = \dcCore::app()->blog->settings->system->editor;
            $editor = strpos($editor, ' ') === false ? $editor : '"' . $editor . '"';

            echo '<meta name=author content=', $editor, '>', "\n";
        }

        // Adds the content of the copyright notice.
        if (\dcCore::app()->blog->settings->system->copyright_notice) {
            $notice = \dcCore::app()->blog->settings->system->copyright_notice;
            $notice = strpos($notice, ' ') === false ? $notice : '"' . $notice . '"';

            echo '<meta name=copyright content=', $notice, '>', "\n";
        }
    }

    /**
     * Displays navigation links for screen readers.
     *
     * @return void
     */
    public static function origineScreenReaderLinks()
    {
        $links  = '<a id="skip-content" class="skip-links" href="#site-content">';
        $links .= __('accessibility-skip-content');
        $links .= '</a>';

        // If simpleMenu exists, is activated and a menu has been set.
        if (\dcCore::app()->plugins->moduleExists('simpleMenu') && \dcCore::app()->blog->settings->system->simpleMenu_active === true) {
            $links .= '<a id=skip-menu class=skip-links href=#main-menu>';
            $links .= __('accessibility-skip-menu');
            $links .= '</a>';
        }

        return $links;
    }

    /**
     * Displays a lang attribute and its value when the language of the current post is different
     * from the language defined for the whole blog.
     *
     * @return void
     */
    public static function origineEntryLang()
    {
        return '<?php if (\dcCore::app()->ctx->posts->post_lang !== \dcCore::app()->blog->settings->system->lang) { echo " lang=" , dcCore::app()->ctx->posts->post_lang; } ?>';
    }

    /**
     * Creates an URL of the current post without http or https
     * to be displayed on printing only.
     */
    public static function origineEntryPrintURL()
    {
        return '<?php echo str_replace([\'http://\', \'https://\'], "", \dcCore::app()->ctx->posts->getURL()); ?>';
    }

    /**
     * Returns the relative URI of the current page.
     *
     * @return void
     */
    public static function origineURIRelative()
    {
        return '<?php echo $_SERVER["REQUEST_URI"]; ?>';
    }

    // Template tags used in combination with origineConfig plugin.

    /**
     * Checks if the plugin origineConfig is installed and activated.
     *
     * @return bool Returns true if the plugin is installed and activated.
     */
    public static function origineConfigActive()
    {
        if (\dcCore::app()->plugins->moduleExists('origineConfig') === true && \dcCore::app()->blog->settings->origineConfig->active === true) {
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
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->content_separator === "/")) {
            return "/";
        } else {
            return \dcCore::app()->blog->settings->origineConfig->content_separator;
        }
    }

    /**
     * Loads the right entry list template based on origineConfig settings.
     * Default: standard
     */
    public static function originePostListType()
    {
        $plugin_activated = self::origineConfigActive();

        $post_list_types = ['standard', 'short', 'full'];

        if ($plugin_activated === false || ($plugin_activated === true && !in_array(\dcCore::app()->blog->settings->origineConfig->content_post_list_type, $post_list_types))) {
            $tpl = \dcCore::app()->tpl->includeFile(['src' => '_entry-list-standard.html']);
        } elseif ($plugin_activated === true) {
            $tpl = \dcCore::app()->tpl->includeFile(['src' => '_entry-list-' . \dcCore::app()->blog->settings->origineConfig->content_post_list_type . '.html']);
        }

        return $tpl;
    }

    /**
     * Displays a text when the current post is selected.
     */
    public static function origineEntryIfSelected()
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false) {
            $tpl = 'standard';
        } else {
            $tpl = \dcCore::app()->blog->settings->origineConfig->content_post_list_type;
        }

        if ($tpl === 'standard' || $tpl === 'full') {
            $label = __('post-selected-label');
        } else {
            $label = __('post-selected-short-label');
        }

        return '
        <?php
        if (\dcCore::app()->ctx->posts->post_selected === "1") {
            echo "<div class=\"label label-selected\">' . $label . '</div>";
        } ?>';
    }

    /**
     * Credits to display at the bottom of the site.
     *
     * They can be removed with the plugin origineConfig.
     *
     * @return void
     */
    public static function origineFooterCredits()
    {
        $plugin_activated = self::origineConfigActive();
        $the_footer             = '';

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->footer_credits === true)) {
            $the_footer .= '<div class=widget id=site-footer-ad>';

            $url_dotclear  = __('footer-dotclear-url');
            $text_dotclear = __('footer-dotclear-name');
            $text_origine  = __('footer-origine-name');

            if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
                $url_origine = __('footer-origine-dotaddict-url');
            } else {
                $dotclear_version        = \dcCore::app()->getVersion('core');
                $dotclear_version_parts  = explode('-', $dotclear_version);
                $dotclear_version_simple = $dotclear_version_parts[0] ? $dotclear_version_parts[0] : $dotclear_version;

                $text_dotclear .= ' ' . $dotclear_version_simple;
                $url_origine    = __('footer-origine-github-url');
                $text_origine  .= ' ' . \dcCore::app()->themes->moduleInfo('origine', 'version');
            }

            $the_footer .= sprintf(
                __('footer-powered-by'),
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
        if (\dcCore::app()->blog->settings->system->markdown_comments === true) {
            echo '<div class="form-entry text-italic text-small">',
            __('comment-form-markdown-supported'),
            '</div>';
        }
    }

    /**
     * Displays a link to the comment feed and trackbacks.
     */
    public static function origineCommentLinks($attr, $content)
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->content_comment_links === true)) {
            return $content;
        }
    }

    /**
     * Displays navigation widgets.
     */
    public static function origineWidgetsNav($attr, $content)
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->widgets_nav_position !== 'disabled')) {
            return $content;
        }
    }

    /**
     * Displays extra widgets.
     */
    public static function origineWidgetsExtra($attr, $content)
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->widgets_extra_enabled === true)) {
            return $content;
        }
    }

    /**
     * Displays the footer.
     */
    public static function origineFooter($attr, $content)
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->footer_enabled === true)) {
            return $content;
        }
    }

    /**
     * Adds styles in the head.
     *
     * If origineConfig is activated, it returns custom styles generated by it;
     * otherwise, default styles are returned.
     *
     * @return string The styles.
     */
    public static function origineStylesInline()
    {
        $plugin_activated = self::origineConfigActive();

        $styles  = ':root{';
        $styles .= '--page-width:30em;';
        $styles .= '--order-content:2;';
        $styles .= '--order-widgets-nav:3;';
        $styles .= '--order-widgets-extra:4;';
        $styles .= '--order-footer:5;';
        $styles .= '--font-family:"Iowan Old Style","Apple Garamond",Baskerville,"Times New Roman","Droid Serif",Times,"Source Serif Pro",serif,"Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";';
        $styles .= '--font-size:1em;';
        $styles .= '--color-background:#fff;';
        $styles .= '--color-text-primary:#000;';
        $styles .= '--color-text-secondary:#595959;';
        $styles .= '--color-link:#de0000;';
        $styles .= '--color-border:#aaa;';
        $styles .= '--color-input-text:#000;';
        $styles .= '--color-input-text-hover:#fff;';
        $styles .= '--color-input-background:#eaeaea;';
        $styles .= '--color-input-background-hover:#000;';
        $styles .= '--text-align:left';
        $styles .= '}';
        $styles .= ' @media (prefers-color-scheme:dark){';
        $styles .= ':root{';
        $styles .= '--color-background:#16161d;';
        $styles .= '--color-text-primary:#d9d9d9;';
        $styles .= '--color-text-secondary:#8c8c8c;';
        $styles .= '--color-link:#f14646;';
        $styles .= '--color-border:#aaa;';
        $styles .= '--color-input-text:#d9d9d9;';
        $styles .= '--color-input-text-hover:#16161d;';
        $styles .= '--color-input-background:#333;';
        $styles .= '--color-input-background-hover:#d9d9d9';
        $styles .= '}';
        $styles .= '}';

        if ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->css_origine) {
            $styles = \dcCore::app()->blog->settings->origineConfig->css_origine;
        }

        return '<style>' . $styles . '</style>';
    }
}
