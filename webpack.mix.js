const mix = require('laravel-mix');
const postcssImport = require('postcss-import');
const tailwind = require('tailwindcss');
const postCssNested = require('postcss-nested');
const postCssFocusVisible = require('postcss-focus-visible');
const postCssPresetEnv = require('postcss-preset-env');
const autoprefixer = require('autoprefixer');

mix.js('resources/js/site.js', 'public/js/site.js');

mix.sass('resources/sass/site.scss', 'public/css/site.css');

mix.options({
  processCssUrls: false,
  cssNano: { minifyFontValues: false },
  postCss: [
    postcssImport,
    tailwind,
    postCssNested,
    postCssFocusVisible,
    autoprefixer,
    postCssPresetEnv({stage: 0}),
  ],
});

mix.browserSync({
  proxy: process.env.APP_URL,
  files: [
    'resources/views/**/*.html',
    'public/**/*.(css|js)',
  ],
  // Option to open in non default OS browser.
  // browser: "firefox",
  notify: false,
});

// Copy over fontawesome fonts.
mix.copy(
  'node_modules/@fortawesome/fontawesome-free/webfonts',
  'public/webfonts',
);

// Extract vendor libs.
mix.extract([
  'alpinejs',
  'vue',
  'prisimjs',
]);

// Version assets.
if (mix.inProduction()) {
  mix.version();
}
