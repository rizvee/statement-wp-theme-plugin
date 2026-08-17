import fs from 'node:fs';
import path from 'node:path';

const DUNE_DIR = path.resolve('.local-assets', 'client-drive', 'dune-jacket');
const HOOD_DIR = path.resolve('.local-assets', 'client-drive', 'white-hoodie');
const DEMO_IMG_DIR = path.resolve('tools', 'statement-client-demo', 'assets', 'images');
const THEME_IMG_DIR = path.resolve('wp-content', 'themes', 'statement-collector-theme', 'assets', 'images');

fs.mkdirSync(DEMO_IMG_DIR, { recursive: true });
fs.mkdirSync(THEME_IMG_DIR, { recursive: true });

const ASSET_MAP = [
  // Monogram Jacquard (Dune Jacket Series)
  {
    src: path.join(DUNE_DIR, 'pomelli_photoshoot_image_4_5_0412.png'),
    destNames: ['statement-monogram-jacket-front.jpg', 'statement-monogram-jacquard-jacket-front.jpg']
  },
  {
    src: path.join(DUNE_DIR, 'pomelli_photoshoot_image_4_5_0412 (4).png'),
    destNames: ['statement-monogram-jacket-back.jpg', 'statement-monogram-jacquard-jacket-back.jpg']
  },
  {
    src: path.join(DUNE_DIR, 'pomelli_photoshoot_image_4_5_0412 (2).png'),
    destNames: ['statement-monogram-jacket-side.jpg', 'statement-monogram-jacquard-jacket-side.jpg']
  },
  {
    src: path.join(DUNE_DIR, 'pomelli_photoshoot_image_4_5_0412 (1).png'),
    destNames: ['statement-monogram-jacket-collar-detail.jpg', 'statement-monogram-jacquard-jacket-detail.jpg']
  },
  {
    src: path.join(DUNE_DIR, 'pomelli_photoshoot_image_4_5_0412 (5).png'),
    destNames: ['statement-monogram-jacket-flatlay-concrete.jpg', 'statement-monogram-jacquard-jacket-editorial-01.jpg']
  },
  {
    src: path.join(DUNE_DIR, '2.png'),
    destNames: ['statement-monogram-jacket-flatlay-slate.jpg', 'statement-monogram-jacquard-jacket-editorial-02.jpg']
  },
  {
    src: path.join(DUNE_DIR, 'pomelli_photoshoot_image_9_16_0412 (1).png'),
    destNames: ['statement-hero-slide-monogram-01.jpg']
  },
  {
    src: path.join(DUNE_DIR, 'pomelli_photoshoot_image_9_16_0412 (2).png'),
    destNames: ['statement-hero-slide-monogram-02.jpg']
  },

  // Panelled Hood Jacket (White Hoodie Series)
  {
    src: path.join(HOOD_DIR, 'freepik_add-these-items-to-the-mo_2874783971.jpg'),
    destNames: ['statement-panelled-hood-jacket-front.jpg']
  },
  {
    src: path.join(HOOD_DIR, 'freepik_change-the-pose-so-the-mo_2874810909.jpg'),
    destNames: ['statement-panelled-hood-jacket-side.jpg']
  },
  {
    src: path.join(HOOD_DIR, 'freepik_generate-a-cinematic-stor_2880512847.png'),
    destNames: ['statement-panelled-hood-jacket-back.jpg']
  },
  {
    src: path.join(HOOD_DIR, 'freepik_generate-a-cinematic-stor_2880512825.png'),
    destNames: ['statement-panelled-hood-jacket-embroidery-detail.jpg', 'statement-panelled-hood-jacket-detail.jpg']
  },
  {
    src: path.join(HOOD_DIR, '2_freepik_generate-a-cinematic-stor_2880512885.png'),
    destNames: ['statement-panelled-hood-jacket-cathedral-front.jpg', 'statement-panelled-hood-jacket-editorial-01.jpg']
  },
  {
    src: path.join(HOOD_DIR, '1_a-cinematic-stor_2880512940.png'),
    destNames: ['statement-panelled-hood-jacket-night-34.jpg', 'statement-panelled-hood-jacket-editorial-02.jpg']
  },
  {
    src: path.join(HOOD_DIR, 'freepik_generate-a-cinematic-stor_2880512871.png'),
    destNames: ['statement-hero-slide-hood-01.jpg']
  },
  {
    src: path.join(HOOD_DIR, 'freepik_generate-a-cinematic-stor_2880512968.png'),
    destNames: ['statement-hero-slide-hood-02.jpg']
  }
];

function run() {
  console.log('Optimizing & syncing client Drive assets into theme and demo tool...');
  for (const item of ASSET_MAP) {
    if (!fs.existsSync(item.src)) {
      console.warn(`[WARN] Source file not found: ${item.src}`);
      continue;
    }
    const data = fs.readFileSync(item.src);
    for (const destName of item.destNames) {
      const demoTarget = path.join(DEMO_IMG_DIR, destName);
      const themeTarget = path.join(THEME_IMG_DIR, destName);
      fs.writeFileSync(demoTarget, data);
      fs.writeFileSync(themeTarget, data);
      console.log(`[SYNC] ${path.basename(item.src)} -> ${destName} (${data.length} bytes)`);
    }
  }
  console.log('Asset synchronization complete.');
}

run();
