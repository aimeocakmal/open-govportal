# Sample Image Pack

Every image slot on the public site, what the views do with it, the exact size to supply, and the committed asset pack
that fills the slots today. Photographs are supplied by the project owner against the request list at the end; vector
assets (icons, logo, favicon, placeholders) are already in the repository.

Pair this document with [sample-content.md](sample-content.md), which lists the rows that reference these files.

---

## Conventions

- **Location.** Everything lives under `public/images/` (and `public/files/` for PDFs) and is referenced by a
  root-relative path such as `/images/hero/hero-01.svg`. No storage symlink is involved and nothing is served from
  `storage/`.
- **How images are stored.** Hero, broadcast, achievement, staff, head-of-organisation and quick-link images are plain
  URL text fields in the admin. The views print the value straight into `src`, so the path must be root-relative
  (leading slash) or absolute. A bare `banners/photo.jpg` resolves relative to the current page and breaks.
- **Formats.** WebP or JPEG for photographs (WebP preferred, JPEG fallback if a tool cannot write WebP). SVG for icons,
  logo and placeholders. ICO for the favicon.
- **Weight.** Hero photographs at most 400 KB, broadcast images at most 250 KB, portraits at most 80 KB. Views already
  add `loading="lazy"` on everything except the first hero slide.
- **Alt text.** Every slot has a bilingual alt source: `image_alt_ms` / `image_alt_en` on hero banners, `alt_ms` /
  `alt_en` in the media registry, and the row title elsewhere. Describe what is in the picture, not the page it sits on.
- **Composition for the hero.** The view lays a black gradient over the left 60 percent and prints white text inside a
  672 px column on the left. Keep the subject on the right third and the left side calm and low-detail so the text stays
  at 4.5:1 contrast.
- **Cards crop.** Broadcast cards force 16:9 with `object-cover` and scale to 105 percent on hover; keep important
  content away from the edges and never bake text into the image.
- **People.** Staff and head-of-organisation slots are optional. Real portraits of real people should not stand in for
  fictional staff; the silhouette fallback is acceptable. If portraits are used, they must be licensed for this purpose.

---

## Slot Specification

Derived from the Tailwind classes in `resources/themes/default/views/`.

| Slot                            | Field                                                                                   | Rendered box                                                                       | Ratio   | Supply         | Format    | Filled today by                                          |
|---------------------------------|-----------------------------------------------------------------------------------------|------------------------------------------------------------------------------------|---------|----------------|-----------|----------------------------------------------------------|
| Hero slide (×4)                 | `hero_banners.image`                                                                    | full-bleed, 400 / 480 / 560 px tall, `object-cover`, dark gradient on the left     | ~2.14:1 | 2400×1120      | WebP/JPEG | `hero-01.svg` … `hero-04.svg` placeholders               |
| Broadcast image (×6, cycled)    | `broadcasts.featured_image`                                                             | card `aspect-[16/9]` ≈ 400 px wide; detail page ≤ 864 px; also `og:image`          | 16:9    | 1600×900       | WebP/JPEG | `broadcast-01.svg` … `broadcast-06.svg` placeholders     |
| Achievement icon                | `achievements.icon`                                                                     | 40 px on cards, 96 px on detail, `object-contain`                                  | 1:1     | 192 viewBox 48 | SVG       | 8 icons in `icons/achievements/`                         |
| Quick-link icon                 | `quick_links.icon` (≤ 100 chars)                                                        | 32 px inside a 56 px tile, `object-contain`                                        | 1:1     | 96 viewBox 24  | SVG       | 6 icons in `icons/quick-links/`                          |
| Head of organisation (optional) | `minister_profiles.photo`                                                               | 288 px wide on desktop stretching to card height; 256 px tall full-width on mobile | 3:4     | 864×1152       | WebP/JPEG | `null` (photo column hidden)                             |
| Staff portrait (optional ×14)   | `staff_directories.photo`                                                               | 80 px circle, `object-cover`                                                       | 1:1     | 240×240        | WebP/JPEG | `null` (silhouette fallback)                             |
| Footer logo                     | `footer_settings.url` where `type = logo`                                               | `h-10`, width auto, on a gray-50 background                                        | 5:1     | 200×40 viewBox | SVG       | `logo/opengovportal-footer.svg`                          |
| Favicon                         | `public/favicon.ico` (auto-requested by browsers; no `<link rel="icon">` in the layout) | 16 / 32 / 48                                                                       | 1:1     | multi-size ICO | ICO       | generated from the mark                                  |
| Chatbot avatar                  | `ai_chatbot_avatar` setting (upload field)                                              | 32 px circle                                                                       | 1:1     | 96×96          | SVG/PNG   | `chatbot/avatar.svg` in the pack; upload via AI Settings |
| Site logo (admin)               | `site_logo` setting (upload field)                                                      | not rendered by any public view yet                                                | —       | 96×96          | SVG       | `logo/opengovportal-mark.svg`; upload via Site Info      |
| Policy document                 | `policies.file_url`                                                                     | download button; the download route redirects to the file                          | —       | A4 PDF         | PDF       | 10 sample PDFs in `public/files/policies/`               |

---

## Committed Asset Pack

```
public/
  favicon.ico                       multi-size ICO (16, 32, 48) built from the mark
  favicon.svg                       copy of the mark for future <link rel="icon"> use
  images/
    hero/hero-01.svg … hero-04.svg              2400×1120 placeholders, blue gradient, one white arch motif on the right
    broadcasts/broadcast-01.svg … broadcast-06.svg   1600×900 placeholders, light blue gradient, one blue arch motif
    icons/quick-links/{siaran,pencapaian,statistik,dasar,direktori,hubungi-kami}.svg
    icons/achievements/{trophy,users,globe,shield,chart,document,rocket,star}.svg
    logo/opengovportal-mark.svg     96×96 rounded square with a white arch (the brand mark)
    logo/opengovportal-footer.svg   200×40 mark plus "OpenGovPortal" wordmark
    chatbot/avatar.svg              copy of the mark
  files/policies/<slug>.pdf         10 one-page A4 sample documents, one per published policy
```

### Design notes

- **One motif.** A rounded-top doorway, the "portal arch", is the only decorative element. It appears as the brand mark,
  at low opacity on the placeholders, and nowhere else. Everything around it stays quiet.
- **Colours.** Only the MyDS primary scale from `resources/themes/default/css/theme.css`: `#1E3A8A`, `#1D4ED8`,
  `#2563EB`, `#3A75F6`, `#96B7FF`, `#C2D5FF`, `#EFF6FF`. Hero placeholders run dark-to-light left to right so the
  left-hand text keeps contrast even before the gradient overlay. Broadcast placeholders are light so cards do not
  overpower the page.
- **Icons.** Outline paths from the Heroicons set (MIT licence, shipped with Filament under
  `vendor/blade-ui-kit/blade-heroicons`), stroke `#2563EB`, 1.5 stroke width, round caps. Achievement icons sit on a
  `#EFF6FF` circle so they match the check-circle fallback the card uses when `icon` is empty.
- **Logo.** The wordmark is set in Poppins 600 at 18 units so it fits the 200-unit viewBox with fallback fonts. Text
  colour is gray-900 for the light footer; a dark-background variant is not needed yet because no view places the logo
  on a dark surface.
- **Placeholders carry a `<title>`** so they are described to assistive technology even before real alt text is
  supplied.

---

## Photo Request List

Supply these files and drop them into the paths shown. Then change the extension in the matching seeder row
(`HeroBannerSeeder`, and the `$images` list in `BroadcastSeeder`) from `.svg` to `.jpg` or `.webp`, run
`php artisan migrate:fresh --seed` and `php artisan cache:clear`. The alt text below is what will be stored.

### Hero (required for the carousel to look finished)

| File                                               | Size      | Subject brief                                                                                                              | Composition                                                                       | Alt (ms / en)                                                                                                                |
|----------------------------------------------------|-----------|----------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------|
| `public/images/hero/hero-01.jpg`                   | 2400×1120 | A bright, modern public-service space or open-plan office with a few people at work. Conveys "welcome".                    | Subject on the right third; plain wall, sky or soft bokeh on the left 40 percent. | Ruang pejabat terbuka yang terang dengan beberapa orang sedang bekerja / A bright open-plan office with a few people at work |
| `public/images/hero/hero-02.jpg`                   | 2400×1120 | Printed documents or folders beside a laptop on a desk, shallow depth of field. Conveys "policies and guidelines".         | Desk items on the right; empty desk surface on the left.                          | Dokumen bercetak di sebelah komputer riba di atas meja / Printed documents beside a laptop on a desk                         |
| `public/images/hero/hero-03.jpg`                   | 2400×1120 | A person using a chat interface on a phone or laptop, or an abstract light-trail or network image. Conveys "AI assistant". | Device or light pattern on the right; dark, calm left side.                       | Seseorang menggunakan antara muka sembang pada telefon / A person using a chat interface on a phone                          |
| `public/images/hero/hero-04.jpg` (inactive banner) | 2400×1120 | A small training room with people at laptops facing a screen. Conveys "training".                                          | Group on the right; blank wall or screen on the left.                             | Bilik latihan kecil dengan peserta di hadapan komputer riba / A small training room with participants at laptops             |

### Broadcast images (six themes, cycled across the 19 broadcasts)

| File                                        | Size     | Theme                                                                                       | Used by                                                                    | Alt (ms / en)                                                                                            |
|---------------------------------------------|----------|---------------------------------------------------------------------------------------------|----------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------|
| `public/images/broadcasts/broadcast-01.jpg` | 1600×900 | Releases and code: keyboard, screen with code, or a terminal.                               | 0.7.0 release, AI editor launch, documentation refresh, 2027 roadmap draft | Papan kekunci dan skrin memaparkan kod / Keyboard and a screen showing code                              |
| `public/images/broadcasts/broadcast-02.jpg` | 1600×900 | Infrastructure: server rack, network cables, or a data-centre corridor.                     | Maintenance notice, bilingual search, 0.6.0 release                        | Rak pelayan dengan kabel rangkaian / Server rack with network cables                                     |
| `public/images/broadcasts/broadcast-03.jpg` | 1600×900 | AI and privacy: abstract light pattern, or hands on a laptop with a chat window.            | AI beta, privacy policy update, interactive charts                         | Corak cahaya abstrak berwarna biru / Abstract blue light pattern                                         |
| `public/images/broadcasts/broadcast-04.jpg` | 1600×900 | Accessibility and design: a braille display, screen-reader user, or a designer at a tablet. | WCAG audit, theme system, directory live search                            | Seseorang menggunakan paparan braille dengan komputer / A person using a braille display with a computer |
| `public/images/broadcasts/broadcast-05.jpg` | 1600×900 | Writing and editorial: a person writing at a desk with a notebook and laptop.               | How-to guide, community meetup, bilingual writing tips                     | Seseorang menulis nota di sebelah komputer riba / A person writing notes beside a laptop                 |
| `public/images/broadcasts/broadcast-06.jpg` | 1600×900 | Community and training: a meeting room or meetup with a small group.                        | Training sessions, feedback rate limit, source code opened                 | Kumpulan kecil berbincang di dalam bilik mesyuarat / A small group in discussion in a meeting room       |

### Optional portraits

| File                                                | Size     | Notes                                                                                                                               |
|-----------------------------------------------------|----------|-------------------------------------------------------------------------------------------------------------------------------------|
| `public/images/profile/minister.jpg`                | 864×1152 | Head of organisation. Head in the upper-middle; mobile crops to a 256 px tall band. Set `photo` in `MinisterProfileSeeder`.         |
| `public/images/staff/staff-01.jpg` … `staff-14.jpg` | 240×240  | One per active staff member in sort order. Face centred; the view crops to a circle. Set `photo` per row in `StaffDirectorySeeder`. |

If no portraits are supplied, leave `photo` as `null`: the profile card hides the photo column and staff cards show the
silhouette.

---

## Licensing And Attribution

- Photographs must be owned by the project or licensed for redistribution in a public repository. Record the source,
  licence and attribution line for each file in the table below when it is added.
- Icons are derived from Heroicons (MIT). The licence text ships with `vendor/blade-ui-kit/blade-heroicons`.
- Placeholders, the logo, the favicon and the sample PDFs were produced for this project and carry no third-party
  rights.

| File                                                                                                 | Source               | Licence         | Attribution   |
|------------------------------------------------------------------------------------------------------|----------------------|-----------------|---------------|
| `images/icons/**`                                                                                    | Heroicons v2 outline | MIT             | Tailwind Labs |
| `images/hero/*.svg`, `images/broadcasts/*.svg`, `images/logo/*`, `favicon.*`, `files/policies/*.pdf` | This project         | Project licence | —             |
| `images/hero/*.jpg`                                                                                  | pending              | pending         | pending       |
| `images/broadcasts/*.jpg`                                                                            | pending              | pending         | pending       |

---

## Known Limitations

- **Header logo.** The header prints the app name as text; no view reads `site_logo`. Uploading a logo in Site Info only
  affects the chatbot avatar fallback.
- **Favicon.** The layout has no `<link rel="icon">`; browsers fall back to requesting `/favicon.ico`, which now exists.
  `site_favicon` in Site Info is stored but not emitted.
- **Open Graph.** Only the broadcast detail page sets `og:image`, and it uses the stored root-relative path. Scrapers
  need an absolute URL. Other pages have no `og:image`.
- **Upload fields.** `site_logo`, `site_logo_dark`, `site_favicon` and `ai_chatbot_avatar` are Filament upload fields on
  the default disk. They cannot be seeded with a `/images/...` path because the next admin save validates the file
  against the disk and drops it.
- **No image processing.** Uploads are stored at their original dimensions; there is no resize or crop step. Supply
  files at the sizes above.
- **Sample PDF sizes.** The ten policy PDFs are one page each (under 1 KB). The policy card shows `file_size` rounded to
  one decimal in MB, so the seeder leaves the size empty for files under 50 KB. Replace them with real documents at
  `public/files/policies/<slug>.pdf` and re-seed to show sizes.

---

## Related Docs

- [sample-content.md](sample-content.md) — the rows that reference these files
- [design.md](design.md) — MyDS tokens the placeholders and icons follow
- [pages-features.md](pages-features.md) — which page section renders each slot
