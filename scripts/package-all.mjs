import { execSync } from 'node:child_process';
import { readFileSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { packagePlugin } from './package-plugin.mjs';
import { packageTheme } from './package-theme.mjs';
import { verifyPackage } from './verify-package.mjs';

const root = resolve(import.meta.dirname, '..');
const candidateVersion = '0.13.0-rc.1';

export function packageAll(options = {}) {
  const silent = options.silent ?? false;
  const log = (...args) => {
    if (!silent) console.log(...args);
  };

  log('Step 1: Verifying source repository prerequisites...');
  try {
    execSync('node scripts/verify-foundation.mjs', { cwd: root, stdio: 'pipe' });
  } catch (err) {
    throw new Error(`Source foundation verification failed prior to packaging: ${err.message}`);
  }

  log('Step 2: Packaging Statement Collector Theme...');
  const themePkg = packageTheme(candidateVersion);

  log('Step 3: Packaging Statement Collector Core Plugin...');
  const pluginPkg = packagePlugin(candidateVersion);

  log('Step 4: Verifying packaged theme ZIP artifact...');
  const themeVerify = verifyPackage(themePkg.path);
  if (!themeVerify.ok) {
    throw new Error(`Packaged theme verification failed:\n  ${themeVerify.errors.join('\n  ')}`);
  }

  log('Step 5: Verifying packaged plugin ZIP artifact...');
  const pluginVerify = verifyPackage(pluginPkg.path);
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
    theme_version: themePkg.version,
    plugin_version: pluginPkg.version,
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
  log('--------------------------------------------------');
  log('STATUS: NOT DEPLOYED. ATOMIC UPLOAD AUTHORIZATION REQUIRED.');
  log('==================================================\n');

  return { manifest, themePkg, pluginPkg };
}

if (process.argv[1] && process.argv[1].endsWith('package-all.mjs')) {
  try {
    packageAll();
  } catch (err) {
    console.error(`FATAL: Packaging failed: ${err.message}`);
    process.exit(1);
  }
}
