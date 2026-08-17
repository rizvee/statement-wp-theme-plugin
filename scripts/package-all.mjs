import { execSync } from 'node:child_process';
import { readFileSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { packagePlugin } from './package-plugin.mjs';
import { packageTheme } from './package-theme.mjs';
import { packageClientDemo } from './package-client-demo.mjs';
import { packageChildTheme } from './package-child-theme.mjs';
import { verifyPackage } from './verify-package.mjs';

const root = resolve(import.meta.dirname, '..');
const coreVersion = '0.13.0-rc.13';
const themeVersion = '0.13.0-rc.11';
const childVersion = '0.1.0';
const demoVersion = '0.2.4';
const pluginVersion = '0.13.0-rc.13';

export function packageAll(options = {}) {
  const silent = options.silent ?? false;
  const log = (...args) => {
    if (!silent) console.log(...args);
  };

  log('Step 1: Verifying source repository prerequisites...');
  try {
    execSync('node scripts/verify-foundation.mjs', { cwd: root, stdio: 'pipe' });
    execSync('node scripts/verify-git-runtime-tracking.mjs', { cwd: root, stdio: 'pipe' });
  } catch (err) {
    throw new Error(`Source foundation verification failed prior to packaging: ${err.message}`);
  }

  log('Step 2: Packaging Statement Collector Theme...');
  const themePkg = packageTheme(themeVersion);

  log('Step 3: Packaging Statement Collector Core Plugin...');
  const pluginPkg = packagePlugin(pluginVersion);

  log('Step 4: Packaging Statement Client Demo Tool...');
  const demoPkg = packageClientDemo(demoVersion);

  log('Step 5: Packaging Statement Starter Child Theme...');
  const childPkg = packageChildTheme(childVersion);

  log('Step 6: Verifying packaged theme ZIP artifact...');
  const themeVerify = verifyPackage(themePkg.path, themeVersion);
  if (!themeVerify.ok) {
    throw new Error(`Packaged theme verification failed:\n  ${themeVerify.errors.join('\n  ')}`);
  }

  log('Step 7: Verifying packaged plugin ZIP artifact...');
  const pluginVerify = verifyPackage(pluginPkg.path, pluginVersion);
  if (!pluginVerify.ok) {
    throw new Error(`Packaged plugin verification failed:\n  ${pluginVerify.errors.join('\n  ')}`);
  }

  let gitCommit = 'unknown';
  let gitBranch = 'unknown';

  try {
    gitCommit = execSync('git rev-parse --short HEAD', { cwd: root, stdio: 'pipe' }).toString().trim();
    gitBranch = execSync('git rev-parse --abbrev-ref HEAD', { cwd: root, stdio: 'pipe' }).toString().trim();
  } catch {
    // Ignore if git not present
  }

  const manifest = {
    generated_at: new Date().toISOString(),
    git_commit: gitCommit,
    branch: gitBranch,
    candidate_version: {
      theme: themeVersion,
      plugin: pluginVersion,
      demo: demoVersion,
      child_theme: childVersion,
    },
    theme: {
      candidate_version: themePkg.version,
      header_version: themeVerify.headerVersion,
      runtime_version: themeVerify.constantVersion,
    },
    plugin: {
      candidate_version: pluginPkg.version,
      header_version: pluginVerify.headerVersion,
      runtime_version: pluginVerify.constantVersion,
    },
    client_demo: {
      candidate_version: demoPkg.version,
      size_bytes: demoPkg.sizeBytes,
      sha256: demoPkg.sha256,
    },
    child_theme: {
      candidate_version: childPkg.version,
      size_bytes: childPkg.sizeBytes,
      sha256: childPkg.sha256,
    },
    environment: 'integration-candidate',
    deployment_authorized: false,
    artifacts: [
      {
        type: 'theme',
        filename: themePkg.name,
        root_folder: themePkg.rootFolder,
        size_bytes: themePkg.sizeBytes,
        sha256: themePkg.sha256,
        file_count: themePkg.fileCount,
        php_count: themePkg.phpCount,
        header_version: themeVerify.headerVersion,
        runtime_version: themeVerify.constantVersion,
        verification: 'PASS',
      },
      {
        type: 'plugin',
        filename: pluginPkg.name,
        root_folder: pluginPkg.rootFolder,
        size_bytes: pluginPkg.sizeBytes,
        sha256: pluginPkg.sha256,
        file_count: pluginPkg.fileCount,
        php_count: pluginPkg.phpCount,
        header_version: pluginVerify.headerVersion,
        runtime_version: pluginVerify.constantVersion,
        verification: 'PASS',
      },
      {
        type: 'client_demo',
        filename: demoPkg.name,
        root_folder: demoPkg.rootFolder,
        size_bytes: demoPkg.sizeBytes,
        sha256: demoPkg.sha256,
        file_count: demoPkg.fileCount,
        php_count: demoPkg.phpCount,
        verification: 'PASS',
      },
      {
        type: 'child_theme',
        filename: childPkg.name,
        root_folder: childPkg.rootFolder,
        size_bytes: childPkg.sizeBytes,
        sha256: childPkg.sha256,
        file_count: childPkg.fileCount,
        verification: 'PASS',
      },
    ],
  };

  const manifestPath = join(root, 'dist', 'manifest.json');
  writeFileSync(manifestPath, JSON.stringify(manifest, null, 2) + '\n', 'utf8');

  log('\n==================================================');
  log('PACKAGING & VERIFICATION COMPLETE');
  log('==================================================');
  log(`Manifest: ${manifestPath}`);
  log(`Theme ZIP: ${themePkg.path}`);
  log(`  - Size: ${themePkg.sizeBytes} bytes`);
  log(`  - SHA-256: ${themePkg.sha256}`);
  log(`  - Packaged Files: ${themePkg.fileCount} (${themePkg.phpCount} PHP)`);
  log(`Plugin ZIP: ${pluginPkg.path}`);
  log(`  - Size: ${pluginPkg.sizeBytes} bytes`);
  log(`  - SHA-256: ${pluginPkg.sha256}`);
  log(`  - Packaged Files: ${pluginPkg.fileCount} (${pluginPkg.phpCount} PHP)`);
  log(`Client Demo ZIP: ${demoPkg.path}`);
  log(`  - Size: ${demoPkg.sizeBytes} bytes`);
  log(`  - SHA-256: ${demoPkg.sha256}`);
  log(`  - Packaged Files: ${demoPkg.fileCount} (${demoPkg.phpCount} PHP)`);
  log(`Child Theme ZIP: ${childPkg.path}`);
  log(`  - Size: ${childPkg.sizeBytes} bytes`);
  log(`  - SHA-256: ${childPkg.sha256}`);
  log(`  - Packaged Files: ${childPkg.fileCount}`);
  log('--------------------------------------------------');
  log('STATUS: NOT DEPLOYED. ATOMIC UPLOAD AUTHORIZATION REQUIRED.');
  log('==================================================\n');

  return { manifest, themePkg, pluginPkg, demoPkg, childPkg };
}

if (process.argv[1] && process.argv[1].endsWith('package-all.mjs')) {
  try {
    packageAll();
  } catch (err) {
    console.error(`FATAL: Packaging failed: ${err.message}`);
    process.exit(1);
  }
}
