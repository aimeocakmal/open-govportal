import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';
import path from 'path';

/**
 * Scan resources/themes for theme.json manifests and return
 * the CSS + JS entry points for each discovered theme.
 */
function discoverThemeEntryPoints() {
    const themesDir = path.resolve(__dirname, 'resources/themes');
    const entries = [];

    if (!fs.existsSync(themesDir)) {
        return entries;
    }

    for (const dir of fs.readdirSync(themesDir, { withFileTypes: true })) {
        if (!dir.isDirectory()) continue;

        const manifestPath = path.join(themesDir, dir.name, 'theme.json');
        if (!fs.existsSync(manifestPath)) continue;

        const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf-8'));
        const css = manifest.css || 'css/app.css';
        const js = manifest.js || 'js/app.js';

        entries.push(`resources/themes/${dir.name}/${css}`);
        entries.push(`resources/themes/${dir.name}/${js}`);
    }

    return entries;
}

export default defineConfig({
    plugins: [
        laravel({
            input: [...discoverThemeEntryPoints()],
            refresh: ['resources/themes/**/views/**'],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
