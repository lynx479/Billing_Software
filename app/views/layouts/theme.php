<?php
/**
 * Theme bridge: turns app/config/theme.php into CSS custom properties.
 * Included once by the layout header — never duplicate styles in a view.
 */
if (!isset($GLOBALS['UI_THEME']) || !is_array($GLOBALS['UI_THEME'])) {
    $GLOBALS['UI_THEME'] = require __DIR__ . '/../../config/theme.php';
}
$theme = $GLOBALS['UI_THEME'];

/** Read a single theme token from anywhere (views, controllers). */
if (!function_exists('ui')) {
    function ui($key, $fallback = '')
    {
        $theme = isset($GLOBALS['UI_THEME']) ? $GLOBALS['UI_THEME'] : [];
        return isset($theme[$key]) ? $theme[$key] : $fallback;
    }
}

/** Reference a token as a CSS variable, e.g. ui_var('accent') -> var(--ui-accent) */
if (!function_exists('ui_var')) {
    function ui_var($key)
    {
        return 'var(--ui-' . $key . ')';
    }
}

/** Emit every theme token as a CSS custom property. Called once from the <head>. */
if (!function_exists('ui_theme_css')) {
    function ui_theme_css()
    {
        $theme = isset($GLOBALS['UI_THEME']) ? $GLOBALS['UI_THEME'] : [];
        $skip  = ['brand-name', 'brand-tagline', 'brand-icon'];
        echo "<style id=\"ui-theme-tokens\">\n:root {\n";
        foreach ($theme as $key => $value) {
            if (in_array($key, $skip, true)) {
                continue;
            }
            echo '    --ui-' . $key . ': ' . $value . ";\n";
        }
        echo "}\n</style>\n";
    }
}
