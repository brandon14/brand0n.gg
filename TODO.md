# brand0n.gg TODO

## Version 1.0 TODO:

### Backend

- Clean up Bgg section and write tests for ApexApi service and fix broken tests.
- Document all classes, reformat, add strict types, etc, etc.
- Clean up currently documented classes to ensure they make sense and are concise.
- Optimize for performance with potentially full static caching, etc.
- Add in all used `.env.` variables and clean up `.env.example`, plus force redo live and dev `.env`

### Frontend

- Reformat antlers templates.
- Convert all templates to work with dark mode.
- Fix search form and header/mobile menu.
- Refine website theme, and possibly tweak blueprints to make maintaining/adding stuff easier.
- Clean up SEO and fully set it up along with social images and webmanifests, etc.
- Clean up frontend design and make sure all of page_builder works like I need to build the pages I want.

### Build Pipeline

- Add in phpunit tests on composer install and update scripts (composer run-script test:phpunit).
- Fix and enable linting and static analysis.
- Lint all PHP, JS and Sass. Add strict types to PHP files, make sure purgeCss is optimal and
  JS is minified properly.
- Make a build process to add features, hotfixes, etc with release versions and proper branch management.
- Shoot for near 100% code coverage of PHP and JS.
