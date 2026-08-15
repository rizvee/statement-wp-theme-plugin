import { execSync } from 'node:child_process';
import { readdirSync, readFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

function getTrackedFiles() {
  const output = execSync('git ls-files', { encoding: 'utf8' });
  return new Set(output.split(/\r?\n/).map(f => f.trim().replace(/\\/g, '/')).filter(Boolean));
}

function walkFiles(dir, out = []) {
  if (!existsSync(dir)) return out;
  for (const item of readdirSync(dir, { withFileTypes: true })) {
    const p = join(dir, item.name);
    if (item.isDirectory()) {
      walkFiles(p, out);
    } else {
      out.push(p.replace(/\\/g, '/'));
    }
  }
  return out;
}

console.log('Step 1: Fetching tracked Git files...');
const tracked = getTrackedFiles();

// A. Assert explicit critical files
const CRITICAL_FILES = [
  'wp-content/plugins/statement-collector-core/statement-collector-core.php',
  'wp-content/plugins/statement-collector-core/src/Access/Secrets.php',
  'wp-content/plugins/statement-collector-core/src/Access/SecretVault.php',
  'wp-content/plugins/statement-collector-core/src/Access/Crypto.php',
  'wp-content/plugins/statement-collector-core/src/Access/EligibilityService.php',
  'wp-content/plugins/statement-collector-core/src/Access/DropConfigAdmin.php',
  'wp-content/themes/statement-collector-theme/style.css',
  'wp-content/themes/statement-collector-theme/functions.php',
  'tools/statement-integration-fixtures/statement-integration-fixtures.php',
  'tools/statement-integration-fixtures/src/PrivateFixtureService.php',
];

for (const file of CRITICAL_FILES) {
  if (!tracked.has(file)) {
    console.error(`FATAL: Critical runtime file missing from Git tracking: ${file}`);
    process.exit(1);
  }
}
console.log('PASS: All critical runtime files are tracked in Git.');

// B. Parse require_once statements in Core entrypoint to ensure all dependencies are tracked
const coreEntry = 'wp-content/plugins/statement-collector-core/statement-collector-core.php';
const entryContent = readFileSync(coreEntry, 'utf8');
const requireRegex = /require_once\s+__DIR__\s*\.\s*'([^']+)'/g;
let match;
while ((match = requireRegex.exec(entryContent)) !== null) {
  const relPath = match[1]; // e.g. '/src/Access/Secrets.php'
  const fullRelPath = `wp-content/plugins/statement-collector-core${relPath}`;
  if (!tracked.has(fullRelPath)) {
    console.error(`FATAL: Core require_once target is untracked in Git: ${fullRelPath}`);
    process.exit(1);
  }
}
console.log('PASS: All require_once dependencies in Core entrypoint are tracked in Git.');

// C. Verify all PHP runtime files under core, theme, and fixture tools are tracked
const RUNTIME_ROOTS = [
  'wp-content/plugins/statement-collector-core',
  'wp-content/themes/statement-collector-theme',
  'tools/statement-integration-fixtures',
];

const untrackedRuntime = [];
for (const root of RUNTIME_ROOTS) {
  const localFiles = walkFiles(root);
  for (const file of localFiles) {
    if (file.endsWith('.php') || file.endsWith('.css') || file.endsWith('.js') || file.endsWith('.json')) {
      if (!tracked.has(file)) {
        untrackedRuntime.push(file);
      }
    }
  }
}

if (untrackedRuntime.length > 0) {
  console.error('FATAL: Untracked runtime source files discovered in Git source tree:');
  console.error(untrackedRuntime.join('\n'));
  process.exit(1);
}
console.log('PASS: 100% of local runtime source files under Core, Theme, and Fixture tools are tracked in Git.');

// D. Verify forbidden files remain untracked
const FORBIDDEN_PATTERNS = [
  /\.env.*/,
  /^wp-config\.php$/,
  /^\.local-runtime\/.*/,
  /^\.local-tools\/.*/,
  /^dist\/.*/,
  /credentials\.json/,
];

const forbiddenTracked = [];
for (const file of tracked) {
  for (const pattern of FORBIDDEN_PATTERNS) {
    if (pattern.test(file)) {
      forbiddenTracked.push(file);
      break;
    }
  }
}

if (forbiddenTracked.length > 0) {
  console.error('FATAL: Forbidden local/secret files tracked in Git:');
  console.error(forbiddenTracked.join('\n'));
  process.exit(1);
}
console.log('PASS: All secret, local-runtime, credential, and build-artifact paths remain strictly untracked.');
console.log('==================================================');
console.log('GIT RUNTIME TRACKING VERIFICATION: PASS');
console.log('==================================================');
