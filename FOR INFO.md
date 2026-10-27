# Design System

Source reviewed: `docs/TutorKita-Design-Reference.pdf` (all four pages). The reference contains a public landing page, an isolated blue mark, a sign-in composition, and a registration composition. The notes below distinguish visible evidence from inferences.

## Color palette

Colors sampled from the rendered reference pages. Gradient endpoint values are approximate because the gradient contains many interpolated shades.

- **Primary action blue:** `#0866F5` and `#0366F7` appear on calls to action, links, icons, and the large authentication panel. The small variations likely reflect separate Figma fills or rasterization.
- **Secondary blue:** the authentication and landing call-to-action gradient moves from saturated blue (`#0866F5` / `#0366F7`) toward a light sky blue near `#57A0FA`.
- **Navy / primary text:** `#0D2B5E` for headlines, navigation, labels, and body text.
- **Page surfaces:** white (`#FFFFFF`) and a very pale blue around `#EFF6FE`; form fields also use pale blue fills.
- **Muted text / illustration gray:** approximately `#818793` in placeholder tutor portraits and secondary content.
- **Borders:** light neutral gray around fields, social buttons, and tutor cards; the reference does not establish a single unambiguous border token.
- **Buttons:** saturated blue with white text for primary actions; white with blue text for the landing CTA; transparent/blue-panel treatment with a thin white outline for the sign-up CTA on the authentication panel.

## Typography

- The UI uses a clean sans-serif. The exact font family cannot be identified confidently from the rendered PDF; do not assume a particular web font based on appearance alone.
- Landing headings use bold navy, with selected words emphasized in primary blue. Authentication titles are large, bold, and navy; panel headings are bold white.
- Body copy is regular weight and smaller than headings, generally navy. Supporting labels and card details are compact.
- Button labels are medium to bold. The visual hierarchy is clear: large page/section heading, medium section heading, regular explanatory copy, then compact labels and helper text.
- The TutorKita wordmark uses its own distinct letterforms and should be treated as artwork, not recreated with UI typography.

## Layout

- The landing page uses full-width horizontal sections with centered content, generous whitespace, and multiple columns for feature items and tutor cards.
- The landing hero has text and metrics on the left and a large education photograph on the right. Below it are a four-item feature row, a full-width blue gradient statement/CTA, a centered tutor section, and a footer.
- Landing content aligns to a broad centered container with consistent outer gutters. Section contents are centered or arranged in evenly spaced columns.
- Sign-in and registration references use a two-panel composition: a white form area on the left and a full-height blue-to-sky gradient panel on the right. The form content is centered in its column, with vertically stacked fields and actions.
- Spacing is generous between major sections and more compact within form groups and cards. No exact spacing scale is established by the reference.

## UI components and recurring patterns

- **Navbar:** horizontal white header; brand at the left, text navigation near the center/right, and outlined sign-in plus filled registration actions on the right.
- **Buttons:** rounded pill-like landing CTAs and compact blue tutor-card actions; larger rounded rectangular actions on authentication screens. Primary and outlined/white variants are visible.
- **Cards:** tutor cards use a white surface, subtle outline/shadow, rounded corners, portrait area, name, short affiliation details, and a small action button.
- **Inputs:** large, pale-blue filled fields with rounded corners, a subtle gray outline/shadow, and muted placeholder text.
- **Forms:** social sign-in buttons precede email/password fields. Sign-in includes a password-recovery link; registration includes name, contact, password, confirmation, consent checkbox, and a primary action.
- **Navigation and footer:** landing navigation links; footer groups the logo and social icons, link text, and a simple email subscription field/action.
- **Icons:** simple blue outline/solid symbols for search and learning features; social platforms use recognizable marks inside rounded blue circles or outlined square controls.
- **Badges/labels:** small rounded pale/white label above the hero heading. No recurring data/status badge pattern is established.

## Visual style

- Friendly, accessible education identity: bright blue accents, navy text, white/pale-blue surfaces, encouraging copy, and student/tutor imagery.
- Rounded corners are common on buttons, fields, and tutor cards. The authentication side panel has especially large curved corners along its inner edge.
- Shadows are restrained and visible mainly on form fields, social buttons, and tutor cards; borders are light rather than heavy.
- Photography is used in the landing hero. Tutor cards use simple gray placeholder portraits. The authentication panel includes light, low-contrast education-themed line illustrations over the gradient.
- Keep iconography simple and blue on light surfaces, or white over the blue panel. Do not substitute unrelated decorative graphics or a generic dashboard visual language.

## Responsive behavior

The reference pages are composed as desktop-like frames. They do not show alternate viewport sizes, collapsed navigation, rearranged card counts, or mobile form behavior. Responsive rules cannot be determined from this reference and should be decided only when responsive designs or explicit requirements are available.

## TutorKita branding

Official assets are in `public/assets/tutorkita/`:

- `tutorkita-logo.svg` and `tutorkita-logo.png` — horizontal logo.
- `tutorkita-logo-vertikal.svg` and `tutorkita-logo-vertikal.png` — vertical logo.
- `tutorkita-logo-putih.svg` and `tutorkita-logo-putih.png` — white logo variant, intended for dark/blue surfaces when contrast is needed.
- `tutorkita-ikon.svg` and `tutorkita-ikon.png` — standalone mark.
- `tutorkita-ikon-app.svg` and `tutorkita-ikon-app.png` — app icon variant, suitable for compact app/icon contexts.

Prefer the supplied SVG files for web display when a logo is needed. Use the horizontal variant where the reference places the wordmark in a header or form column; the vertical variant where a stacked mark fits; use the white variant on blue backgrounds if its supplied colors provide contrast; use the standalone mark for compact icon-only contexts. Preserve the original files and proportions.

**Branding discrepancy to resolve:** the supplied SVG artwork uses navy `#1E3A8A` and amber `#F59E0B` in the mark, while the PDF depicts a bright-blue/navy TutorKita mark and wordmark. The PDF and supplied assets therefore do not match exactly. The assets have not been recolored; confirm which is the final authority before implementing a logo treatment intended to match the reference precisely.

## Dashboard design implications

Based only on patterns visible in the reference, future dashboard work should:

- Carry forward the blue/navy/pale-blue/white palette and friendly education tone.
- Use generous spacing, centered content regions, clear heading levels, and blue emphasis for key actions or short highlighted phrases.
- Reuse the rounded, lightly bordered/shadowed card treatment and pale-blue filled form fields where those component types are needed.
- Use the reference's blue primary buttons and restrained white/outlined variants, with legible contrast.
- Use the official TutorKita artwork in its original proportions; prefer SVG and select the variant to suit the surface.
- Keep icon treatment simple and aligned with the blue/white reference style.
- Avoid importing visual conventions not shown here, such as dark sidebars, dense metric tiles, arbitrary chart palettes, or generic SaaS dashboard chrome.

These are visual reuse guidelines, not a specification for dashboard information architecture or behavior; those details are absent from the reference.

# Dashboard MVP Implementation

## Route and structure

- Dashboard URL: `/dashboard`, named route `dashboard`.
- `app/Http/Controllers/DashboardController.php` renders `resources/views/dashboard/index.blade.php`.
- The dashboard uses the existing Blade, Tailwind CSS v4, Vite, and plain JavaScript stack. No package was added.
- Reusable pieces are the dashboard layout, sidebar and topbar partials, plus Blade components for the icon, summary card, schedule item, and tutor card.
- The existing `/` welcome route and page remain in place.

## Mock data and interactions

`app/Support/DashboardMockData.php` centralizes the sample student, summary figures, five schedule entries, three tutors, seven-day study chart, subject progress, and three notifications. It supplies the dashboard view through the controller so these collections can later be replaced at the data-provider boundary.

Client-side interactions include the mobile navigation drawer; notification and profile dropdowns; notification read state; live search across tutors and schedules; tutor subject selection; schedule tabs for all/today/upcoming; dismissible feedback toasts; and card/action hover, focus, and pressed states. Actions that need server support show an explanatory local toast and do not make a backend request.

CSS adds restrained entrance, dropdown, bar/progress, and toast animations, button/card transitions, and a `prefers-reduced-motion` override. Responsive layouts use a fixed desktop sidebar that becomes a drawer at tablet widths, stacked content sections, two-column summary cards, and single-column tutor cards on narrow mobile screens. These responsive choices were implemented for the MVP; they are not specified by the PDF.

## Assets and visual decisions

- The dashboard uses the official `public/assets/tutorkita/tutorkita-logo.svg` in the sidebar and preserves the source file as supplied.
- Tutor portrait placeholders use initials and colored CSS surfaces; no generated or external portraits were added.
- The UI carries the reference's blue/navy emphasis, white and pale-blue surfaces, rounded controls, subtle borders/shadows, simple icons, and generous section spacing into the dashboard context.
- The supplied logo artwork's navy/amber mark differs from the blue/navy logo depiction in the PDF. The official supplied SVG was used without recoloring, as the final brand authority is unresolved.

## Database boundary and future entities

All dashboard data is local mock data; there is no dashboard authentication, persistent user data, database query, model, migration, or dashboard backend. The session, cache, and queue defaults in `.env.example` and the local `.env` were set to `file`, `file`, and `sync` respectively so local page rendering does not require database-backed infrastructure. The existing `DB_CONNECTION` setting was not used or changed, and no database was created.

The current UI implies future data for users, subjects, tutors, tutor availability, scheduled lessons, learning sessions, subject/weekly progress, and notifications. These are planning implications only; none has a schema or persistence layer in this MVP.

## Assumptions and limits

- “Gusti” and the linked learner details are illustrative mock-user data, not a fixed account model.
- Schedule categories, tutor ratings, availability, progress figures, and notification states are representative sample values. Dates for the sample schedule are computed relative to the current date.
- Profile, logout, session detail, and tutor profile actions are intentionally local simulations; authentication and backend flows are out of scope.
- Mobile behavior is a practical implementation choice because the supplied reference contains desktop-like pages only.
- The public landing page was not redesigned, and no authentication pages or other dashboard routes were added.

# Public Landing Page and Dashboard Visual Update

## Pages and routes

- `/` now renders the public TutorKita landing page through `LandingController` and `resources/views/landing/index.blade.php`.
- `/dashboard` remains available through `DashboardController`; its existing mock-data provider and interactions are preserved.
- `/subjects` renders the subject foundation page through `SubjectsController` with static data from `SubjectsMockData`.
- Both pages use the shared icon Blade component and the existing Laravel Blade, Tailwind CSS v4, Vite, and JavaScript setup. No additional package was installed.

## Landing page structure

The landing page follows the available reference composition: compact white header with TutorKita logo/navigation/actions; pale-blue split hero with headline, summary, impact figures, and tutoring photo; four learning features; full-width blue gradient call-to-action; five volunteer tutor cards; and a pale-blue footer with brand, social controls, information links, and newsletter form.

The exact tutoring photo visible in the supplied PDF page-one reference was cropped from its rendered page and saved as `public/assets/images/tutorkita-hero-reference.jpg`. It is the source photo shown by the supplied reference rather than an unrelated replacement image. The crop retains the reference's soft pale-blue fade on the left edge. Official TutorKita logo artwork is used from `public/assets/tutorkita/tutorkita-logo.svg`, without editing or recoloring it.

`LandingPageData` provides four feature descriptions and five illustrative tutor profiles (names, institution, major, and subject). Tutor profile actions and social controls provide local feedback; the newsletter validates the entered email and confirms locally without sending or storing it.

## Dashboard visual update

The dashboard keeps its navigation, stats, schedule, tutors, progress, search, notifications, profile menu, and mobile drawer. Styling accents were brought closer to the public page's blue/navy/pale-blue palette; non-blue subject accents were softened to blue/neutral tones. The dashboard welcome panel also uses the same cropped study photo as a subtle background treatment. Existing mock data and local-only action behavior remain in `DashboardMockData` and its Blade components. Development-only wording was removed from dashboard toasts and interface actions; no such labels should appear to site visitors.

## Components, animation, and responsive behavior

Landing structure is split into `layouts/landing.blade.php`, header/footer partials, and reusable feature/tutor-card components. The existing dashboard layout, partials, and stat/schedule/tutor/icon components remain.

The landing page uses a restrained hero entrance, intersection-based section reveal, feature/tutor hover elevation, button/social transitions, an animated mobile menu, and dismissible local feedback. Reduced-motion preferences are honored. Responsive rules collapse the header to a menu, stack the hero and photo, move features to two columns, reduce tutor cards to one column on phones and two or three columns on wider screens, and stack the footer. These are implementation choices; the PDF depicts desktop page compositions only. Dashboard breakpoints keep navigation as a drawer on smaller screens, stack schedule/progress content, and reduce tutor cards to a single column on narrow phones.

The landing header's “Masuk” and “Daftar Sekarang” actions link to `/login` and `/register`. The forms validate locally, then open `/dashboard` as a frontend-only flow; they do not authenticate or save an account.

## Current integration limits and future data

Neither page uses authentication or makes backend requests. The dashboard learner, summaries, schedule, tutor, progress, and notification data are local examples. Landing tutor profiles and newsletter confirmation are also local-only. The project environment uses file sessions/cache and a synchronous queue so pages do not depend on database-backed services; no database was created or configured.

Future entities implied by the two pages include users, student profiles, subjects, tutors and their institutions/subjects, tutor availability, schedules, learning sessions, progress, notifications, and newsletter subscriptions. The landing tutor and impact figures, contact/social destinations, newsletter subscription, sign-up, login, profile edits, logout, and all dashboard records need real service/data sources before those actions become persistent. No database schema or backend for them exists.

The supplied SVG branding remains navy/amber while the PDF reference depicts blue/navy artwork. The official supplied SVG remains unaltered; resolving that source discrepancy requires a final brand asset decision.

# Landing and Dashboard Refinements

## Landing refinements

- The “Daftar Sekarang” action uses `white-space: nowrap`, a minimum-content width, and responsive horizontal padding so the label remains on one line in desktop and mobile navigation.
- Soft radial highlights, a slowly shifting blue CTA gradient, and one slow decorative ring add movement while keeping the blue/navy/pale-blue palette. Hero copy/photo entrances, staggered feature reveal, scroll reveals, button/card hover and press transitions, tutor portrait micro-interaction, and a light footer reveal are implemented in CSS and the existing JavaScript.
- `prefers-reduced-motion` disables the motion effects and reveals content immediately.
- Footer contact person is shown as `082143592001` and links to `tel:082143592001`.

## Subject navigation and foundation page

- The dashboard “Mata Pelajaran” item now targets `/subjects` and receives the active navigation style there. Tutor, schedule, and progress sidebar links explicitly return to their dashboard anchors instead of relying on the current page URL.
- `/subjects` uses the shared dashboard layout, sidebar, profile menu, and top navigation. It contains a heading, explanatory text, six subject cards, learning progress indicators, jenjang filter, shared search field, and local “Lihat materi” feedback.
- `app/Support/SubjectsMockData.php` stores each subject's category, levels, progress, icon, tutor count, and description. Search and level filtering are client-side; there is no database, CRUD, or backend integration.

## Dashboard visual and motion refinements

- The dashboard background has a pale-blue radial accent, active navigation has a soft blue fill and indicator, and cards keep the shared light surfaces and blue/navy color direction.
- Summary values animate in, stat icons react on hover, schedule rows enter with a small stagger and highlight on hover, progress bars animate from their initial state, and tutor cards/avatars have restrained hover transitions.
- The subject page shares dashboard search, responsive navigation drawer, and page-title configuration.

## Responsive notes

- The landing header actions never wrap their text; mobile menu actions use proportional grid columns. Hero, features, tutor cards, footer, and subject cards change column count at tablet/mobile widths.
- The footer changes to a two-column tablet arrangement and one-column mobile arrangement to preserve room for the contact link and newsletter form.
- The subjects grid shows three cards on wide screens, two on tablet, and one on narrow phones. Dashboard breakpoints remain in place.
- These breakpoint choices are implementation decisions. A rendered browser viewport check was not available during this update; responsive CSS was reviewed and the Vite output was built.

# Frontend Interaction and Motion Update

## Landing Page

- Sections remain aligned with the TutorKita reference: compact navigation, pale-blue education hero, four feature items, a short About block, blue learning call-to-action, volunteer tutor cards, and footer.
- The header sticks to the top. Scrolling adds a light translucent surface and subtle shadow; the navigation indicator tracks the visible home, About, and tutor sections.
- Hero copy enters in a staggered sequence. The reference photo uses a small image reveal and very restrained hover scale. Soft radial shapes remain clipped to the hero section; the blue CTA uses slow ambient movement.
- Feature buttons respond with a small lift, icon scale, accent line, and local informational toast. Tutor cards use hover feedback; “Tentang Guru” opens a keyboard-accessible local detail modal populated from mock data. Tutor search filters by tutor name, institution, major, and subject without reloading.
- Login/registration, social buttons, modal contact action, and newsletter feedback are local only. The newsletter checks email format in the browser and sends no request. The footer contact remains `tel:082143592001`.
- Mock profiles and feature descriptions live in `app/Support/LandingPageData.php`. Persistent future data would include tutor profiles, institutions, subjects, availability, ratings/feedback, impact counts, and newsletter subscriptions.

## Dashboard

- `/dashboard` keeps its current welcome panel, statistics, schedule, progress chart, subject progress, recommended tutors, closing call-to-action, and footer.
- Local interactions include stat-card anchor navigation, schedule tabs, search/filter, notification and profile menus, mark-as-read feedback, mobile sidebar drawer, and action toasts. Cards, sidebar icons, schedule arrows/statuses, progress bars, and buttons use restrained hover/entrance feedback.
- `app/Support/DashboardMockData.php` supplies the sample learner, statistics, schedules, tutors, progress, and notifications. Future persistent data would include student profiles, tutor assignments, schedules, learning sessions, progress, and notification state.
- The sidebar “Mata Pelajaran” link targets the existing `/subjects` route.

## Subjects

- `/subjects` retains its six local subject cards with category, school levels, progress, tutor counts, icons, and descriptions.
- The shared dashboard search and level selector filter cards locally. “Lihat materi” gives local feedback; subject cards and progress indicators have hover/focus treatment.
- Mock records live in `app/Support/SubjectsMockData.php`. Future data would include subjects, grade levels, learning materials, tutor availability by subject, and student enrollment/progress.

## Future Database Candidates

These are planning candidates only. No database, schema, migration, model, or database query was added for this work:

- `users` and `student_profiles` for account and learner details.
- `subjects` and `learning_materials` for subject catalogs and lessons.
- `tutors`, `tutor_subjects`, and `tutor_availability` for tutor profiles and schedules.
- `schedules` and `learning_sessions` for booked and completed tutoring sessions.
- `progress` for learner and subject progress history.
- `notifications` for account-specific notification state.
- `newsletter_subscriptions` for opt-in email subscriptions.

All future pages should keep the supplied TutorKita logo artwork intact, use the established bright-blue/navy/white/pale-blue palette, keep motion subtle, preserve keyboard access, and honor `prefers-reduced-motion`. Responsive behavior is implemented from the explicit project requirements; the design PDF itself only shows desktop compositions.

## Authentication Pages

### Login

- Route: `GET /login` (`login` named route), rendered by `resources/views/auth/login.blade.php` in the shared `resources/views/layouts/auth.blade.php` layout.
- UI follows the supplied sign-in screenshot: white form panel with official horizontal TutorKita logo, four social sign-in controls, email divider, email/phone and password fields, recovery feedback, and a rounded blue gradient panel with paper-plane/book illustrations and a register link. The small illustrations are inline SVG; no extra image package or substitute TutorKita logo was added.
- Frontend validation requires an email or phone number in a basic valid format and a non-empty password. Feedback is shown in an accessible inline status area; social sign-in and recovery remain local feedback only, and the form does not send a request.
- On valid input, the UI confirms validation and navigates to `/dashboard` as a local frontend flow. No session or authenticated state is created.
- Google, Facebook, GitHub, LinkedIn, and password-recovery actions show local availability feedback only. The right panel CTA links to `/register`.
- The “Daftar sekarang” link opens `/register`.

### Register

- Route: `GET /register` (`register` named route), rendered by `resources/views/auth/register.blade.php` in the shared authentication layout.
- Fields: full name, email or phone number, password, password confirmation, and agreement to the terms/privacy notice. The layout follows the supplied registration screenshot, including the social sign-in row and the blue gradient illustration panel.
- Frontend validation checks required fields, basic email/phone format, agreement, password length of at least eight characters, and matching confirmation. Feedback is inline and accessible; no request is sent.
- On valid input, the UI confirms validation and navigates to `/dashboard` as a local frontend flow. No account is saved or authenticated.
- The “Masuk” link opens `/login`.

Both pages keep the split panel while the viewport is wide enough; below 520px the blue panel moves below the form. Entrance and ambient animations follow the shared reduced-motion preference.

Authentication and database persistence are not active. Future support will require account and learner-profile records, secure server-side password handling, authentication/session management, and authorization. This frontend-only work adds no migration, model, database, or backend authentication handler.
