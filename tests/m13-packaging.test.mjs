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

test('Theme packaging generates single-root ZIP artifact with matching 0.13.0-rc.15 version', () => {
  const result = packageTheme('0.13.0-rc.15');
  assert.ok(existsSync(result.path), 'Theme ZIP must exist in dist/');
  assert.equal(result.rootFolder, 'statement-collector-theme', 'Theme root folder must be statement-collector-theme');
  assert.equal(result.version, '0.13.0-rc.15');
  assert.ok(result.fileCount > 0, 'File count must be greater than 0');
  assert.ok(result.phpCount > 0, 'PHP file count must be greater than 0');
  assert.ok(result.sha256.length === 64, 'SHA-256 hash must be 64 hex characters');
});

test('Plugin packaging generates single-root ZIP artifact with matching 0.13.0-rc.13 version', () => {
  const result = packagePlugin('0.13.0-rc.13');
  assert.ok(existsSync(result.path), 'Plugin ZIP must exist in dist/');
  assert.equal(result.rootFolder, 'statement-collector-core', 'Plugin root folder must be statement-collector-core');
  assert.equal(result.version, '0.13.0-rc.13');
  assert.ok(result.fileCount > 0, 'File count must be greater than 0');
  assert.ok(result.phpCount > 0, 'PHP file count must be greater than 0');
  assert.ok(result.sha256.length === 64, 'SHA-256 hash must be 64 hex characters');
});

test('Package verification confirms headers, constants, exclusions, and PHP syntax', () => {
  const themePath = resolve(root, 'dist', 'statement-collector-theme-0.13.0-rc.15.zip');
  const pluginPath = resolve(root, 'dist', 'statement-collector-core-0.13.0-rc.13.zip');

  const themeVerify = verifyPackage(themePath, '0.13.0-rc.15');
  assert.ok(themeVerify.ok, `Theme package verification must pass. Errors: ${themeVerify.errors?.join(', ')}`);
  assert.equal(themeVerify.rootFolder, 'statement-collector-theme');
  assert.equal(themeVerify.headerVersion, '0.13.0-rc.15');
  assert.equal(themeVerify.constantVersion, '0.13.0-rc.15');

  const pluginVerify = verifyPackage(pluginPath, '0.13.0-rc.13');
  assert.ok(pluginVerify.ok, `Plugin package verification must pass. Errors: ${pluginVerify.errors?.join(', ')}`);
  assert.equal(pluginVerify.rootFolder, 'statement-collector-core');
  assert.equal(pluginVerify.headerVersion, '0.13.0-rc.13');
  assert.equal(pluginVerify.constantVersion, '0.13.0-rc.13');
});

test('Negative regression test: verifier rejects ZIP when internal plugin header Version is 0.1.0 vs filename 0.13.0-rc.11', () => {
  const mockStagingParent = resolve(root, 'tmp', 'test-mock-plugin');
  const mockStagingDir = resolve(mockStagingParent, 'statement-collector-core');
  const mockZip = resolve(root, 'tmp', 'statement-collector-core-0.13.0-rc.11.zip');

  if (existsSync(mockStagingParent)) rmSync(mockStagingParent, { recursive: true, force: true });
  mkdirSync(mockStagingDir, { recursive: true });

  const mockHeaderContent = `<?php
/*
Plugin Name: Statement Collector Core
Version: 0.1.0
*/
`;
  writeFileSync(resolve(mockStagingDir, 'statement-collector-core.php'), mockHeaderContent, 'utf8');

  if (existsSync(mockZip)) rmSync(mockZip, { force: true });
  execSync(`tar -caf "${mockZip}" -C "${mockStagingParent}" "statement-collector-core"`, { cwd: root, stdio: 'pipe' });

  const verifyResult = verifyPackage(mockZip, '0.13.0-rc.11');
  assert.equal(verifyResult.ok, false, 'Verifier must fail when header version does not match expected version');
  assert.ok(
    verifyResult.errors.some((err) => err.includes('Version mismatch')),
    'Must report header version mismatch error'
  );

  rmSync(mockStagingParent, { recursive: true, force: true });
  if (existsSync(mockZip)) rmSync(mockZip, { force: true });
});

test('Package verifier rejects non-existent or invalid ZIP files', () => {
  const badResult = verifyPackage(resolve(root, 'dist', 'non-existent-file.zip'));
  assert.equal(badResult.ok, false, 'Verifier must fail for non-existent ZIP file');
});

test('packageAll orchestration generates verified artifacts and manifest without mutations', () => {
  const { manifest } = packageAll({ silent: true });

  const manifestPath = resolve(root, 'dist', 'manifest.json');
  assert.ok(existsSync(manifestPath), 'manifest.json must exist in dist/');

  assert.equal(manifest.plugin.header_version, '0.13.0-rc.13', 'plugin header_version must be 0.13.0-rc.13');
  assert.equal(manifest.plugin.runtime_version, '0.13.0-rc.13', 'plugin runtime_version must be 0.13.0-rc.13');
  assert.equal(manifest.theme.header_version, '0.13.0-rc.15', 'theme header_version must be 0.13.0-rc.15');
  assert.equal(manifest.theme.runtime_version, '0.13.0-rc.15', 'theme runtime_version must be 0.13.0-rc.15');

  assert.equal(manifest.deployment_authorized, false, 'deployment_authorized must be false');
  assert.equal(manifest.environment, 'integration-candidate', 'environment must be integration-candidate');
  assert.ok(manifest.artifacts.length >= 3, 'Manifest must describe at least 3 artifacts');
  assert.equal(manifest.artifacts[0].verification, 'PASS', 'Theme verification must be PASS');
  assert.equal(manifest.artifacts[1].verification, 'PASS', 'Plugin verification must be PASS');
  assert.equal(manifest.artifacts[2].verification, 'PASS', 'Client Demo verification must be PASS');
});
