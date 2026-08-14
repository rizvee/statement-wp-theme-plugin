import { spawnSync } from 'node:child_process';
import { existsSync, readdirSync, statSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, extname, relative, resolve } from 'node:path';
import { resolvePhp } from './lib/resolve-php.mjs';

const projectRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const ignoredDirectories = new Set([
  '.cache', '.git', '.local-tools', 'build', 'coverage', 'dist', 'node_modules', 'tmp', 'vendor',
]);

function collectPhpFiles(path, output = []) {
  if (!existsSync(path)) return output;
  const stat = statSync(path);
  if (stat.isFile()) {
    if (extname(path).toLowerCase() === '.php') output.push(resolve(path));
    return output;
  }
  for (const entry of readdirSync(path, { withFileTypes: true })) {
    if (entry.isDirectory() && ignoredDirectories.has(entry.name)) continue;
    collectPhpFiles(resolve(path, entry.name), output);
  }
  return output;
}

export function lintPhp({ roots, log = true } = {}) {
  const requestedRoots = roots?.length
    ? roots.map((path) => resolve(path))
    : [
        resolve(projectRoot, 'wp-content', 'themes', 'statement-collector-theme'),
        resolve(projectRoot, 'wp-content', 'plugins', 'statement-collector-core'),
        resolve(projectRoot, 'tools'),
      ];
  const files = [...new Set(requestedRoots.flatMap((path) => collectPhpFiles(path)))].sort();
  const php = resolvePhp();

  if (!php) {
    const message = `PHP unavailable; cannot lint ${files.length} PHP file(s).`;
    if (log) console.error(message);
    return { ok: files.length === 0, available: false, files, message };
  }

  if (log) {
    console.log(`PHP executable: ${php.executable}`);
    console.log(`PHP version: ${php.version}`);
    console.log(`PHP files: ${files.length}`);
  }

  const failures = [];
  for (const file of files) {
    const result = spawnSync(php.executable, ['-l', file], {
      encoding: 'utf8',
      shell: false,
      windowsHide: true,
    });
    if (result.status !== 0) {
      failures.push({ file, output: `${result.stdout || ''}${result.stderr || ''}`.trim() });
    }
  }

  if (failures.length) {
    if (log) {
      for (const failure of failures) {
        console.error(`FAIL: ${relative(projectRoot, failure.file)}: ${failure.output}`);
      }
      console.error(`PHP lint failed: ${failures.length} of ${files.length} file(s).`);
    }
    return { ok: false, available: true, files, failures, php };
  }

  if (log) console.log(`PASS: PHP lint passed (${files.length} PHP files).`);
  return { ok: true, available: true, files, failures, php };
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  const result = lintPhp({ roots: process.argv.slice(2) });
  process.exit(result.ok ? 0 : 1);
}
