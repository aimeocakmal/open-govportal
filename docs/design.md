# Design Documentation

## Overview

OpenGovPortal follows the **Malaysian Government Design System (MyDS)** to ensure consistency, accessibility, and trust across all government digital services.

**Design System:** MyDS (Malaysian Government Design System)
**GitHub:** [github.com/govtechmy/myds](https://github.com/govtechmy/myds)
**Design Guidelines:** [design.digital.gov.my/en/docs/design](https://design.digital.gov.my/en/docs/design)
**Component Library:** [design.digital.gov.my/en/docs/develop](https://design.digital.gov.my/en/docs/develop)
**Figma:** [MyDS Beta](https://www.figma.com/design/BwyAzgDaGno8QhaoTtLBMx/MYDS--Beta-?node-id=7-20696&t=xrPkDdDl0w2DqlwX-1)

### Implementation Approach

MyDS ships as React components (`@govtechmy/myds-react`) and CSS (`@govtechmy/myds-style`). Since OpenGovPortal uses the TALL stack (Tailwind + Alpine.js + Laravel + Livewire), we:

1. **Use the MyDS design tokens** — colors, typography, spacing, shadows, radius, motion
2. **Re-implement components in Blade + Alpine.js** — matching the MyDS visual spec exactly
3. **Reference CSS-only (TA) components** — MyDS provides 16 Transistory Assistance components that work without React
4. **Do NOT import the React package** — all components are Blade/Alpine.js native

---

## Design Principles

1. **Beautiful Government Sites** — Clean, minimalist design ensuring information is easy to find
2. **Rapid Development** — Pre-built visual and functional elements for efficient development
3. **Cost Savings** — Accelerated design and development, saving valuable taxpayer ringgits
4. **Compliant with Standards** — Full adherence to Jabatan Digital Negara's design principles
5. **Accessible by Design** — Out-of-the-box adherence to WCAG 2.2 AA and best accessibility practices
6. **Trusted by Citizens** — Familiar look and feel, increasing citizens' trust in the government's digital presence

---

## Grid System (12-8-4)

MyDS uses a responsive grid system that adapts across breakpoints.

| Breakpoint | Screen Width | Columns | Column Gap | Max Content Width |
|---|---|---|---|---|
| **Desktop** | >= 1024px | 12 | 24px | 1280px |
| **Tablet** | 768px–1023px | 8 | 24px | — |
| **Mobile** | <= 767px | 4 | 18px | — |

**Content width constraints:**
- Article paragraph max width: 640px
- Image/chart max width: 740px

**Tailwind implementation:**

```html
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <!-- 1280px max, responsive padding -->
</div>
```

---

## Color Palette

### Primitive Colors

MyDS defines full 11-shade scales for each color. Values use RGB space-separated format for alpha compositing support.

#### Primary (Blue)

| Token | RGB | Hex | Usage |
|---|---|---|---|
| `primary-50` | 239 246 255 | `#EFF6FF` | Light backgrounds |
| `primary-100` | 219 234 254 | `#DBEAFE` | Subtle fills |
| `primary-200` | 194 213 255 | `#C2D5FF` | Light borders |
| `primary-300` | 150 183 255 | `#96B7FF` | — |
| `primary-400` | 99 148 255 | `#6394FF` | — |
| `primary-500` | 58 117 246 | `#3A75F6` | — |
| `primary-600` | 37 99 235 | `#2563EB` | **Primary actions, buttons, links** |
| `primary-700` | 29 78 216 | `#1D4ED8` | **Hover states** |
| `primary-800` | 30 64 175 | `#1E40AF` | **Active/pressed** |
| `primary-900` | 30 58 138 | `#1E3A8A` | Dark text on light |
| `primary-950` | 23 37 84 | `#172554` | Darkest |

#### Danger (Red)

| Token | RGB | Hex | Usage |
|---|---|---|---|
| `danger-50` | 254 242 242 | `#FEF2F2` | Error backgrounds |
| `danger-100` | 254 226 226 | `#FEE2E2` | — |
| `danger-200` | 254 202 202 | `#FECACA` | Light borders |
| `danger-300` | 252 165 165 | `#FCA5A5` | — |
| `danger-400` | 248 113 113 | `#F87171` | — |
| `danger-500` | 239 68 68 | `#EF4444` | **Error text, icons** |
| `danger-600` | 220 38 38 | `#DC2626` | **Destructive buttons** |
| `danger-700` | 185 28 28 | `#B91C1C` | Hover |
| `danger-800` | 153 27 27 | `#991B1B` | — |
| `danger-900` | 127 29 29 | `#7F1D1D` | — |
| `danger-950` | 69 10 10 | `#450A0A` | Darkest |

#### Success (Green)

| Token | RGB | Hex | Usage |
|---|---|---|---|
| `success-50` | 240 253 244 | `#F0FDF4` | Success backgrounds |
| `success-100` | 220 252 231 | `#DCFCE7` | — |
| `success-200` | 187 247 208 | `#BBF7D0` | Light borders |
| `success-300` | 131 218 163 | `#83DAA3` | — |
| `success-400` | 74 222 128 | `#4ADE80` | — |
| `success-500` | 34 197 94 | `#22C55E` | **Success text, icons** |
| `success-600` | 22 163 74 | `#16A34A` | **Success buttons** |
| `success-700` | 21 128 61 | `#15803D` | Hover |
| `success-800` | 22 101 52 | `#166534` | — |
| `success-900` | 20 83 45 | `#14532D` | — |
| `success-950` | 5 46 22 | `#052E16` | Darkest |

#### Warning (Yellow/Amber)

| Token | RGB | Hex | Usage |
|---|---|---|---|
| `warning-50` | 254 252 232 | `#FEFCE8` | Warning backgrounds |
| `warning-100` | 254 249 195 | `#FEF9C3` | — |
| `warning-200` | 254 240 138 | `#FEF08A` | Light borders |
| `warning-300` | 253 224 71 | `#FDE047` | — |
| `warning-400` | 250 204 21 | `#FACC15` | — |
| `warning-500` | 234 179 8 | `#EAB308` | **Warning text, icons** |
| `warning-600` | 202 138 4 | `#CA8A04` | — |
| `warning-700` | 161 98 7 | `#A16207` | — |
| `warning-800` | 133 77 14 | `#854D0E` | — |
| `warning-900` | 113 63 18 | `#713F12` | — |
| `warning-950` | 66 32 6 | `#422006` | Darkest |

#### Gray (14 shades)

| Token | RGB | Hex | Usage |
|---|---|---|---|
| `gray-50` | 250 250 250 | `#FAFAFA` | Lightest background |
| `gray-100` | 244 244 245 | `#F4F4F5` | Section backgrounds |
| `gray-200` | 228 228 231 | `#E4E4E7` | Light borders |
| `gray-300` | 212 212 216 | `#D4D4D8` | Borders |
| `gray-400` | 161 161 170 | `#A1A1AA` | Disabled text |
| `gray-500` | 107 107 116 | `#6B6B74` | Placeholder text |
| `gray-600` | 82 82 91 | `#52525B` | Secondary text |
| `gray-700` | 63 63 70 | `#3F3F46` | Emphasis text |
| `gray-800` | 39 39 42 | `#27272A` | Strong text |
| `gray-850` | 29 29 33 | `#1D1D21` | — |
| `gray-900` | 24 24 27 | `#18181B` | Near-black |
| `gray-930` | 22 22 25 | `#161619` | — |
| `gray-950` | 9 9 11 | `#09090B` | Near-black |

### Semantic Token Prefixes

MyDS uses semantic prefixes to theme-aware tokens that change between light and dark modes:

| Prefix | Purpose | Example |
|---|---|---|
| `bg-` | Background | `--bg-white`, `--bg-washed`, `--bg-primary-600` |
| `txt-` | Text color | `--txt-black-900`, `--txt-primary`, `--txt-danger` |
| `otl-` | Outline / Border | `--otl-divider`, `--otl-gray-300`, `--otl-primary-300` |
| `fr-` | Focus Ring | `--fr-primary`, `--fr-danger` |

### Tailwind Configuration

Our default theme CSS at `resources/themes/default/css/theme.css` maps MyDS tokens to CSS custom properties:

```css
[data-theme="default"],
:root {
    /* Primary — MyDS primary-600 */
    --color-primary: #2563EB;
    --color-primary-dark: #1D4ED8;
    --color-primary-light: #3B82F6;

    /* Surfaces */
    --color-bg: #FFFFFF;
    --color-surface: #F4F4F5;
    --color-border: #D4D4D8;
    --color-text: #18181B;
    --color-muted: #6B6B74;
}
```

Tailwind v4.x CSS-first config in `resources/themes/default/css/app.css` uses `@theme inline` to generate utility classes from these variables.

---

## Typography

### Font Families

MyDS specifies three font families:

| Role | Font | Weights | Usage |
|---|---|---|---|
| **Headings** | Poppins | 400, 500, 600 | All `<h1>`–`<h6>` elements |
| **Body** | Inter | 400, 500, 600 | Body text, UI labels, inputs |
| **Monospace** | Roboto Mono | 400, 500 | Code blocks, technical data |

**Google Fonts import:**

```css
@import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=Roboto+Mono:wght@400;500&display=swap");
```

**Tailwind `@theme` config:**

```css
@theme {
    --font-heading: 'Poppins', ui-sans-serif, system-ui, sans-serif;
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'Roboto Mono', ui-monospace, monospace;
}
```

### Heading Scale (Poppins)

| Style | Size | Line Height | HTML | Tailwind |
|---|---|---|---|---|
| **Heading XL** | 60px (3.75rem) | 72px (4.5rem) | — | `text-heading-xl` |
| **Heading LG** | 48px (3rem) | 60px (3.75rem) | — | `text-heading-lg` |
| **Heading MD** | 36px (2.25rem) | 44px (2.75rem) | `<h1>` | `text-heading-md` |
| **Heading SM** | 30px (1.875rem) | 38px (2.375rem) | `<h2>` | `text-heading-sm` |
| **Heading XS** | 24px (1.5rem) | 32px (2rem) | `<h3>` | `text-heading-xs` |
| **Heading 2XS** | 20px (1.25rem) | 28px (1.75rem) | `<h4>` | `text-heading-2xs` |
| **Heading 3XS** | 16px (1rem) | 24px (1.5rem) | `<h5>` | — |
| **Heading 4XS** | 14px (0.875rem) | 20px (1.25rem) | `<h6>` | — |

### Body Scale (Inter)

| Style | Size | Line Height | Tailwind |
|---|---|---|---|
| **Body XL** | 20px (1.25rem) | 30px (1.875rem) | `text-body-xl` |
| **Body LG** | 18px (1.125rem) | 26px (1.625rem) | `text-body-lg` |
| **Body MD** | 16px (1rem) | 24px (1.5rem) | `text-body-md` |
| **Body SM** | 14px (0.875rem) | 20px (1.25rem) | `text-body-sm` |
| **Body XS** | 12px (0.75rem) | 18px (1.125rem) | `text-body-xs` |

**Body text spacing:**
- List spacing: 6px (0.375rem)
- Paragraph spacing: 12px (0.75rem)

### Rich Text Format Scale (Inter, for CMS content)

| Style | Size | Line Height | HTML |
|---|---|---|---|
| **RTF H1** | 30px (1.875rem) | 38px (2.375rem) | `<h1>` |
| **RTF H2** | 24px (1.5rem) | 32px (2rem) | `<h2>` |
| **RTF H3** | 20px (1.25rem) | 28px (1.75rem) | `<h3>` |
| **RTF H4** | 18px (1.125rem) | 26px (1.625rem) | `<h4>` |
| **RTF H5** | 16px (1rem) | 24px (1.5rem) | `<h5>` |
| **RTF H6** | 14px (0.875rem) | 20px (1.25rem) | `<h6>` |
| **RTF Paragraph** | 16px (1rem) | 28px (1.75rem) | `<p>` |

---

## Spacing System

Base unit: **4px**

| Value | Measurement | Use Case |
|---|---|---|
| **4px** | 0.25rem | Base unit, smallest increment |
| **8px** | 0.5rem | Gap in button groups, fields and labels |
| **12px** | 0.75rem | Minor spacing |
| **16px** | 1rem | Standard spacing |
| **20px** | 1.25rem | Medium spacing |
| **24px** | 1.5rem | Gap between sub-sections, cards |
| **32px** | 2rem | Gap between main sections |
| **40px** | 2.5rem | Large spacing |
| **48px** | 3rem | Extra-large spacing |
| **64px** | 4rem | Maximum spacing |

---

## Border Radius

| Name | Value | Tailwind | Use Case |
|---|---|---|---|
| **Extra Small** | 4px | `rounded-xs` | Context menu items |
| **Small** | 6px | `rounded-sm` | Small buttons |
| **Medium** | 8px | `rounded` / `rounded-md` | Buttons, CTA, context menus |
| **Large** | 12px | `rounded-lg` | Content cards |
| **Extra Large** | 14px | `rounded-xl` | Context menu with search field |
| **Full** | 9999px | `rounded-full` | Circular elements, pills |

---

## Shadows

| Name | CSS Value | Tailwind | Use Case |
|---|---|---|---|
| **Button** | `0px 1px 3px 0px rgba(0,0,0,0.07)` | `shadow-button` | Button elevation |
| **Card** | `0px 2px 6px 0px rgba(0,0,0,0.05), 0px 6px 24px 0px rgba(0,0,0,0.05)` | `shadow-card` | Card elevation |
| **Context Menu** | `0px 2px 6px 0px rgba(0,0,0,0.05), 0px 12px 50px 0px rgba(0,0,0,0.10)` | `shadow-context-menu` | Dropdowns, modals |

---

## Motion & Animation

### Easing Curves

| Token | CSS Value | Use |
|---|---|---|
| `instant` | none | No transition |
| `linear` | `cubic-bezier(0, 0, 1, 1)` | Charts, progress bars |
| `easeout` | `cubic-bezier(0, 0, 0.58, 1)` | State transitions, fade-out |
| `easeoutback` | `cubic-bezier(0.4, 1.4, 0.2, 1)` | Playful elements, button interactions |

### Duration Tokens

| Token | Duration | Application |
|---|---|---|
| `short` | 200ms | Buttons, dropdowns, micro-interactions |
| `medium` | 400ms | Callouts, alert dialogs, toasts |
| `long` | 600ms | Page/section transitions |

### Composite Tokens

| Composite | Duration | Easing |
|---|---|---|
| `easeoutback.short` | 200ms | `cubic-bezier(0.4, 1.4, 0.2, 1)` |
| `easeoutback.medium` | 400ms | `cubic-bezier(0.4, 1.4, 0.2, 1)` |
| `easeout.long` | 600ms | `cubic-bezier(0, 0, 0.58, 1)` |

### Keyframe Animations

| Animation | Duration | Easing | Use |
|---|---|---|---|
| `slide-up` | 300ms | ease-out | Dropdowns, toasts |
| `slide-down` | 300ms | ease-out | Accordion, collapsible |
| `accordion-slide-down` | 300ms | ease-out | Accordion open |
| `accordion-slide-up` | 300ms | ease-out | Accordion close |
| `shimmer` | 1200ms | linear infinite | Loading skeleton |
| `expire` | 5s | linear | Toast auto-dismiss |

---

## Icons

MyDS provides a comprehensive icon library (221 icons) at a base grid of **20x20** with **1.5px stroke width**.

### Available Sizes

| Size | Use Case |
|---|---|
| 16x16 | Inline text, compact UI |
| 20x20 | Default (base grid) |
| 24x24 | Buttons, navigation |
| 32x32 | Feature highlights |
| 42x42 | Hero sections |

### Icon Variants

- **Outline** — Default, 1.5px stroke
- **Filled** — Solid fill for emphasis or active states

### Icon Categories

| Category | Count | Examples |
|---|---|---|
| **Generic UI** | ~140 | `search`, `home`, `user`, `setting`, `bell`, `chevron-*`, `arrow-*`, `check`, `cross`, `edit`, `trash`, `download`, `upload`, `globe`, `calendar`, `lock`, `eye-show`/`eye-hide`, `filter`, `hamburger-menu` |
| **Social Media** | 10 | `facebook`, `instagram`, `twitter-x`, `youtube`, `linkedin`, `telegram`, `tiktok`, `whatsapp`, `github`, `google` |
| **File Types** | 7 | `pdf`, `excel`, `word`, `powerpoint-media`, `pdf-media`, `excel-media`, `word-media` |
| **Geographic** | 3 | `malaysia-flag`, `govt-office`, `putrajaya` |

### Implementation

Since we use Blade, icons are implemented as inline SVGs or via a Blade component:

```blade
{{-- Inline SVG approach --}}
<svg class="h-5 w-5 text-current" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 20 20">
  <!-- path data -->
</svg>
```

---

## Components

### MyDS Component Inventory

MyDS defines 32 design components and provides 40 React components + 16 CSS-only (Transistory Assistance) components. Below is the full inventory mapped to our Blade + Alpine.js implementation.

#### Core UI Components

| MyDS Component | Our Implementation | Status | Notes |
|---|---|---|---|
| **Accordion** | Blade + Alpine.js `x-data` | Planned | TA (CSS-only) available |
| **Alert Dialog** | Blade + Alpine.js modal | Planned | Confirmation dialogs |
| **Announce Bar** | Blade component | Planned | Top-of-page announcements |
| **Breadcrumb** | Blade component | Planned | Navigation trail |
| **Button** | Blade component | Planned | TA (CSS-only) available |
| **Callout** | Blade component | Planned | TA (CSS-only) available |
| **Card** | Blade component | Implemented | TA (CSS-only) available |
| **Carousel** | Alpine.js + Embla.js | Implemented | Hero banner carousel |
| **Checkbox** | Blade + Livewire | Planned | Form input |
| **Cookies Banner** | Blade + Alpine.js | Planned | GDPR/cookie consent |
| **Data Table** | Livewire component | Planned | Sortable, filterable |
| **Date Field** | Blade input | Planned | Text-based date entry |
| **Date Picker** | Alpine.js | Planned | Calendar popup |
| **Dialog / Modal** | Blade + Alpine.js | Planned | TA (CSS-only) available |
| **Dropdown** | Alpine.js `x-data` | Planned | Context menus, selects |
| **Footer** | Blade component | Implemented | Site footer |
| **Input** | Blade component | Planned | TA (CSS-only) available |
| **Input OTP** | Blade + Alpine.js | Planned | One-time password |
| **Label** | Blade component | Planned | TA (CSS-only) available |
| **Link** | Blade component | Planned | TA (CSS-only) available |
| **Masthead** | Blade component | Planned | Government masthead bar |
| **Navbar** | Blade + Alpine.js | Implemented | Main navigation |
| **Pagination** | Blade component | Planned | TA (CSS-only) available |
| **Pill** | Blade component | Planned | Status indicators |
| **Radio** | Blade + Livewire | Planned | Form input |
| **Search Bar** | Livewire component | Planned | Site search |
| **Select** | Blade component | Planned | TA (CSS-only) available |
| **Skip Link** | Blade component | Planned | Accessibility |
| **Spinner** | Blade component | Planned | TA (CSS-only) available |
| **Summary List** | Blade component | Planned | Key-value display |
| **Switch / Toggle** | Blade + Alpine.js | Planned | TA (CSS-only) available |
| **Table** | Blade component | Planned | Data display |
| **Tabs** | Blade + Alpine.js | Planned | Tab navigation |
| **Tag** | Blade component | Implemented | TA (CSS-only) available |
| **Text Area** | Blade component | Planned | Multiline input |
| **Theme Switch** | Blade + Alpine.js | Implemented | TA (CSS-only) available |
| **Toast** | Alpine.js | Planned | Notifications |
| **Tooltip** | Alpine.js | Planned | Hover information |

#### Design-Only Components (no React implementation)

These components exist in the MyDS design guidelines but not yet in the component library:

| Component | Description | Our Approach |
|---|---|---|
| **Backlink** | "Back to previous page" link | Blade component |
| **Character Count** | Text input character counter | Alpine.js |
| **Details** | Expandable content section | Alpine.js |
| **File Upload** | File upload with drag & drop | Livewire |
| **Inset Text** | Highlighted sidebar text | Blade component |
| **Panel** | Content panel with header | Blade component |
| **Password Input** | Password with show/hide toggle | Alpine.js |
| **Phase Banner** | Alpha/Beta phase indicator | Blade component |
| **Task List** | Multi-step task tracker | Blade component |

### Button Variants (MyDS Spec)

**Variants:**

| Variant | Use Case |
|---|---|
| `primary-fill` | Primary actions (CTA, submit) |
| `primary-outline` | Secondary primary actions |
| `primary-ghost` | Tertiary primary actions |
| `default-outline` | Default/neutral actions |
| `default-ghost` | Subtle neutral actions |
| `danger-fill` | Destructive actions (delete) |
| `danger-outline` | Secondary destructive |
| `danger-ghost` | Tertiary destructive |

**Sizes:**

| Size | Padding | Font Size |
|---|---|---|
| `small` | py-1.5 px-3 | text-body-sm (14px) |
| `medium` | py-2 px-4 | text-body-md (16px) |
| `large` | py-2.5 px-5 | text-body-lg (18px) |

**Sub-components:** `ButtonIcon` (icon wrapper), `ButtonCounter` (badge count)

**Blade implementation:**

```blade
{{-- Primary fill --}}
<button class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-md shadow-button transition-all duration-200">
    Button Text
</button>

{{-- Primary outline --}}
<button class="border border-primary text-primary hover:bg-primary-50 font-medium py-2 px-4 rounded-md transition-all duration-200">
    Button Text
</button>

{{-- Danger fill --}}
<button class="bg-danger-600 hover:bg-danger-700 text-white font-medium py-2 px-4 rounded-md shadow-button transition-all duration-200">
    Delete
</button>
```

---

## Page Templates

MyDS provides 6 standard page templates:

| Template | MyDS Path | Our Route | Status |
|---|---|---|---|
| **Landing Page** | `/en/docs/develop/landing-page` | `/{locale}` | Implemented |
| **Standard Directory** | `/en/docs/develop/standard-page` | `/{locale}/direktori` | Planned |
| **Contact Us** | `/en/docs/develop/contact-us` | `/{locale}/hubungi-kami` | Planned |
| **Login** | `/en/docs/develop/login` | `/admin/login` | Implemented (Filament) |
| **Registration** | `/en/docs/develop/registration` | — | Not needed |
| **Forgot Password** | `/en/docs/develop/forgot-password` | `/admin/password-reset` | Implemented (Filament) |

---

## Page Layouts

### 1. Homepage (Landing Page)

```
+-------------------------------------------+
|           MASTHEAD (govt bar)             |
+-------------------------------------------+
|              HEADER                       |
|  Logo | Nav Links | Language | Search     |
+-------------------------------------------+
|                                           |
|            HERO SECTION                   |
|  Headline + Subheadline + CTA Button     |
|  [Background Image / Carousel]           |
|                                           |
+-------------------------------------------+
|                                           |
|       QUICK LINKS / SERVICES              |
|  [Icon] [Icon] [Icon] [Icon]             |
|                                           |
+-------------------------------------------+
|                                           |
|       LATEST BROADCASTS (Siaran)          |
|  [Card] [Card] [Card]                    |
|  View All link                            |
|                                           |
+-------------------------------------------+
|                                           |
|       ACHIEVEMENTS (Pencapaian)           |
|  [Timeline Card] [Timeline Card]         |
|  View All link                            |
|                                           |
+-------------------------------------------+
|              FOOTER                       |
|  Links | Social | Copyright              |
+-------------------------------------------+
```

### 2. Content Listing Page (Siaran, Pencapaian)

```
+-------------------------------------------+
|  MASTHEAD + HEADER                        |
+-------------------------------------------+
|                                           |
|  Breadcrumb: Home > Section               |
|                                           |
|  [Page Title]                             |
|                                           |
|  Search: [___________] [Filter v]         |
|                                           |
|  +------+ +------+ +------+              |
|  |Card 1| |Card 2| |Card 3|              |
|  +------+ +------+ +------+              |
|  +------+ +------+ +------+              |
|  |Card 4| |Card 5| |Card 6|              |
|  +------+ +------+ +------+              |
|                                           |
|  [ < Prev ] Page 1 of 10 [ Next > ]      |
|                                           |
+-------------------------------------------+
|              FOOTER                       |
+-------------------------------------------+
```

### 3. Content Detail Page

```
+-------------------------------------------+
|  MASTHEAD + HEADER                        |
+-------------------------------------------+
|                                           |
|  Breadcrumb: Home > Section > Title       |
|                                           |
|  [Type Badge]                             |
|                                           |
|  # Article Title                          |
|                                           |
|  By Department | 20 Feb 2026              |
|                                           |
|  [Featured Image]                         |
|                                           |
|  Article content (RTF typography)...      |
|                                           |
|  [Share Buttons]                          |
|                                           |
|  --- Related Articles ---                 |
|  [Card] [Card] [Card]                    |
|                                           |
+-------------------------------------------+
|              FOOTER                       |
+-------------------------------------------+
```

---

## Responsive Breakpoints

| Breakpoint | Width | Tailwind Prefix | Grid Columns |
|---|---|---|---|
| **Mobile** | < 640px | Default | 4 (gap: 18px) |
| **Tablet SM** | 640px+ | `sm:` | 4 |
| **Tablet** | 768px+ | `md:` | 8 (gap: 24px) |
| **Desktop** | 1024px+ | `lg:` | 12 (gap: 24px) |
| **Wide** | 1280px+ | `xl:` | 12 |

**Max content width:** 1280px (`max-w-7xl`)

---

## Accessibility Requirements

### WCAG 2.2 AA Compliance

1. **Color Contrast**
   - Normal text: minimum **4.5:1** ratio
   - Large text (18pt+ / 14pt+ bold): minimum **3:1** ratio
   - UI components and graphical objects: minimum **3:1** ratio

2. **Touch Targets**
   - Minimum: **24x24** CSS pixels
   - Recommended: **44x44** CSS pixels

3. **Keyboard Navigation**
   - All interactive elements focusable via Tab
   - Visible focus indicators (using `--fr-primary` ring)
   - Logical tab order matching visual layout

4. **Screen Readers**
   - Semantic HTML structure (`<header>`, `<nav>`, `<main>`, `<footer>`)
   - ARIA labels on interactive elements without visible text
   - Alt text for all meaningful images
   - Live regions for dynamic content updates

5. **Text Sizing**
   - Support 200% zoom without content loss
   - Reflow at 400% zoom without horizontal scrolling
   - Relative units (rem, em) throughout

6. **Motion**
   - Respect `prefers-reduced-motion` media query
   - Provide pause/stop for auto-playing content (carousel)

---

## Theme System

OpenGovPortal uses a WordPress-like theme system where each theme is a self-contained folder with its own views, CSS, JS, and assets.

### Theme Directory Structure

```
resources/themes/
  default/                              <-- Ships with the app
    theme.json                          <-- Manifest
    css/
      app.css                           <-- Tailwind entry point
      theme.css                         <-- Design tokens
    js/
      app.js                            <-- JS entry (Alpine + Embla)
    views/
      components/
        layouts/
          app.blade.php                 <-- Main layout
          guest.blade.php               <-- Minimal layout
        layout/
          nav.blade.php                 <-- Navigation
          footer.blade.php              <-- Footer
          theme-switcher.blade.php      <-- Theme switcher
        home/
          hero-banner.blade.php         <-- Hero carousel
          quick-links.blade.php         <-- Quick links grid
          broadcast-card.blade.php      <-- Broadcast card
          achievement-card.blade.php    <-- Achievement card
      home/
        index.blade.php                 <-- Homepage
      preview/
        show.blade.php                  <-- Content preview
      errors/                           <-- Error pages
    assets/                             <-- Theme-specific images, fonts
```

### theme.json Manifest

```json
{
    "name": "default",
    "label": { "ms": "Tema Lalai", "en": "Default Theme" },
    "version": "1.0.0",
    "author": "GovPortal",
    "css": "css/app.css",
    "js": "js/app.js"
}
```

### View Resolution Chain

When a request comes in with an active theme:

```
view('home.index')
  1. resources/themes/{active-theme}/views/home/index.blade.php   <-- first match wins
  2. resources/themes/default/views/home/index.blade.php          <-- fallback
```

### Key Files

| File | Role |
|---|---|
| `app/Services/ThemeService.php` | Discovery, active management, Vite entries |
| `app/Providers/ThemeServiceProvider.php` | Scoped singleton, default view path |
| `app/Http/Middleware/ApplyTheme.php` | Per-request view path reset (Octane-safe) |
| `config/themes.php` | Base path, fallback, cache TTL |
| `vite.config.js` | Auto-discovers theme entry points |

### Creating a New Theme

1. Copy `resources/themes/default/` to `resources/themes/my-theme/`
2. Edit `theme.json` — change `name`, update labels
3. Delete views you don't want to override (they fall back to `default`)
4. Customize CSS (tokens in `theme.css`, Tailwind config in `app.css`)
5. Run `npm run build`
6. Theme is auto-discovered — activate via admin Settings > Theme

### Available Themes

| Key | Name | Status |
|---|---|---|
| `default` | MyDS Blue (Light) | Implemented |

---

## Implementation Checklist

### Foundations
- [x] Import Poppins + Inter + Roboto Mono from Google Fonts
- [x] Configure color tokens in theme CSS
- [x] Configure Tailwind v4.x `@theme` in `app.css`
- [x] Set up theme system with `ThemeService`
- [x] Create and register `ApplyTheme` middleware
- [x] Apply `data-theme` attribute to `<html>` in layout
- [ ] Add MyDS shadow tokens (`shadow-button`, `shadow-card`, `shadow-context-menu`)
- [ ] Add MyDS motion tokens (easing curves, durations)
- [ ] Add MyDS typography tokens (heading + body font sizes)
- [ ] Add MyDS radius tokens (`rounded-xs` through `rounded-xl`)
- [ ] Add MyDS grid system (12-8-4 responsive)

### Components (Blade + Alpine.js)
- [x] Hero banner carousel (Embla.js)
- [x] Card component (broadcast, achievement)
- [x] Navigation (header + mobile menu)
- [x] Footer
- [x] Theme switcher
- [x] Quick links grid
- [ ] Masthead (government bar)
- [ ] Breadcrumb
- [ ] Button variants (all 8 MyDS variants)
- [ ] Form inputs (text, select, textarea, checkbox, radio)
- [ ] Accordion
- [ ] Alert / Callout
- [ ] Dialog / Modal
- [ ] Pagination
- [ ] Search bar
- [ ] Tabs
- [ ] Tag / Pill
- [ ] Toast notifications
- [ ] Tooltip
- [ ] Skip link
- [ ] Cookies banner
- [ ] Spinner / Loading state
- [ ] Summary list
- [ ] Data table

### Pages
- [x] Homepage layout
- [ ] Content listing page (Siaran, Pencapaian)
- [ ] Content detail page
- [ ] Search results page
- [ ] Contact form page
- [ ] Directory page
- [ ] Policy page
- [ ] Ministry profile page
- [ ] Static pages (Penafian, Dasar Privasi)

### Testing
- [ ] Cross-browser testing (Chrome, Firefox, Safari)
- [ ] Mobile responsiveness testing
- [ ] Accessibility audit (WCAG 2.2 AA)
- [ ] Performance testing (Lighthouse 90+)
- [ ] Reduced motion testing

---

## Resources

- **GitHub:** [github.com/govtechmy/myds](https://github.com/govtechmy/myds)
- **Design Guidelines:** [design.digital.gov.my/en/docs/design](https://design.digital.gov.my/en/docs/design)
- **Component Library:** [design.digital.gov.my/en/docs/develop](https://design.digital.gov.my/en/docs/develop)
- **CSS-Only Components:** [design.digital.gov.my/en/docs/develop](https://design.digital.gov.my/en/docs/develop) (Transistory Assistance section)
- **Figma:** [MyDS Beta](https://www.figma.com/design/BwyAzgDaGno8QhaoTtLBMx/MYDS--Beta-?node-id=7-20696&t=xrPkDdDl0w2DqlwX-1)
- **NPM (style only):** `@govtechmy/myds-style` — CSS tokens and Tailwind preset

---

*This design documentation ensures OpenGovPortal adheres to the Malaysian Government Design System (MyDS) for consistency across all government digital services.*
