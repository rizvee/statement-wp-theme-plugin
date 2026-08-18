import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const defaultVersion = '0.2.5';

export const approvedDemoFiles = [
  'statement-client-demo.php',
  'src/AdminPage.php',
  'src/AssetRegistry.php',
  'src/OwnershipClassifier.php',
  'src/DemoSeederService.php',
  'src/ManifestService.php',
  'assets/images/statement-monogram-jacket-front.jpg',
  'assets/images/statement-monogram-jacket-back.jpg',
  'assets/images/statement-monogram-jacket-side.jpg',
  'assets/images/statement-monogram-jacket-flatlay-concrete.jpg',
  'assets/images/statement-monogram-jacket-collar-detail.jpg',
  'assets/images/statement-monogram-jacket-flatlay-slate.jpg',
  'assets/images/statement-panelled-hood-jacket-front.jpg',
  'assets/images/statement-panelled-hood-jacket-side.jpg',
  'assets/images/statement-panelled-hood-jacket-back.jpg',
  'assets/images/statement-panelled-hood-jacket-cathedral-front.jpg',
  'assets/images/statement-panelled-hood-jacket-embroidery-detail.jpg',
  'assets/images/statement-panelled-hood-jacket-night-34.jpg',
  'assets/images/statement-brand-leather-patch.jpg',
  'assets/images/statement-brand-leather-badge.jpg',
  'assets/images/statement-brand-insignia-vector.jpg',
  'assets/images/statement-brand-insignia-gold.jpg',
  'assets/images/statement-brand-wordmark.jpg',
  'assets/images/statement-collector-dust-bag.jpg',
  'assets/images/statement-collector-patch-palm.jpg',
  'assets/images/statement-crafted-not-mass-made-poster.jpg',
  'assets/images/statement-hero-slide-hood-01.jpg',
  'assets/images/statement-hero-slide-hood-02.jpg',
  'assets/images/statement-hero-slide-monogram-01.jpg',
  'assets/images/statement-hero-slide-monogram-02.jpg',
];

export function packageClientDemo(version = defaultVersion) {
  const sourceRoot = join(root, 'tools', 'statement-client-demo');
  const mainPhpFile = join(sourceRoot, 'statement-client-demo.php');
  if (!existsSync(mainPhpFile)) {
    throw new Error(`Missing demo main PHP file: ${mainPhpFile}`);
  }
  const mainContent = readFileSync(mainPhpFile, 'utf8');
  const headerMatch = mainContent.match(/^[ \t\/*#]*Version:\s*(.+)$/m);
  if (!headerMatch || headerMatch[1].trim() !== version) {
    throw new Error(`Client demo header Version mismatch. Found "${headerMatch ? headerMatch[1].trim() : 'NONE'}", expected "${version}".`);
  }

  const distDir = join(root, 'dist');
  const uniqueId = `pkg-client-demo-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
  const stagingParent = join(root, 'tmp', uniqueId);
  const stagingDir = join(stagingParent, 'statement-client-demo');
  const zipName = `statement-client-demo-${version}.zip`;
  const zipPath = join(distDir, zipName);

  if (existsSync(stagingParent)) rmSync(stagingParent, { recursive: true, force: true, maxRetries: 3 });
  mkdirSync(stagingDir, { recursive: true });
  if (!existsSync(distDir)) mkdirSync(distDir, { recursive: true });

  let fileCount = 0;
  let phpCount = 0;

  for (const relFile of approvedDemoFiles) {
    const srcFile = join(sourceRoot, relFile);
    const destFile = join(stagingDir, relFile);

    if (!existsSync(srcFile)) {
      throw new Error(`Missing approved demo runtime file: ${relFile}`);
    }

    mkdirSync(dirname(destFile), { recursive: true });
    writeFileSync(destFile, readFileSync(srcFile));

    fileCount++;
    if (relFile.endsWith('.php')) phpCount++;
  }

  if (existsSync(zipPath)) rmSync(zipPath, { force: true, maxRetries: 3 });

  const tarCmd = `tar -caf "${zipPath}" -C "${stagingParent}" "statement-client-demo"`;
  execSync(tarCmd, { cwd: root, stdio: 'pipe' });

  if (existsSync(stagingParent)) rmSync(stagingParent, { recursive: true, force: true, maxRetries: 3 });

  const zipBytes = readFileSync(zipPath);
  const sha256 = createHash('sha256').update(zipBytes).digest('hex');
  const sizeBytes = zipBytes.length;

  return {
    name: zipName,
    path: zipPath,
    version,
    fileCount,
    phpCount,
    sizeBytes,
    sha256,
    rootFolder: 'statement-client-demo',
  };
}

if (process.argv[1] && process.argv[1].endsWith('package-client-demo.mjs')) {
  const result = packageClientDemo();
  console.log(`Packaged Client Demo: ${result.name} (${result.sizeBytes} bytes, SHA-256: ${result.sha256})`);
}
