/**
 * Generate Skolaria PWA icons, iOS splash screens, and social preview.
 * Cross-platform — works on Windows, macOS, Linux.
 *
 * Usage:
 *   node scripts/generate-assets.mjs
 *
 * Output naming:
 *   public/icons/icon-{W}x{H}.png            (standard PWA icons)
 *   public/icons/icon-maskable-{W}x{H}.png   (maskable PWA icons)
 *   public/splash/splash-{W}x{H}.png         (iOS splash screens)
 *   public/images/skolaria-og.png            (social preview)
 */

import { mkdir, access } from "node:fs/promises";
import { constants } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import sharp from "sharp";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");

const paths = {
    iconsSrc: path.join(root, "public/images/icons/skolaria.svg"),
    maskableSrc: path.join(root, "public/images/icons/skolaria-maskable.svg"),
    splashSrc: path.join(root, "public/images/icons/skolaria-splash.svg"),
    ogSrc: path.join(root, "public/images/icons/skolaria-og.svg"),
    iconsOut: path.join(root, "public/images/icons"),
    splashOut: path.join(root, "public/images/splash"),
    imagesOut: path.join(root, "public/images"),
};

const standardSizes = [
    16, 20, 29, 30, 32, 36, 40, 44, 48, 50, 55, 57, 58, 60, 62, 64, 66, 70, 71,
    72, 76, 80, 87, 88, 92, 96, 100, 114, 120, 128, 144, 150, 152, 167, 170,
    172, 180, 192, 196, 216, 256, 300, 310, 358, 360, 384, 512, 558, 1024,
];

const maskableSizes = [192, 512];

const splashSizes = [
    [640, 1136],
    [750, 1334],
    [828, 1792],
    [1125, 2436],
    [1136, 640],
    [1170, 2532],
    [1179, 2556],
    [1242, 2208],
    [1242, 2688],
    [1284, 2778],
    [1290, 2796],
    [1536, 2048],
    [1334, 750],
    [1488, 2266],
    [1620, 2160],
    [1640, 2360],
    [1668, 2224],
    [1668, 2388],
    [1792, 828],
    [2048, 2732],
    [2160, 1620],
    [2208, 1242],
    [2224, 1668],
    [2266, 1488],
    [2360, 1640],
    [2388, 1668],
    [2436, 1125],
    [2532, 1170],
    [2256, 1179],
    [2688, 1242],
    [2732, 2048],
    [2778, 1284],
    [2796, 1290],
];

async function ensureDir(dir) {
    await mkdir(dir, { recursive: true });
}

async function fileExists(file) {
    try {
        await access(file, constants.R_OK);
        return true;
    } catch {
        return false;
    }
}

async function render(source, destination, width, height) {
    if (!(await fileExists(source))) {
        console.warn(`  ⚠ source missing: ${source}`);
        return;
    }

    await sharp(source, { density: 384 })
        .resize(width, height, { fit: "fill" })
        .png({ compressionLevel: 9, palette: false })
        .toFile(destination);

    console.log(`  ✓ ${path.relative(root, destination)}`);
}

async function generateIcons() {
    console.log("\nStandard PWA icons");
    for (const size of standardSizes) {
        await render(
            paths.iconsSrc,
            path.join(paths.iconsOut, `icon-${size}x${size}.png`),
            size,
            size,
        );
    }
}

async function generateMaskable() {
    console.log("\nMaskable icons");
    for (const size of maskableSizes) {
        await render(
            paths.maskableSrc,
            path.join(paths.iconsOut, `icon-maskable-${size}x${size}.png`),
            size,
            size,
        );
    }
}

async function generateSplash() {
    console.log("\nSplash screens");
    for (const [w, h] of splashSizes) {
        await render(
            paths.splashSrc,
            path.join(paths.splashOut, `splash-${w}x${h}.png`),
            w,
            h,
        );
    }
}

async function generateSocial() {
    console.log("\nSocial preview");
    await render(
        paths.ogSrc,
        path.join(paths.imagesOut, "skolaria-og.png"),
        1200,
        630,
    );
}

async function main() {
    console.log("Skolaria asset generator");
    console.log("========================");

    await ensureDir(paths.iconsOut);
    await ensureDir(paths.splashOut);
    await ensureDir(paths.imagesOut);

    await generateIcons();
    await generateMaskable();
    await generateSplash();
    await generateSocial();

    console.log("\n✅ All assets generated.\n");
    console.log("Next steps:");
    console.log("  1. Review icons at  https://maskable.app");
    console.log("  2. Preview social card at  https://www.opengraph.xyz");
    console.log("  3. Run Lighthouse PWA audit in Chrome DevTools\n");
}

main().catch((err) => {
    console.error("\n❌ Generation failed:", err.message);
    process.exit(1);
});
