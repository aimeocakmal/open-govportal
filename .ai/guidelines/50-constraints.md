# What Not To Do

- Do not add features not present in kd-portal (exception: approved AI features — see Resolved Decisions)
- Do not use Inertia.js, React, or Vue
- Do not use Alpine.js for anything that involves server state — use Livewire instead
- Do not use `fetch()` / `axios` directly in Alpine.js for data fetching — use Livewire wire calls
- Do not leave `// TODO` comments in committed code
- Do not reference docs that don't exist in this repo
- Do not mark a task done without running the validation commands
- Do not combine multiple models or routes in a single task — work in atomic slices
- Do not write Filament resource boilerplate (migrations, models, resource pages) manually from scratch — define in `draft.yaml` and run `php artisan blueprint:build` first, then customise
