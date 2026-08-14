import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';
import { packageAll } from '../scripts/package-all.mjs';
import { packagePlugin } from '../scripts/package-plugin.mjs';
import { packageTheme } from '../scripts/package-theme.mjs';
import { verifyPackage } from '../scripts/verify-package.mjs';

const root = resolve(import.meta.dirname, '..');

test('Theme packaging generates single-root ZIP artifact', () => {
  const result = packageTheme('0.13.0-rc.1');
  assert.ok(existsSync(result.path), 'Theme ZIP must exist in dist/');
  assert.equal(result.rootFolder, 'statement-collector-theme', 'Theme root folder must be statement-collector-theme');
  assert.ok(result.fileCount > 0, 'File count must be greater than 0');
  assert.ok(result.phpCount > 0, 'PHP file count must be greater than 0');
  assert.ok(result.sha256.length === 64, 'SHA-256 hash must be 64 hex characters');
});

test('Plugin packaging generates single-root ZIP artifact', () => {
  const result = packagePlugin('0.13.0-rc.1');
  assert.ok(existsSync(result.path), 'Plugin ZIP must exist in dist/');
  assert.equal(result.rootFolder, 'statement-collector-core', 'Plugin root folder must be statement-collector-core');
  assert.ok(result.fileCount > 0, 'File count must be greater than 0');
  assert.ok(result.phpCount > 0, 'PHP file count must be greater than 0');
  assert.ok(result.sha256.length === 64, 'SHA-256 hash must be 64 hex characters');
});

test('Package verification confirms exclusions, required files, and PHP syntax', () => {
  const themePath = resolve(root, 'dist', 'statement-collector-theme-0.13.0-rc.1.zip');
  const pluginPath = resolve(root, 'dist', 'statement-collector-core-0.13.0-rc.1.zip');

  const themeVerify = verifyPackage(themePath);
  assert.ok(themeVerify.ok, `Theme package verification must pass. Errors: ${themeVerify.errors?.join(', ')}`);
  assert.equal(themeVerify.rootFolder, 'statement-collector-theme');

  const pluginVerify = verifyPackage(pluginPath);
  assert.ok(pluginVerify.ok, `Plugin package verification must pass. Errors: ${pluginVerify.errors?.join(', ')}`);
  assert.equal(pluginVerify.rootFolder, 'statement-collector-core');
});

test('Package verifier rejects non-existent or invalid ZIP files', () => {
  const badResult = verifyPackage(resolve(root, 'dist', 'non-existent-file.zip'));
  assert.equal(badResult.ok, false, 'Verifier must fail for non-existent ZIP file');
});

test('Master packageAll script generates manifest with deployment_authorized = false', () => {
  const { manifest } = packageAll({ silent: true });
  const manifestPath = resolve(root, 'dist', 'manifest.json');
  assert.ok(existsSync(manifestPath), 'manifest.json must exist in dist/');

  assert.equal(manifest.deployment_authorized, false, 'deployment_authorized must be false in Phase 1');
  assert.equal(manifest.environment, 'integration-candidate', 'environment must be integration-candidate');
  assert.equal(manifest.artifacts.length, 2, 'Manifest must describe 2 artifacts');
  assert.equal(manifest.artifacts[0].verification, 'PASS', 'Theme verification must be PASS');
  assert.equal(manifest.artifacts[1].verification, 'PASS', 'Plugin verification must be PASS');
});

test('Packaging scripts contain zero remote upload or deployment commands', () => {
  const scriptFiles = [
    resolve(root, 'scripts', 'package-theme.mjs'),
    resolve(root, 'scripts', 'package-plugin.mjs'),
    resolve(root, 'scripts', 'verify-package.mjs'),
    resolve(root, 'scripts', 'package-all.mjs'),
  ];

  const forbiddenDeploymentPatterns = [
    /wordpress\.com/i,
    /wp-cli/i,
    /\bftp\b/i,
    /\bsftp\b/i,
    /\bssh\s/i,
    /cpanel/i,
    /api\.wordpress/i,
    /git push/i,
  ];

  for (const file of scriptFiles) {
    const content = readFileSync(file, 'utf8');
    for (const pattern of forbiddenDeploymentPatterns) {
      assert.ok(!pattern.test(content), `Forbidden remote deployment pattern ${pattern} found in ${file}`);
    }
  }
});
