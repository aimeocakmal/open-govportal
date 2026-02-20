# 🎨 Design Documentation

## Overview

OpenGovPortal follows the **Malaysian Government Design System (MYDS)** to ensure consistency, accessibility, and trust across all government digital services.

**Design System:** MYDS (Malaysian Government Design System)  
**Figma:** [MYDS Beta](https://www.figma.com/design/BwyAzgDaGno8QhaoTtLBMx/MYDS--Beta-?node-id=7-20696&t=xrPkDdDl0w2DqlwX-1)  
**Documentation:** [design.digital.gov.my](https://design.digital.gov.my/en)  
**Status:** Beta (Production-ready April 2025)

---

## Design Principles

### 1. Beautiful Government Sites
Clean and minimalist design ensuring information is easy to find and services are easy to use.

### 2. Rapid Development
Pre-built visual and functional elements for efficient prototyping and development.

### 3. Cost Savings
Accelerated design and development, saving valuable taxpayer ringgits.

### 4. Compliant with Standards
Full adherence to Jabatan Digital Negara's design principles and service architecture.

### 5. Accessible by Design
Out-of-the-box adherence to WCAG 2.1 AA and best accessibility practices.

### 6. Trusted by Citizens
Familiar look and feel, increasing citizens' trust in the government's digital presence.

---

## Color Palette

### Primary Colors

| Color | Hex | RGB | Usage |
|-------|-----|-----|-------|
| **Primary** | `#2563EB` | rgb(37, 99, 235) | Buttons, links, key actions |
| **Primary Dark** | `#1D4ED8` | rgb(29, 78, 216) | Hover states |
| **Primary Light** | `#3B82F6` | rgb(59, 130, 246) | Active states |

### Secondary Colors

| Color | Hex | RGB | Usage |
|-------|-----|-----|-------|
| **Secondary** | `#64748B` | rgb(100, 116, 139) | Secondary buttons |
| **Secondary Dark** | `#475569` | rgb(71, 85, 105) | Secondary hover |
| **Secondary Light** | `#94A3B8` | rgb(148, 163, 184) | Secondary active |

### Neutral Colors (Grayscale)

| Color | Hex | RGB | Usage |
|-------|-----|-----|-------|
| **Black** | `#0F172A` | rgb(15, 23, 42) | Headings, body text |
| **Gray 800** | `#1E293B` | rgb(30, 41, 59) | Strong text |
| **Gray 700** | `#334155` | rgb(51, 65, 85) | Emphasis |
| **Gray 600** | `#475569` | rgb(71, 85, 105) | Secondary text |
| **Gray 500** | `#64748B` | rgb(100, 116, 139) | Placeholders |
| **Gray 400** | `#94A3B8` | rgb(148, 163, 184) | Disabled |
| **Gray 300** | `#CBD5E1` | rgb(203, 213, 225) | Borders |
| **Gray 200** | `#E2E8F0` | rgb(226, 232, 240) | Light backgrounds |
| **Gray 100** | `#F1F5F9` | rgb(241, 245, 249) | Section backgrounds |
| **White** | `#FFFFFF` | rgb(255, 255, 255) | Card backgrounds |

### Semantic Colors

| Color | Hex | Usage |
|-------|-----|-------|
| **Success** | `#10B981` | Success messages, confirmations |
| **Success Light** | `#D1FAE5` | Success backgrounds |
| **Warning** | `#F59E0B` | Warnings, alerts |
| **Warning Light** | `#FEF3C7` | Warning backgrounds |
| **Error** | `#EF4444` | Errors, destructive actions |
| **Error Light** | `#FEE2E2` | Error backgrounds |
| **Info** | `#3B82F6` | Information, notices |
| **Info Light** | `#DBEAFE` | Info backgrounds |

### Tailwind Config

Tailwind CSS v4.x uses **CSS-first configuration** — no `tailwind.config.js`. All theme tokens are declared in your CSS file using the `@theme` directive.

```css
/* resources/css/app.css */
@import "tailwindcss";

@theme {
  /* MyDS colour palette */
  --color-primary: #2563EB;
  --color-primary-dark: #1D4ED8;
  --color-primary-light: #3B82F6;

  --color-secondary: #64748B;
  --color-secondary-dark: #475569;
  --color-secondary-light: #94A3B8;

  --color-success: #10B981;
  --color-success-light: #D1FAE5;

  --color-warning: #F59E0B;
  --color-warning-light: #FEF3C7;

  --color-error: #EF4444;
  --color-error-light: #FEE2E2;

  --color-info: #3B82F6;
  --color-info-light: #DBEAFE;
}
```

Utility classes are generated automatically from `@theme` tokens (e.g. `bg-primary`, `text-primary-dark`, `border-error`).

---

## Typography

### Font Family

**Primary Font:** Inter  
**Fallback:** system-ui, -apple-system, sans-serif

```css
/* resources/css/app.css */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
```

### Type Scale

| Level | Size | Line Height | Weight | Usage |
|-------|------|-------------|--------|-------|
| **Display** | 48px (3rem) | 1.1 | 700 | Hero headlines |
| **H1** | 36px (2.25rem) | 1.2 | 700 | Page titles |
| **H2** | 30px (1.875rem) | 1.3 | 600 | Section headings |
| **H3** | 24px (1.5rem) | 1.4 | 600 | Sub-sections |
| **H4** | 20px (1.25rem) | 1.4 | 600 | Card titles |
| **H5** | 18px (1.125rem) | 1.5 | 600 | Sub-headings |
| **H6** | 16px (1rem) | 1.5 | 600 | Small headings |
| **Body Large** | 18px (1.125rem) | 1.6 | 400 | Lead paragraphs |
| **Body** | 16px (1rem) | 1.6 | 400 | Default text |
| **Body Small** | 14px (0.875rem) | 1.5 | 400 | Captions, metadata |
| **Caption** | 12px (0.75rem) | 1.5 | 400 | Labels, timestamps |

### Typography CSS

```css
/* resources/css/typography.css */
.text-display {
  font-size: 3rem;
  line-height: 1.1;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.text-h1 {
  font-size: 2.25rem;
  line-height: 1.2;
  font-weight: 700;
}

.text-h2 {
  font-size: 1.875rem;
  line-height: 1.3;
  font-weight: 600;
}

.text-h3 {
  font-size: 1.5rem;
  line-height: 1.4;
  font-weight: 600;
}

.text-body-large {
  font-size: 1.125rem;
  line-height: 1.6;
}

.text-body {
  font-size: 1rem;
  line-height: 1.6;
}

.text-body-small {
  font-size: 0.875rem;
  line-height: 1.5;
}
```

---

## Spacing System

### Base Unit: 4px

| Token | Value | Pixels | Usage |
|-------|-------|--------|-------|
| **space-1** | 0.25rem | 4px | Tight spacing |
| **space-2** | 0.5rem | 8px | Small gaps |
| **space-3** | 0.75rem | 12px | Default padding |
| **space-4** | 1rem | 16px | Component padding |
| **space-5** | 1.25rem | 20px | Card padding |
| **space-6** | 1.5rem | 24px | Section gaps |
| **space-8** | 2rem | 32px | Large gaps |
| **space-10** | 2.5rem | 40px | Section padding |
| **space-12** | 3rem | 48px | Major sections |
| **space-16** | 4rem | 64px | Page sections |
| **space-20** | 5rem | 80px | Hero spacing |

### Section Spacing

- **Small Section:** 48px (3rem) vertical padding
- **Medium Section:** 80px (5rem) vertical padding
- **Large Section:** 120px (7.5rem) vertical padding

---

## Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| **rounded-none** | 0px | Sharp corners |
| **rounded-sm** | 2px | Subtle rounding |
| **rounded** | 4px | Default (inputs) |
| **rounded-md** | 6px | Cards, buttons |
| **rounded-lg** | 8px | Large cards |
| **rounded-xl** | 12px | Modals, panels |
| **rounded-2xl** | 16px | Featured sections |
| **rounded-full** | 9999px | Pills, avatars |

---

## Shadows

| Token | Value | Usage |
|-------|-------|-------|
| **shadow-sm** | 0 1px 2px rgba(0,0,0,0.05) | Subtle elevation |
| **shadow** | 0 1px 3px rgba(0,0,0,0.1) | Default cards |
| **shadow-md** | 0 4px 6px rgba(0,0,0,0.1) | Elevated cards |
| **shadow-lg** | 0 10px 15px rgba(0,0,0,0.1) | Modals, dropdowns |
| **shadow-xl** | 0 20px 25px rgba(0,0,0,0.15) | Overlays |

---

## Components

### 1. Buttons

#### Primary Button
```html
<button class="bg-primary hover:bg-primary-dark text-white font-medium py-2.5 px-5 rounded-md transition-colors">
  Button Text
</button>
```

#### Secondary Button
```html
<button class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-md transition-colors">
  Button Text
</button>
```

#### Button Sizes
- **Small:** py-2 px-4 text-sm
- **Medium:** py-2.5 px-5 text-base (default)
- **Large:** py-3 px-6 text-lg

### 2. Cards

```html
<div class="bg-white rounded-lg shadow-md overflow-hidden">
  <img src="image.jpg" class="w-full h-48 object-cover" alt="">
  <div class="p-5">
    <span class="text-sm text-primary font-medium">Category</span>
    <h3 class="text-h4 mt-2">Card Title</h3>
    <p class="text-gray-600 mt-2">Card description text goes here...</p>
  </div>
</div>
```

### 3. Forms

#### Input Field
```html
<div class="space-y-2">
  <label class="block text-sm font-medium text-gray-700">Label</label>
  <input type="text" 
         class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary transition-shadow"
         placeholder="Placeholder text">
  <p class="text-sm text-gray-500">Helper text</p>
</div>
```

#### Select Dropdown
```html
<div class="space-y-2">
  <label class="block text-sm font-medium text-gray-700">Select Option</label>
  <select class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary">
    <option>Option 1</option>
    <option>Option 2</option>
  </select>
</div>
```

### 4. Navigation

#### Main Navigation
```html
<nav class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <!-- Logo -->
      <div class="flex items-center">
        <img src="logo.svg" alt="Logo" class="h-8">
      </div>
      
      <!-- Nav Links -->
      <div class="hidden md:flex items-center space-x-8">
        <a href="#" class="text-gray-600 hover:text-primary font-medium">Home</a>
        <a href="#" class="text-gray-600 hover:text-primary font-medium">Services</a>
        <a href="#" class="text-gray-600 hover:text-primary font-medium">Announcements</a>
        <a href="#" class="text-gray-600 hover:text-primary font-medium">Contact</a>
      </div>
      
      <!-- Language Switcher -->
      <div class="flex items-center">
        <select class="border-none text-sm font-medium text-gray-600 focus:ring-0">
          <option value="ms">BM</option>
          <option value="en">EN</option>
        </select>
      </div>
    </div>
  </div>
</nav>
```

### 5. Alert/Banner

```html
<!-- Info Banner -->
<div class="bg-info-light border-l-4 border-info p-4">
  <div class="flex">
    <div class="flex-shrink-0">
      <svg class="h-5 w-5 text-info" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div class="ml-3">
      <p class="text-sm text-info-dark">Information message goes here.</p>
    </div>
  </div>
</div>
```

---

## Page Layouts

### 1. Homepage

```
┌─────────────────────────────────────────┐
│              HEADER                     │
│  Logo | Nav Links | Language | Search   │
├─────────────────────────────────────────┤
│                                         │
│              HERO SECTION               │
│  Headline + Subheadline + CTA Button   │
│  [Background Image or Gradient]        │
│                                         │
├─────────────────────────────────────────┤
│                                         │
│         QUICK LINKS / SERVICES          │
│  [Icon] [Icon] [Icon] [Icon]           │
│  Service 1  Service 2  Service 3  ...  │
│                                         │
├─────────────────────────────────────────┤
│                                         │
│         LATEST ANNOUNCEMENTS            │
│  [Card] [Card] [Card]                  │
│  View All Button                        │
│                                         │
├─────────────────────────────────────────┤
│                                         │
│           FEATURED CONTENT              │
│  [Large Image] + [Text Content]        │
│                                         │
├─────────────────────────────────────────┤
│                                         │
│              NEWSLETTER                 │
│  Subscribe form                        │
│                                         │
├─────────────────────────────────────────┤
│              FOOTER                     │
│  Links | Social | Copyright            │
└─────────────────────────────────────────┘
```

### 2. Content Listing Page

```
┌─────────────────────────────────────────┐
│              HEADER                     │
├─────────────────────────────────────────┤
│                                         │
│  Breadcrumb: Home > Announcements      │
│                                         │
│  [Page Title: Announcements]           │
│                                         │
│  Search: [____________] [Filter ▼]     │
│                                         │
│  ┌──────┐ ┌──────┐ ┌──────┐           │
│  │Card 1│ │Card 2│ │Card 3│           │
│  └──────┘ └──────┘ └──────┘           │
│  ┌──────┐ ┌──────┐ ┌──────┐           │
│  │Card 4│ │Card 5│ │Card 6│           │
│  └──────┘ └──────┘ └──────┘           │
│                                         │
│  [ < Prev ] Page 1 of 10 [ Next > ]    │
│                                         │
├─────────────────────────────────────────┤
│              FOOTER                     │
└─────────────────────────────────────────┘
```

### 3. Content Detail Page

```
┌─────────────────────────────────────────┐
│              HEADER                     │
├─────────────────────────────────────────┤
│                                         │
│  Breadcrumb: Home > Category > Title   │
│                                         │
│  [Category Badge]                       │
│                                         │
│  # Article Title                        │
│                                         │
│  By Department Name | 20 Feb 2026      │
│                                         │
│  [Featured Image]                       │
│                                         │
│  Article content goes here...          │
│  Multiple paragraphs...                │
│                                         │
│  [Share Buttons]                        │
│                                         │
│  ---                                    │
│  Related Articles:                     │
│  [Card] [Card] [Card]                  │
│                                         │
├─────────────────────────────────────────┤
│              FOOTER                     │
└─────────────────────────────────────────┘
```

---

## Responsive Breakpoints

| Breakpoint | Width | Tailwind Prefix | Usage |
|------------|-------|-----------------|-------|
| **Mobile** | < 640px | Default | Single column |
| **Tablet** | 640px+ | sm: | 2 columns |
| **Desktop** | 768px+ | md: | 3 columns |
| **Large** | 1024px+ | lg: | Full layout |
| **Extra Large** | 1280px+ | xl: | Wide screens |

### Container Widths

```css
.container {
  max-width: 100%;
  padding-left: 1rem;
  padding-right: 1rem;
}

@media (min-width: 640px) {
  .container { max-width: 640px; }
}

@media (min-width: 768px) {
  .container { max-width: 768px; }
}

@media (min-width: 1024px) {
  .container { max-width: 1024px; }
}

@media (min-width: 1280px) {
  .container { max-width: 1280px; }
}
```

---

## Accessibility Requirements

### WCAG 2.1 AA Compliance

1. **Color Contrast**
   - Text on background: minimum 4.5:1
   - Large text (18pt+): minimum 3:1
   - All MyDS colors meet this standard

2. **Keyboard Navigation**
   - All interactive elements focusable
   - Visible focus indicators
   - Logical tab order

3. **Screen Readers**
   - Semantic HTML structure
   - ARIA labels where needed
   - Alt text for images

4. **Text Sizing**
   - Support 200% zoom without horizontal scroll
   - Relative units (rem, em)

---

## Theme System

OpenGovPortal supports multiple visual themes selectable by the end user. The default theme implements the full MyDS colour palette (blue, light mode).

### Architecture

Themes are implemented using CSS custom properties and the HTML `data-theme` attribute:

1. `resources/css/themes/default.css` — declares the default design tokens under `[data-theme="default"], :root`
2. Each additional theme overrides the same CSS variables under its own `[data-theme="name"]` selector
3. `resources/css/app.css` imports all theme files; Tailwind `@theme` block generates utility classes from the same variable names
4. `<html data-theme="{{ $currentTheme }}">` is set server-side by `ApplyTheme` middleware (reads `govportal_theme` cookie)
5. Alpine.js `<x-theme-switcher>` component handles client-side switching (updates `data-theme` attribute + writes cookie)

Tailwind utility classes (`bg-primary`, `text-primary-dark`, `border-error`, etc.) automatically reflect theme overrides because Tailwind v4 generates them as `var(--color-primary)` references.

### File Structure

```
resources/css/
  app.css                 ← @import "tailwindcss"; @import each theme file; global utilities
  themes/
    default.css           ← MyDS Blue, light — [data-theme="default"], :root
    dark.css              ← Dark mode overrides — [data-theme="dark"]  (Phase 4)
```

### Default Theme (`themes/default.css`)

```css
/* resources/css/themes/default.css */
[data-theme="default"],
:root {
  /* Primary */
  --color-primary:       #2563EB;
  --color-primary-dark:  #1D4ED8;
  --color-primary-light: #3B82F6;

  /* Secondary */
  --color-secondary:       #64748B;
  --color-secondary-dark:  #475569;
  --color-secondary-light: #94A3B8;

  /* Semantic */
  --color-success:       #10B981;
  --color-success-light: #D1FAE5;
  --color-warning:       #F59E0B;
  --color-warning-light: #FEF3C7;
  --color-error:         #EF4444;
  --color-error-light:   #FEE2E2;
  --color-info:          #3B82F6;
  --color-info-light:    #DBEAFE;

  /* Surfaces */
  --color-bg:      #FFFFFF;
  --color-surface: #F1F5F9;
  --color-border:  #CBD5E1;
  --color-text:    #0F172A;
  --color-muted:   #64748B;
}
```

### Theme Persistence

| Layer | Mechanism |
|-------|-----------|
| User preference | Cookie `govportal_theme` (1-year, path=`/`) |
| Site default | `settings` key `site_default_theme` (editable in Filament `ManageSiteInfo`) |
| Server-side render | `ApplyTheme` middleware reads cookie → shares `$currentTheme` view variable |
| HTML attribute | `<html data-theme="{{ $currentTheme }}">` in base layout |
| Client-side switch | `<x-theme-switcher>` Alpine.js component updates attribute + writes cookie |

### `ApplyTheme` Middleware

```php
// app/Http/Middleware/ApplyTheme.php
public function handle(Request $request, Closure $next): Response
{
    $valid  = config('themes.valid_themes', ['default']);
    $cookie = $request->cookie('govportal_theme');
    $default = Setting::get('site_default_theme', 'default');
    $theme  = ($cookie && in_array($cookie, $valid)) ? $cookie : $default;

    view()->share('currentTheme', $theme);

    return $next($request);
}
```

Register on the `web` middleware group in `bootstrap/app.php`.

### `config/themes.php`

```php
return [
    'valid_themes' => ['default'],  // extend as new themes are added
];
```

### Alpine.js Theme Switcher Component

```html
{{-- resources/views/components/layout/theme-switcher.blade.php --}}
<div x-data="{
    theme: document.documentElement.dataset.theme || 'default',
    set(name) {
        this.theme = name;
        document.documentElement.dataset.theme = name;
        document.cookie = 'govportal_theme=' + name + ';path=/;max-age=31536000';
    }
}" class="flex items-center gap-2">
    @foreach(config('themes.valid_themes') as $key)
        <button
            @click="set('{{ $key }}')"
            :class="{ 'ring-2 ring-primary': theme === '{{ $key }}' }"
            class="px-3 py-1 rounded-md text-sm font-medium border border-border hover:bg-surface transition-colors"
        >
            {{ ucfirst($key) }}
        </button>
    @endforeach
</div>
```

### How to Add a New Theme

1. Create `resources/css/themes/{name}.css` and override the relevant variables under `[data-theme="{name}"]`.
2. Add `@import "./themes/{name}.css";` to `resources/css/app.css`.
3. Add the theme key to `config/themes.php` `valid_themes` array.
4. Update `ManageSiteInfo` Filament settings form to include the new option.

### Available Themes

| Key | Name | Status |
|-----|------|--------|
| `default` | MyDS Blue (Light) | Planned — Week 2 |
| `dark` | Dark Mode | Planned — Phase 4 |

---

## Implementation Checklist

### Setup
- [ ] Import Inter font from Google Fonts
- [ ] Create `resources/css/themes/default.css` with full MyDS token set under `[data-theme="default"], :root`
- [ ] Configure Tailwind v4.x `@theme` block in `resources/css/app.css` to reference the same CSS variable names
- [ ] Create `config/themes.php` with `valid_themes` array
- [ ] Create and register `ApplyTheme` middleware on `web` group
- [ ] Apply `data-theme="{{ $currentTheme }}"` to `<html>` in base layout
- [ ] Set up base typography styles
- [ ] Create component library

### Components
- [ ] Button variants (Primary, Secondary, Ghost)
- [ ] Card component
- [ ] Form inputs (Text, Select, Textarea)
- [ ] Navigation component
- [ ] Alert/Banner component
- [ ] Modal/Dialog component
- [ ] Pagination component

### Pages
- [ ] Homepage layout
- [ ] Content listing page
- [ ] Content detail page
- [ ] Search results page
- [ ] Contact form page

### Testing
- [ ] Cross-browser testing (Chrome, Firefox, Safari)
- [ ] Mobile responsiveness testing
- [ ] Accessibility audit (WCAG 2.1 AA)
- [ ] Performance testing (Lighthouse 90+)

---

## Resources

- **Figma:** [MYDS Beta](https://www.figma.com/design/BwyAzgDaGno8QhaoTtLBMx/MYDS--Beta-?node-id=7-20696&t=xrPkDdDl0w2DqlwX-1)
- **Documentation:** [design.digital.gov.my](https://design.digital.gov.my/en)
- **Component Library:** React version (Dec 2024)
- **Full Launch:** April 2025

---

*This design documentation ensures OpenGovPortal adheres to the Malaysian Government Design System (MYDS) for consistency across all government digital services.*
