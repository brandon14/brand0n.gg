//--------------------------------------------------------------------------
// Tailwind configuration
//--------------------------------------------------------------------------
//
// Use the Tailwind configuration to completely define the current sites
// design system by adding and extending to Tailwinds default utility
// classes. Various aspects of the config are split inmultiple files.
//

const defaultTheme = require('tailwindcss/defaultTheme');
const plugin = require('tailwindcss/plugin');
const customColors = require('./tailwind.config.colors');

// Keep our custom colors so we can use them in the CMS panel.
const whitelistPatterns = Object.keys(customColors).map((colorName) => {
  return new RegExp(colorName);
});

module.exports = {
  // The various configurable Tailwind configuration files.
  presets: [
    require('tailwindcss/defaultConfig'),
    require('./tailwind.config.typography.js'),
    require('./tailwind.config.peak.js'),
    require('./tailwind.config.site.js'),
  ],
  darkMode: 'class',
  // Configure Purge CSS.
  purge: {
    content: [
      './resources/views/**/*.html',
      './resources/views/**/*.blade.php',
      './resources/js/**/*.js',
      './content/**/*.md'
    ],
    layers: ['components', 'utilities'],
    options: {
      safelist: {
        standard: whitelistPatterns,
        deep: [/^content$/],
      },
    },
  },
  variants: {
    inset: ['responsive', 'hover'],
    extend: {
      typography: ['dark'],
    },
  },
};
