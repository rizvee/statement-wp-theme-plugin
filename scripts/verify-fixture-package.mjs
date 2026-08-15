import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { lintPhp } from './php-lint.mjs';

const projectRoot = resolve(import.meta.dirname, '..');
const targetVersion = process.argv[2] || '0.2.2';
const zipPath = join(projectRoot, 'dist', `statement-integration-fixtures-${targetVersion}.zip`);

if (!existsSync(zipPath)) {
  console.error(`Missing fixture ZIP file: ${zipPath}`);
  process.exit(1);
}

const zipBytes = readFileSync(zipPath);
const sha256 = createHash('sha256').update(zipBytes).digest('hex');
const sizeBytes = zipBytes.length;

const tmpParent = join(projectRoot, 'tmp', `verify-fixture-pkg-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`);
if (existsSync(tmpParent)) rmSync(tmpParent, { recursive: true, force: true });
mkdirSync(tmpParent, { recursive: true });

execSync(`tar -xf "${zipPath}" -C "${tmpParent}"`, { cwd: projectRoot });

const extractedRoot = join(tmpParent, 'statement-integration-fixtures');
if (!existsSync(extractedRoot)) {
  console.error(`Package root statement-integration-fixtures missing in ${zipPath}`);
  process.exit(1);
}

const mainPhpPath = join(extractedRoot, 'statement-integration-fixtures.php');
if (!existsSync(mainPhpPath)) {
  console.error(`Main file statement-integration-fixtures.php missing in package.`);
  process.exit(1);
}

const mainContent = readFileSync(mainPhpPath, 'utf8');
const headerMatch = mainContent.match(/^[ \t\/*#]*Version:\s*(.+)$/m);
const headerVer = headerMatch ? headerMatch[1].trim() : 'NONE';

if (headerVer !== targetVersion) {
  console.error(`Header version mismatch: expected ${targetVersion}, got ${headerVer}`);
  process.exit(1);
}

const lintResult = lintPhp({ roots: [extractedRoot], log: false });
if (lintResult.available && !lintResult.ok) {
  console.error(`PHP lint failed on extracted fixture package:\n  ${lintResult.failures.map((f) => f.output).join('\n  ')}`);
  process.exit(1);
}

rmSync(tmpParent, { recursive: true, force: true });

console.log('FIXTURE PACKAGE VERIFICATION PASS:');
console.log(`  Filename: ${zipPath}`);
console.log(`  Size:     ${sizeBytes} bytes`);
console.log(`  SHA-256:  ${sha256}`);
console.log(`  Header:   Version: ${headerVer}`);
console.log(`  PHP Lint: ${lintResult.files.length} PHP files passed clean`);
