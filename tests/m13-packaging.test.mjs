import assert from 'node:assert/strict';
import { execSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';
import { packageAll } from '../scripts/package-all.mjs';
import { packagePlugin } from '../scripts/package-plugin.mjs';
import { packageTheme } from '../scripts/package-theme.mjs';
import { verifyPackage } from '../scripts/verify-package.mjs';

const root = resolve(import.meta.dirname, '..');

test('Theme packaging generates single-root ZIP artifact with matching 0.13.0-rc.4 version', () => {
  const result = packageTheme('0.13.0-rc.4');
  assert.ok(existsSync(result.path), 'Theme ZIP must exist in dist/');
  assert.equal(result.rootFolder, 'statement-collector-theme', 'Theme root folder must be statement-collector-theme');
  assert.equal(result.version, '0.13.0-rc.4');
  assert.ok(result.fileCount > 0, 'File count must be greater than 0');
  assert.ok(result.phpCount > 0, 'PHP file count must be greater than 0');
  assert.ok(result.sha256.length === 64, 'SHA-256 hash must be 64 hex characters');
});

test('Plugin packaging generates single-root ZIP artifact with matching 0.13.0-rc.9 version', () => {
  const result = packagePlugin('0.13.0-rc.9');
  assert.ok(existsSync(result.path), 'Plugin ZIP must exist in dist/');
  assert.equal(result.rootFolder, 'statement-collector-core', 'Plugin root folder must be statement-collector-core');
  assert.equal(result.version, '0.13.0-rc.9');
  assert.ok(result.fileCount > 0, 'File count must be greater than 0');
  assert.ok(result.phpCount > 0, 'PHP file count must be greater than 0');
  assert.ok(result.sha256.length === 64, 'SHA-256 hash must be 64 hex characters');
});

test('Package verification confirms headers, constants, exclusions, and PHP syntax', () => {
  const themePath = resolve(root, 'dist', 'statement-collector-theme-0.13.0-rc.4.zip');
  const pluginPath = resolve(root, 'dist', 'statement-collector-core-0.13.0-rc.9.zip');

  const themeVerify = verifyPackage(themePath, '0.13.0-rc.4');
  assert.ok(themeVerify.ok, `Theme package verification must pass. Errors: ${themeVerify.errors?.join(', ')}`);
  assert.equal(themeVerify.rootFolder, 'statement-collector-theme');
  assert.equal(themeVerify.headerVersion, '0.13.0-rc.4');
  assert.equal(themeVerify.constantVersion, '0.13.0-rc.4');


  const pluginVerify = verifyPackage(pluginPath, '0.13.0-rc.9');
  assert.ok(pluginVerify.ok, `Plugin package verification must pass. Errors: ${pluginVerify.errors?.join(', ')}`);
  assert.equal(pluginVerify.rootFolder, 'statement-collector-core');
  assert.equal(pluginVerify.headerVersion, '0.13.0-rc.9');
  assert.equal(pluginVerify.constantVersion, '0.13.0-rc.9');
});

test('Negative regression test: verifier rejects ZIP when internal plugin header Version is 0.1.0 vs filename 0.13.0-rc.9', () => {
  const mockStagingParent = resolve(root, 'tmp', 'test-mock-plugin');
  const mockStagingDir = resolve(mockStagingParent, 'statement-collector-core');
  const mockZip = resolve(root, 'tmp', 'statement-collector-core-0.13.0-rc.9.zip');

  if (existsSync(mockStagingParent)) rmSync(mockStagingParent, { recursive: true, force: true });
  mkdirSync(mockStagingDir, { recursive: true });

  const mockHeaderContent = `<?php
/*
Plugin Name: Statement Collector Core
Version: 0.1.0
*/
define( 'STATEMENT_COLLECTOR_CORE_VERSION', '0.1.0' );
`;
  writeFileSync(resolve(mockStagingDir, 'statement-collector-core.php'), mockHeaderContent, 'utf8');
  mkdirSync(resolve(mockStagingDir, 'src'), { recursive: true });
  writeFileSync(resolve(mockStagingDir, 'src/Plugin.php'), '<?php class Plugin {}', 'utf8');

  execSync(`tar -caf "${mockZip}" -C "${mockStagingParent}" "statement-collector-core"`, { cwd: root, stdio: 'pipe' });
  rmSync(mockStagingParent, { recursive: true, force: true });

  const verifyResult = verifyPackage(mockZip, '0.13.0-rc.9');
  rmSync(mockZip, { force: true });

  assert.equal(verifyResult.ok, false, 'Verifier MUST fail when plugin header Version (0.1.0) mismatches expected candidate (0.13.0-rc.9)');
  assert.ok(
    verifyResult.errors.some((err) => err.includes('statement-collector-core.php Version mismatch')),
    `Errors must include version mismatch detail. Got: ${verifyResult.errors.join('; ')}`
  );
});

test('Package verifier rejects non-existent or invalid ZIP files', () => {
  const badResult = verifyPackage(resolve(root, 'dist', 'non-existent-file.zip'));
  assert.equal(badResult.ok, false, 'Verifier must fail for non-existent ZIP file');
});

test('Master packageAll script generates manifest with candidate_versions and deployment_authorized = false', () => {
  const { manifest } = packageAll({ silent: true });
  const manifestPath = resolve(root, 'dist', 'manifest.json');
  assert.ok(existsSync(manifestPath), 'manifest.json must exist in dist/');

  assert.equal(manifest.plugin.header_version, '0.13.0-rc.9', 'plugin header_version must be 0.13.0-rc.9');
  assert.equal(manifest.plugin.runtime_version, '0.13.0-rc.9', 'plugin runtime_version must be 0.13.0-rc.9');
  assert.equal(manifest.theme.header_version, '0.13.0-rc.4', 'theme header_version must be 0.13.0-rc.4');
  assert.equal(manifest.theme.runtime_version, '0.13.0-rc.4', 'theme runtime_version must be 0.13.0-rc.4');

  assert.equal(manifest.deployment_authorized, false, 'deployment_authorized must be false');
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
