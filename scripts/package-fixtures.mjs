import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { lintPhp } from './php-lint.mjs';

const root = resolve(import.meta.dirname, '..');
const defaultVersion = '0.1.1';

export const approvedFixtureFiles = [
  'statement-integration-fixtures.php',
  'src/AdminPage.php',
  'src/FixtureService.php',
  'src/VerificationService.php',
  'src/CleanupService.php',
];

export function packageFixtures(version = defaultVersion) {
  const sourceRoot = join(root, 'tools', 'statement-integration-fixtures');
  const distDir = join(root, 'dist');
  const stagingParent = join(root, 'tmp', 'pkg-fixtures');
  const stagingDir = join(stagingParent, 'statement-integration-fixtures');
  const zipName = `statement-integration-fixtures-${version}.zip`;
  const zipPath = join(distDir, zipName);

  if (existsSync(stagingParent)) rmSync(stagingParent, { recursive: true, force: true });
  mkdirSync(stagingDir, { recursive: true });
  if (!existsSync(distDir)) mkdirSync(distDir, { recursive: true });

  let fileCount = 0;
  let phpCount = 0;

  for (const relFile of approvedFixtureFiles) {
    const srcFile = join(sourceRoot, relFile);
    const destFile = join(stagingDir, relFile);

    if (!existsSync(srcFile)) {
      throw new Error(`Missing approved fixture plugin file: ${relFile}`);
    }

    mkdirSync(dirname(destFile), { recursive: true });
    writeFileSync(destFile, readFileSync(srcFile));

    fileCount++;
    if (relFile.endsWith('.php')) phpCount++;
  }

  const lintResult = lintPhp({ roots: [stagingDir], log: false });
  if (lintResult.available && !lintResult.ok) {
    throw new Error(`PHP lint failed on fixture plugin files:\n  ${lintResult.failures.map((f) => f.output).join('\n  ')}`);
  }

  if (existsSync(zipPath)) rmSync(zipPath, { force: true });

  const tarCmd = `tar -caf "${zipPath}" -C "${stagingParent}" "statement-integration-fixtures"`;
  execSync(tarCmd, { cwd: root, stdio: 'pipe' });

  rmSync(stagingParent, { recursive: true, force: true });

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
    rootFolder: 'statement-integration-fixtures',
  };
}

if (process.argv[1] && process.argv[1].endsWith('package-fixtures.mjs')) {
  const result = packageFixtures();
  console.log(`Packaged Fixture Plugin: ${result.name} (${result.sizeBytes} bytes, SHA-256: ${result.sha256})`);
}
