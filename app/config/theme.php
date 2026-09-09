<?php
/**
 * =============================================================================
 *  CENTRAL UI / THEME CONFIGURATION  —  SINGLE SOURCE OF TRUTH
 * =============================================================================
 *  Every colour, font, radius, shadow, spacing and component style used across
 *  the whole application is defined here. Each key below is emitted as a CSS
 *  custom property (`--ui-<key>`) by app/views/layouts/theme.php and consumed
 *  by public/css/app.css.
 *
 *  Change a value here and it updates EVERYWHERE automatically.
 *  Never hard-code colours, radii or shadows in a view again.
 * =============================================================================
 */

return [

    /* ---------------------------------------------------------------------
     |  BRAND
     * --------------------------------------------------------------------- */
    'brand-name'            => 'ACCOUNTING',
    'brand-tagline'         => 'Finance Suite',
    'brand-icon'            => 'bi-calculator-fill',

    /* ---------------------------------------------------------------------
     |  CORE PALETTE
     |  dark  = #283237 (primary dark)   accent = #ffc107 (accent)
     * --------------------------------------------------------------------- */
    'dark'                  => '#283237',
    'dark-2'                => '#20292d',
    'dark-3'                => '#334046',
    'dark-soft'             => 'rgba(40, 50, 55, .06)',
    'dark-rgb'              => '40, 50, 55',

    'accent'                => '#ffc107',
    'accent-hover'          => '#ffcd2e',
    'accent-soft'           => 'rgba(255, 193, 7, .12)',
    'accent-line'           => 'rgba(255, 193, 7, .28)',
    'accent-contrast'       => '#283237',
    'accent-rgb'            => '255, 193, 7',

    /* ---------------------------------------------------------------------
     |  BACKGROUNDS & SURFACES
     * --------------------------------------------------------------------- */
    'body-bg'               => '#faf8f2', // Neutral warm sand
    'surface'               => '#ffffff', // Clean white
    'surface-2'             => '#f5f0e3', // Subtle beige-gold for side panels
    'surface-3'             => '#ede3cb', // Defined warm border/highlight layer

    /* ---------------------------------------------------------------------
     |  TEXT
     * --------------------------------------------------------------------- */
    'text'                  => '#1e272b',
    'text-muted'            => '#6b797f',
    'text-subtle'           => '#93a0a6',
    'text-on-dark'          => '#ffffff',
    'text-on-dark-muted'    => 'rgba(255, 255, 255, .68)',

    /* ---------------------------------------------------------------------
     |  BORDERS
     * --------------------------------------------------------------------- */
    'border'                => '#e4e9ec',
    'border-strong'         => '#d3dade',
    'border-dark'           => 'rgba(255, 255, 255, .09)',
    'border-width'          => '1px',

    /* ---------------------------------------------------------------------
     |  STATUS COLOURS
     * --------------------------------------------------------------------- */
    'success'               => '#12805c',
    'success-soft'          => 'rgba(18, 128, 92, .10)',
    'danger'                => '#c0394b',
    'danger-soft'           => 'rgba(192, 57, 75, .10)',
    'warning'               => '#b1740b',
    'warning-soft'          => 'rgba(177, 116, 11, .12)',
    'info'                  => '#20708f',
    'info-soft'             => 'rgba(32, 112, 143, .10)',

    /* ---------------------------------------------------------------------
     |  TYPOGRAPHY
     * --------------------------------------------------------------------- */
    'font-body'             => "'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif",
    'font-heading'          => "'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif",
    'font-mono'             => "'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace",
    'font-size'             => '0.9rem',
    'font-size-sm'          => '0.8rem',
    'font-size-xs'          => '0.72rem',
    'font-weight'           => '500',
    'font-weight-medium'    => '600',
    'font-weight-bold'      => '700',
    'line-height'           => '1.55',
    'heading-weight'        => '700',
    'heading-tracking'      => '-0.02em',
    'label-tracking'        => '0.04em',

    /* ---------------------------------------------------------------------
     |  RADII
     * --------------------------------------------------------------------- */
    'radius-xs'             => '6px',
    'radius-sm'             => '8px',
    'radius'                => '10px',
    'radius-lg'             => '14px',
    'radius-xl'             => '18px',
    'radius-pill'           => '999px',

    /* ---------------------------------------------------------------------
     |  SHADOWS
     * --------------------------------------------------------------------- */
    'shadow-xs'             => '0 1px 2px rgba(30, 39, 43, .05)',
    'shadow-sm'             => '0 2px 8px rgba(30, 39, 43, .05)',
    'shadow-md'             => '0 10px 28px rgba(30, 39, 43, .06)',
    'shadow-lg'             => '0 20px 46px rgba(30, 39, 43, .10)',
    'shadow-focus'          => '0 0 0 .2rem rgba(255, 193, 7, .28)',
    'shadow-sidebar'        => '1px 0 0 rgba(255, 255, 255, .04)',

    /* ---------------------------------------------------------------------
     |  LAYOUT
     * --------------------------------------------------------------------- */
    'sidebar-width'         => '272px',
    'sidebar-width-mini'    => '82px',
    'navbar-height'         => '68px',
    'page-gutter'           => '1.5rem',
    'page-max'              => '1680px',
    'transition'            => '.2s ease',

    /* ---------------------------------------------------------------------
     |  SIDEBAR (dark, subtle accents, no glow)
     * --------------------------------------------------------------------- */
    'sidebar-bg'            => '#283237',
    'sidebar-bg-2'          => '#20292d',
    'sidebar-link'          => 'rgba(255, 255, 255, .78)',
    'sidebar-link-hover-bg' => 'rgba(255, 255, 255, .06)',
    'sidebar-link-hover'    => '#ffffff',
    'sidebar-active-bg'     => 'rgba(255, 193, 7, .10)',
    'sidebar-active-text'   => '#ffffff',
    'sidebar-active-bar'    => '#ffc107',
    'sidebar-icon'          => 'rgba(255, 255, 255, .55)',
    'sidebar-icon-active'   => '#ffc107',
    'sidebar-section-label' => 'rgba(255, 255, 255, .38)',
    'sidebar-radius'        => '8px',

    /* ---------------------------------------------------------------------
     |  NAVBAR
     * --------------------------------------------------------------------- */
    'navbar-bg'             => 'rgba(255, 255, 255, .88)',
    'navbar-border'         => '#e4e9ec',
    'navbar-shadow'         => '0 1px 0 rgba(30, 39, 43, .04)',

    /* ---------------------------------------------------------------------
     |  CARDS
     * --------------------------------------------------------------------- */
    'card-bg'               => '#ffffff',
    'card-border'           => '#e4e9ec',
    'card-radius'           => '14px',
    'card-shadow'           => '0 2px 8px rgba(30, 39, 43, .05)',
    'card-padding'          => '1.15rem',
    'card-header-bg'        => '#ffffff',

    /* ---------------------------------------------------------------------
     |  TABLES  (dark header, white text, thin accent underline)
     * --------------------------------------------------------------------- */
    'table-radius'          => '14px',
    'table-head-bg'         => '#283237',
    'table-head-text'       => '#ffffff',
    'table-head-underline'  => 'rgba(255, 193, 7, .55)',
    'table-head-size'       => '0.72rem',
    'table-head-weight'     => '700',
    'table-head-tracking'   => '.04em',
    'table-row-border'      => '#eef1f3',
    'table-stripe'          => '#fafbfc',
    'table-hover'           => '#f4f7f8',
    'table-cell-padding'    => '.8rem 1rem',
    'table-cell-padding-x'  => '1rem',

    /* ---------------------------------------------------------------------
     |  BUTTONS  (change here → every button in the app updates)
     * --------------------------------------------------------------------- */
    'btn-radius'            => '8px',
    'btn-height'            => '40px',
    'btn-padding'           => '.5rem 1rem',
    'btn-font-size'         => '0.84rem',
    'btn-font-weight'       => '600',
    'btn-primary-bg'        => '#283237',
    'btn-primary-bg-hover'  => '#20292d',
    'btn-primary-text'      => '#ffffff',
    'btn-primary-border'    => '#283237',
    'btn-accent-bg'         => '#ffc107',
    'btn-accent-bg-hover'   => '#ffcd2e',
    'btn-accent-text'       => '#283237',
    'btn-light-bg'          => '#ffffff',
    'btn-light-text'        => '#1e272b',
    'btn-shadow'            => '0 1px 2px rgba(30, 39, 43, .06)',

    /* ---------------------------------------------------------------------
     |  FORMS
     * --------------------------------------------------------------------- */
    'input-bg'              => '#ffffff',
    'input-border'          => '#d9e0e4',
    'input-radius'          => '8px',
    'input-height'          => '42px',
    'input-padding'         => '.55rem .8rem',
    'input-focus-border'    => '#ffc107',
    'input-placeholder'     => '#9aa5aa',
    'label-color'           => '#1e272b',
    'label-size'            => '0.78rem',
    'label-weight'          => '600',

    /* ---------------------------------------------------------------------
     |  BADGES / PILLS / MISC
     * --------------------------------------------------------------------- */
    'badge-radius'          => '999px',
    'badge-padding'         => '.36rem .62rem',
    'badge-size'            => '0.7rem',
    'scroll-thumb'          => '#c8d1d6',

    /* ---------------------------------------------------------------------
     |  PRINTED DOCUMENTS (invoice / credit note / receipt)
     * --------------------------------------------------------------------- */
    'print-ink'             => '#000000',
    'print-line'            => '#e0e0e0',
    'print-muted'           => '#555555',
    'print-radius'          => '6px',
];
