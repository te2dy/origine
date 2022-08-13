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

\l10n::set(dirname(__FILE__) . '/locales/' . \dcCore::app()->lang . '/main');

\dcCore::app()->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineTheme', 'origineHeadMeta']);
\dcCore::app()->tpl->addValue('origineScreenReaderLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineScreenReaderLinks']);
\dcCore::app()->tpl->addValue('origineEntryLang', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryLang']);
\dcCore::app()->tpl->addValue('origineEntryPrintURL', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryPrintURL']);

// Template tags used in combination with origineConfig plugin.
\dcCore::app()->tpl->addValue('origineConfigActivationStatus', [__NAMESPACE__ . '\tplOrigineTheme', 'origineConfigActivationStatus']);
\dcCore::app()->tpl->addValue('origineSeparator', [__NAMESPACE__ . '\tplOrigineTheme', 'origineSeparator']);
\dcCore::app()->tpl->addValue('originePostListType', [__NAMESPACE__ . '\tplOrigineTheme', 'originePostListType']);
\dcCore::app()->tpl->addValue('origineEntryIfSelected', [__NAMESPACE__ . '\tplOrigineTheme', 'origineEntryIfSelected']);
\dcCore::app()->tpl->addValue('origineFooterCredits', [__NAMESPACE__ . '\tplOrigineTheme', 'origineFooterCredits']);
\dcCore::app()->addBehavior('publicCommentFormAfterContent', [__NAMESPACE__ . '\tplOrigineTheme', 'origineCommentsMarkdown']);
\dcCore::app()->tpl->addBlock('origineCommentLinks', [__NAMESPACE__ . '\tplOrigineTheme', 'origineCommentLinks']);
\dcCore::app()->tpl->addBlock('origineWidgetsNav', [__NAMESPACE__ . '\tplOrigineTheme', 'origineWidgetsNav']);
\dcCore::app()->tpl->addBlock('origineWidgetsExtra', [__NAMESPACE__ . '\tplOrigineTheme', 'origineWidgetsExtra']);
\dcCore::app()->tpl->addBlock('origineFooter', [__NAMESPACE__ . '\tplOrigineTheme', 'origineFooter']);
\dcCore::app()->tpl->addValue('origineInlineStyles', [__NAMESPACE__ . '\tplOrigineTheme', 'origineInlineStyles']);

class tplOrigineTheme
{
    /**
     * Adds meta tags in the <head> section depending on the blog settings.
     */
    public static function origineHeadMeta()
    {
        // Adds the name of the editor.
        if (\dcCore::app()->blog->settings->system->editor) {
            echo '<meta name="author" content="', \dcCore::app()->blog->settings->system->editor, '" />', "\n";
        }

        // Adds the content of the copyright notice.
        if (\dcCore::app()->blog->settings->system->copyright_notice) {
            echo '<meta name="copyright" content="', \dcCore::app()->blog->settings->system->copyright_notice, '" />', "\n";
        }
    }

    /**
     * Displays navigation links for screen readers.
     */
    public static function origineScreenReaderLinks()
    {
        $links  = '<a id="skip-content" class="skip-links" href="#site-content">';
        $links .= __('Skip to content');
        $links .= '</a>';

        // If simpleMenu exists, is activated and a menu has been set.
        if (\dcCore::app()->plugins->moduleExists('simpleMenu') && \dcCore::app()->blog->settings->system->simpleMenu_active === true) {
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
        <?php if (\dcCore::app()->ctx->posts->post_lang !== \dcCore::app()->blog->settings->system->lang) {
            echo " lang=\"" . \dcCore::app()->ctx->posts->post_lang . "\"";
        } ?>';
    }

    /**
     * Creates an URL of the current post without http or https
     * to be displayed on printing only.
     */
    public static function origineEntryPrintURL()
    {
        return '<?php echo str_replace([\'http://\', \'https://\'], "", \dcCore::app()->ctx->posts->getURL()); ?>';
    }

    // Template tags used in combination with origineConfig plugin.

    /**
     * Returns true if the plugin origineConfig
     * is installed and activated.
     *
     * To support the user’s settings, the version of the plugin must be superior to 2.0
     */
    public static function origineConfigActivationStatus()
    {
        if (\dcCore::app()->plugins->moduleExists('origineConfig') === true && version_compare('2.0', \dcCore::app()->plugins->moduleInfo('origineConfig', 'version'), '<=') && \dcCore::app()->blog->settings->origineConfig->active === true) {
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
        $plugin_activated = self::origineConfigActivationStatus();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->global_separator === "/")) {
            return "/";
        } else {
            return \dcCore::app()->blog->settings->origineConfig->global_separator;
        }
    }

    /**
     * Loads the right entry list template based on origineConfig settings.
     * Default: standard
     */
    public static function originePostListType()
    {
        $plugin_activated = self::origineConfigActivationStatus();

        if ($plugin_activated === false) {
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
        $plugin_activated = self::origineConfigActivationStatus();

        if ($plugin_activated === false) {
            $tpl = 'standard';
        } else {
            $tpl = \dcCore::app()->blog->settings->origineConfig->content_post_list_type;
        }

        if ($tpl === 'standard' || $tpl === 'full') {
            $label = __('Selected post');
        } else {
            $label = __('Selected');
        }

        return '
        <?php
        if (\dcCore::app()->ctx->posts->post_selected === "1") {
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
        $plugin_activated = self::origineConfigActivationStatus();
        $the_footer             = '';

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->footer_credits === true)) {
            $the_footer .= '<div class="widget" id="site-footer-ad">';

            $url_dotclear  = __('https://dotclear.org/');
            $text_dotclear = __('Dotclear');
            $text_origine  = __('Origine');

            if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
                $url_origine = __('https://themes.dotaddict.org/galerie-dc2/details/origine');
            } else {
                $dotclear_version        = \dcCore::app()->getVersion('core');
                $dotclear_version_parts  = explode('-', $dotclear_version);
                $dotclear_version_simple = $dotclear_version_parts[0] ? $dotclear_version_parts[0] : $dotclear_version;

                $text_dotclear .= ' ' . $dotclear_version_simple;
                $url_origine    = __('https://github.com/te2dy/origine');
                $text_origine  .= ' ' . \dcCore::app()->themes->moduleInfo('origine', 'version');
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
        if (\dcCore::app()->blog->settings->system->markdown_comments === true) {
            echo '<div class="form-entry text-italic text-small">',
            __('Markdown language allowed (<a href="https://commonmark.org/help/" rel="nofollow">help</a>).'),
            '</div>';
        }
    }

    /**
     * Displays a link to the comment feed and trackbacks.
     */
    public static function origineCommentLinks($attr, $content)
    {
        $plugin_activated = self::origineConfigActivationStatus();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->content_comment_links === true)) {
            return $content;
        }
    }

    /**
     * Displays navigation widgets.
     */
    public static function origineWidgetsNav($attr, $content)
    {
        $plugin_activated = self::origineConfigActivationStatus();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->widgets_nav_position !== 'disabled')) {
            return $content;
        }
    }

    /**
     * Displays extra widgets.
     */
    public static function origineWidgetsExtra($attr, $content)
    {
        $plugin_activated = self::origineConfigActivationStatus();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->widgets_extra_enabled === true)) {
            return $content;
        }
    }

    /**
     * Displays the footer.
     */
    public static function origineFooter($attr, $content)
    {
        $plugin_activated = self::origineConfigActivationStatus();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->footer_enabled === true)) {
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
        $plugin_activated = self::origineConfigActivationStatus();

        if ($plugin_activated === false) {
            $styles  = ':root{';
            $styles .= '--order-content:2;';
            $styles .= '--order-widgets-nav:3;';
            $styles .= '--order-widgets-extra:4;';
            $styles .= '--order-footer:5;';
            $styles .= '--font-family:"Iowan Old Style","Apple Garamond",Baskerville,"Times New Roman","Droid Serif",Times,"Source Serif Pro",serif,"Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";';
            $styles .= '--font-size:1em;';
            $styles .= '--text-align:left;';
            --content-order:2;--widgets-nav-order:3;--widgets-extra-order:4;--footer-order:5;
            $styles .= '--color-background:#fff;';
            $styles .= '--color-text-primary:#000;';
            $styles .= '--color-text-secondary:#595959;';
            $styles .= '--color-link:#de0000;';
            $styles .= '--color-border:#aaa;';
            $styles .= '--color-input-text:#000;';
            $styles .= '--color-input-text-hover:#fff;';
            $styles .= '--color-input-background:#eaeaea;';
            $styles .= '--color-input-background-hover:#000';
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
        } else {
            $styles = \dcCore::app()->blog->settings->origineConfig->global_css ? \dcCore::app()->blog->settings->origineConfig->global_css : '';
        }

        return '<style>' . $styles . '</style>';
    }
}
