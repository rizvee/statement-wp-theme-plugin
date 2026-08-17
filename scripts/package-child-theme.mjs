import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const defaultVersion = '0.1.0';

export const approvedChildThemeFiles = [
  'style.css',
  'functions.php',
  'README.md',
];

export function packageChildTheme(version = defaultVersion) {
  const sourceRoot = join(root, 'tools', 'statement-collector-child');
  const styleCssFile = join(sourceRoot, 'style.css');
  if (!existsSync(styleCssFile)) {
    throw new Error(`Missing child theme style.css file: ${styleCssFile}`);
  }
  const styleContent = readFileSync(styleCssFile, 'utf8');
  const headerMatch = styleContent.match(/^[ \t\/*#]*Version:\s*(.+)$/m);
  if (!headerMatch || headerMatch[1].trim() !== version) {
    throw new Error(`Child theme header Version mismatch in source style.css. Found "${headerMatch ? headerMatch[1].trim() : 'NONE'}", expected "${version}".`);
  }

  const distDir = join(root, 'dist');
  const uniqueId = `pkg-child-theme-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
  const stagingParent = join(root, 'tmp', uniqueId);
  const stagingDir = join(stagingParent, 'statement-collector-child');
  const zipName = `statement-collector-child-${version}.zip`;
  const zipPath = join(distDir, zipName);

  if (existsSync(stagingParent)) {
    rmSync(stagingParent, { recursive: true, force: true, maxRetries: 3 });
  }
  mkdirSync(stagingDir, { recursive: true });
  if (!existsSync(distDir)) {
    mkdirSync(distDir, { recursive: true });
  }

  let fileCount = 0;

  for (const relativePath of approvedChildThemeFiles) {
    const srcFile = join(sourceRoot, relativePath);
    const destFile = join(stagingDir, relativePath);

    if (!existsSync(srcFile)) {
      throw new Error(`Required approved child theme file missing from source: ${relativePath}`);
    }

    mkdirSync(dirname(destFile), { recursive: true });
    writeFileSync(destFile, readFileSync(srcFile));
    fileCount++;
  }

  if (existsSync(zipPath)) {
    rmSync(zipPath, { force: true });
  }

  const escapeForCmd = (str) => str.replace(/`/g, '``').replace(/\$/g, '`$').replace(/"/g, '`"');
  const psCommand = `Compress-Archive -Path "${escapeForCmd(stagingDir)}" -DestinationPath "${escapeForCmd(zipPath)}" -Force`;
  execSync(`powershell.exe -NoProfile -NonInteractive -Command "${psCommand}"`, {
    cwd: root,
    stdio: 'pipe',
  });

  if (existsSync(stagingParent)) {
    rmSync(stagingParent, { recursive: true, force: true, maxRetries: 3 });
  }

  if (!existsSync(zipPath)) {
    throw new Error(`Zip artifact creation failed for child theme: ${zipPath}`);
  }

  const zipBytes = readFileSync(zipPath);
  const sha256 = createHash('sha256').update(zipBytes).digest('hex');

  return {
    path: zipPath,
    name: zipName,
    rootFolder: 'statement-collector-child',
    version,
    fileCount,
    sizeBytes: zipBytes.length,
    sha256,
  };
}

if (process.argv[1] && import.meta.url.endsWith(process.argv[1].replace(/\\/g, '/'))) {
  const result = packageChildTheme();
  console.log(`Successfully packaged child theme ${result.version} (${result.sizeBytes} bytes, SHA256: ${result.sha256}) -> ${result.name}`);
}
