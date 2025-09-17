# Copilot Instructions for Jackrabbit Calendar Plugin

## Overview
- This is a WordPress plugin integrating with the Jackrabbit Class management system to display class schedules in multiple calendar and list formats.
- The frontend is built with Vue.js 3 (see `_vue/`), using Laravel Mix and Tailwind CSS for asset management and styling.
- The backend is PHP, using standard WordPress plugin architecture and the Settings API.
- Data is fetched from the Jackrabbit API and exposed to Vue via the `window.JACKCA_CALENDAR` object.

## Key Directories & Files
- `setup.php`: Main plugin entry point.
- `includes/`: PHP logic, admin, and shortcode registration.
- `_vue/`: Vue.js source code for all calendar/list views.
  - `jack-2-list-views/src/App.vue`: Example of a main Vue component.
  - `shared/mixins/JackrabbitData.js`: Shared data-fetching and filter logic for Vue components.
- `assets/`: Compiled JS/CSS output from Laravel Mix.

## Build & Development
- To build frontend assets:
  ```sh
  cd _vue
  npm install
  npm run mix
  ```
- Each calendar/list view has its own subfolder in `_vue/` with a `package.json` and `webpack.mix.js`.
- Use `mix` or `mix watch` scripts for development.
- Tailwind CSS is configured per view (see `tailwind.config.js`).

## Frontend Patterns
- Vue components use the Options API and often import shared mixins for data and filter logic.
- Data is loaded from the Jackrabbit API endpoint set in `window.JACKCA_CALENDAR.endpoint`.
- Filtering logic is centralized in `JackrabbitData.js` and exposed as component data/methods.
- UI state (e.g., view mode, filters, modals) is managed in component `data` and `computed` properties.
- Use the `label_tutition` and `label_monthly_tuition` computed properties for fee labels, sourced from `window.JACKCA_CALENDAR.fee_labels`.

## Integration Points
- Shortcodes (see README) are the main integration with WordPress content.
- All user-facing views are rendered via Vue components mounted on shortcode output containers.
- Settings and customization are managed via the WordPress admin and passed to JS via localized variables.

## Conventions
- Use English for code and UI, but some comments may be in Spanish.
- Use the provided filter and category logic; do not hardcode filter values.
- Always use the shared mixin for data loading and filtering in new views.

## Examples
- See `App.vue` in each view for usage of filters, computed properties, and modal logic.
- See `shared/mixins/JackrabbitData.js` for API/data conventions.

---
For questions about project-specific patterns, check the README or contact the plugin author.
