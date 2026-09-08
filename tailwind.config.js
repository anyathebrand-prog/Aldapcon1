/** @type {import('tailwindcss').Config} */

// ALDAPCON design tokens — 04-ui-ux-brief.md §2 to §4.
//
// Every value here is quoted from the brief. Nothing has been invented,
// rounded or "improved". The contrast ratios in §2.2 were calculated against
// these exact hex values, so changing one silently invalidates a measured
// accessibility claim.
//
// The type SCALE lives in resources/css/app.css rather than here, as semantic
// classes (.t-h1, .t-body …). The brief specifies a different size for mobile
// and desktop on most steps, plus a family, weight, line height and tracking
// for each; expressing that as Tailwind utilities would scatter one table
// across every template and make the desktop step easy to forget.

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],

    theme: {
        // Replaced, not extended. Tailwind's default palette is 250 colours
        // this product must never use; leaving them available invites a
        // stray `text-blue-500` that no contrast figure covers.
        colors: {
            transparent: 'transparent',
            current: 'currentColor',

            // Brand and structure (§2.1)
            forest: {
                900: '#0A3B29', // headers, record panel, footer
                800: '#055537', // headings on light, strong emphasis
                700: '#046A44', // primary action fill, links
                100: '#E3F1E9', // active tint, selected state
                50: '#F1F7F4',  // section wash
            },

            // Identity accent ONLY — §2.3. Never body text, never a button
            // fill, never a status. 4.58:1 on white clears AA with almost no
            // margin (F-4), which is why its use is this restricted.
            flag: '#008751',

            // Neutrals (§2.1)
            ink: {
                DEFAULT: '#14201A', // green-black, not grey-black
                muted: '#4E5F56',   // metadata, captions, help text
            },
            paper: '#FAFBFA',   // page background — never pure white
            surface: '#EFF3F0', // raised sections, table headers, admin chrome
            white: '#FFFFFF',   // form fields, record panel, content areas
            rule: '#D3DDD7',    // decorative dividers — 1.49:1, never a control boundary
            border: '#73867C',  // input and control boundaries — 3.87:1, meets 1.4.11

            // Seal, secondary identity (§2.1)
            brass: {
                700: '#7A5E18', // record panel figures, receipt seal mark
                100: '#F5EEDA',
            },

            // Membership status (§2.4). Five states, five treatments.
            // Expired is slate, NOT red: an expired member is the highest-value
            // renewal prospect in the product, and red says "you failed" where
            // slate says "this needs renewing".
            status: {
                'active-fg': '#0A3B29', 'active-bg': '#E3F1E9',
                'expiring-fg': '#6E4600', 'expiring-bg': '#FBF0DC',
                'expired-fg': '#3F4F47', 'expired-bg': '#EAEEEB',
                'suspended-fg': '#8A1F16', 'suspended-bg': '#FAE7E5',
                'pending-fg': '#123A59', 'pending-bg': '#E4EEF6',
            },

            // Feedback (§2.1)
            success: { fg: '#055537', bg: '#E3F1E9', border: '#046A44' },
            warning: { fg: '#6E4600', bg: '#FBF0DC', border: '#B07400' },
            error: { fg: '#8A1F16', bg: '#FAE7E5', border: '#A32218' },
            info: { fg: '#14201A', bg: '#EFF3F0', border: '#73867C' },

            // Focus (§2.1). Never removed, never reduced to a colour change.
            focus: { light: '#046A44', dark: '#C9F2DE' },
        },

        // §4.1 — 8px base with a 4px half-step for tight component interiors.
        spacing: {
            0: '0',
            px: '1px',
            1: '4px',
            2: '8px',
            3: '12px',
            4: '16px',
            5: '24px',
            6: '32px',
            7: '40px',
            8: '48px',
            9: '64px',
            10: '80px',
            11: '96px',
            12: '128px',
        },

        // §4.4 — radius signals KIND of thing, not decoration.
        borderRadius: {
            none: '0',    // record panel, table cells, alert bars
            sm: '3px',    // inputs, selects, checkboxes, badges, tags
            md: '5px',    // buttons
            lg: '8px',    // media, images, avatars
            full: '9999px',
        },

        // §4.4 — elevation is near-absent. Depth comes from borders and
        // background steps. No card, panel, input or button carries a shadow.
        boxShadow: {
            overlay: '0 8px 24px -6px rgba(10, 59, 41, 0.18)', // dropdowns, modals, toasts ONLY
            sticky: '0 -1px 0 0 #D3DDD7',                      // mobile sticky action bar
            none: 'none',
        },

        extend: {
            fontFamily: {
                serif: ['Literata', 'Georgia', '"Times New Roman"', 'serif'],
                sans: ['Archivo', '"Segoe UI"', 'system-ui', '-apple-system', 'sans-serif'],
            },

            // §4.3 — forms are narrow on purpose. A 560px column keeps the
            // registration form (J-06, the longest in the product) from
            // sprawling into two columns people fill in the wrong order.
            maxWidth: {
                prose: '680px', // article body, About, legal pages, FAQ
                form: '560px',  // signup, login, registration, renewal
                app: '1120px',  // home, membership, events, portal
                wide: '1320px', // admin tables only
                measure: '60ch', // interface prose (§3.3)
            },

            // §4.2
            screens: {
                sm: '600px',
                md: '900px',
                lg: '1200px',
            },

            // §9 — motion is functional. This product handles other people's
            // money and professional records; flourish reads as unseriousness.
            transitionDuration: {
                micro: '120ms',    // hover, focus
                standard: '180ms', // disclosure, accordion
                overlay: '240ms',  // modal, sheet
            },
            transitionTimingFunction: {
                enter: 'cubic-bezier(0.2, 0, 0, 1)',
                exit: 'cubic-bezier(0.4, 0, 1, 1)',
            },

            // §5.1 — 44px minimum target, 48px on payment actions.
            minHeight: {
                control: '44px',
                'control-lg': '52px',
            },
            minWidth: {
                control: '44px',
            },
        },
    },

    plugins: [],
};
