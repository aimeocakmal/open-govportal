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

```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#2563EB',
          dark: '#1D4ED8',
          light: '#3B82F6',
        },
        secondary: {
          DEFAULT: '#64748B',
          dark: '#475569',
          light: '#94A3B8',
        },
        success: {
          DEFAULT: '#10B981',
          light: '#D1FAE5',
        },
        warning: {
          DEFAULT: '#F59E0B',
          light: '#FEF3C7',
        },
        error: {
          DEFAULT: '#EF4444',
          light: '#FEE2E2',
        },
      },
    },
  },
}
```

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

## Implementation Checklist

### Setup
- [ ] Import Inter font from Google Fonts
- [ ] Configure Tailwind with MYDS colors
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
