import { spawnSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const projectRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..', '..');

function inspectCandidate(executable, source) {
  const result = spawnSync(executable, ['-r', 'echo PHP_VERSION;'], {
    encoding: 'utf8',
    shell: false,
    windowsHide: true,
  });
  if (result.status !== 0) return null;
  return { executable, source, version: result.stdout.trim() };
}

export function resolvePhp() {
  if (process.env.PHP_BIN?.trim()) {
    return inspectCandidate(process.env.PHP_BIN.trim(), 'PHP_BIN');
  }

  const candidates = [];
  if (process.platform === 'win32') {
    const localPhp = resolve(projectRoot, '.local-tools', 'php', 'php.exe');
    if (existsSync(localPhp)) candidates.push({ executable: localPhp, source: 'project-local' });
    candidates.push({ executable: 'php.exe', source: 'PATH' });
  } else {
    candidates.push({ executable: 'php', source: 'PATH' });
  }

  for (const candidate of candidates) {
    const resolved = inspectCandidate(candidate.executable, candidate.source);
    if (resolved) return resolved;
  }
  return null;
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  const php = resolvePhp();
  if (!php) {
    console.error('PHP unavailable. Set PHP_BIN, add .local-tools/php/php.exe, or expose php on PATH.');
    process.exit(1);
  }
  console.log(`PHP executable: ${php.executable}`);
  console.log(`PHP source: ${php.source}`);
  console.log(`PHP version: ${php.version}`);
}
