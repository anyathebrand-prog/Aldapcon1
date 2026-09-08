/** @type {import('tailwindcss').Config} */

// Phase 1 ships the wiring only. Phase 2 replaces this with the full token
// set from 04-ui-ux-brief.md §2–§4 — colour, type scale, spacing, radius.
// Nothing here should be treated as a design decision.
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
