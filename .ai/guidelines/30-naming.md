# Naming Rules (Non-Negotiable)

## Models

Follow the Payload collection → Laravel model mapping exactly:

| Payload / Source                              | Laravel          |
|-----------------------------------------------|------------------|
| Broadcast                                     | `Broadcast`      |
| Achievement                                   | `Achievement`    |
| Celebration                                   | `Celebration`    |
| Directory                                     | `StaffDirectory` |
| Feedback                                      | `Feedback`       |
| File                                          | `PolicyFile`     |
| HeroBanner                                    | `HeroBanner`     |
| Media                                         | `Media`          |
| Policy                                        | `Policy`         |
| QuickLink                                     | `QuickLink`      |
| Search-Overrides                              | `SearchOverride` |
| New — CMS static pages                        | `StaticPage`     |
| New — hierarchical page categories            | `PageCategory`   |
| New — menu registry                           | `Menu`           |
| New — 4-level menu items with role visibility | `MenuItem`       |

## Controllers

Match the route-to-controller table in [docs/agentic-coding.md](docs/agentic-coding.md) exactly. Do not rename.

## Route Files

Add new routes to the correct file — never mix concerns:

| File                | For                                         |
|---------------------|---------------------------------------------|
| `routes/public.php` | All `/{locale}/...` public pages            |
| `routes/admin.php`  | Custom admin endpoints beyond Filament      |
| `routes/api.php`    | REST API under `/api/v1/`                   |
| `routes/web.php`    | Root redirect only — do not add routes here |

Full rules: [docs/agentic-coding.md → Route Files](docs/agentic-coding.md).

## Blade Views

Match the view directory structure in [docs/agentic-coding.md](docs/agentic-coding.md) exactly.

## Cache Tags

Use only the tag names defined in [docs/pages-features.md](docs/pages-features.md) under "Cache Tag → Route / Model
Mapping". Do not invent new tags.
