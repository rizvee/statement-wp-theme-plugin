import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { extname, join, relative, resolve } from 'node:path';
import { lintPhp } from './php-lint.mjs';

const root = resolve(import.meta.dirname, '..');
const failures = [];
const notes = [];

const requiredFiles = [
  'AGENTS.md',
  'MEMORY.md',
  'TASKS.md',
  'RUNBOOK.md',
  'README.md',
  '.gitignore',
  '.ai/context/current-state.md',
  '.ai/context/project-brief.md',
  '.ai/context/architecture.md',
  '.ai/context/business-rules.md',
  '.ai/context/design-system.md',
  '.ai/context/deployment-rules.md',
  '.ai/checks/m0-foundation.md',
  '.ai/prompts/milestone-task.md',
  '.ai/skills/repository-verification.md',
  'scripts/lib/resolve-php.mjs',
  'scripts/php-lint.mjs',
  'tests/m1-skeleton.test.mjs',
  'tests/m2-design-system.test.mjs',
  'tests/php/m1-bootstrap-smoke.php',
];

const requiredDirectories = [
  '.ai/session-logs',
  'wp-content/themes/statement-collector-theme',
  'wp-content/plugins/statement-collector-core',
  'docs',
  'releases',
  'scripts',
  'tests',
];

function fail(message) {
  failures.push(message);
}

function text(path) {
  return readFileSync(join(root, path), 'utf8');
}

function walk(directory, output = []) {
  if (!existsSync(directory)) return output;
  for (const entry of readdirSync(directory, { withFileTypes: true })) {
    if (entry.name === '.git' || entry.name === '.local-tools' || entry.name === 'node_modules' || entry.name === 'vendor') continue;
    const fullPath = join(directory, entry.name);
    if (entry.isDirectory()) walk(fullPath, output);
    else output.push(fullPath);
  }
  return output;
}

for (const path of requiredFiles) {
  if (!existsSync(join(root, path)) || !statSync(join(root, path)).isFile()) fail(`Missing file: ${path}`);
}

for (const path of requiredDirectories) {
  if (!existsSync(join(root, path)) || !statSync(join(root, path)).isDirectory()) fail(`Missing directory: ${path}`);
}

const contentSignals = [
  ['.ai/context/business-rules.md', 'NEVER RESTOCKED'],
  ['.ai/context/business-rules.md', 'PERMANENT ARCHIVE'],
  ['.ai/context/business-rules.md', 'normal WooCommerce stock manipulation'],
  ['.ai/context/architecture.md', 'statement-collector-theme'],
  ['.ai/context/architecture.md', 'statement-collector-core'],
  ['.ai/context/deployment-rules.md', 'https://mystatement.store/'],
  ['.ai/context/deployment-rules.md', 'Never auto-deploy'],
  ['TASKS.md', 'M1 — Theme Skeleton + Core Plugin Skeleton'],
];

for (const [path, signal] of contentSignals) {
  if (existsSync(join(root, path)) && !text(path).includes(signal)) fail(`Missing locked signal in ${path}: ${signal}`);
}

const runtimeRoots = [
  'wp-content/themes/statement-collector-theme',
  'wp-content/plugins/statement-collector-core',
];
const approvedRuntimeFiles = [
  'wp-content/themes/statement-collector-theme/style.css',
  'wp-content/themes/statement-collector-theme/functions.php',
  'wp-content/themes/statement-collector-theme/index.php',
  'wp-content/themes/statement-collector-theme/header.php',
  'wp-content/themes/statement-collector-theme/footer.php',
  'wp-content/themes/statement-collector-theme/theme.json',
  'wp-content/themes/statement-collector-theme/assets/css/base.css',
  'wp-content/themes/statement-collector-theme/assets/css/layout.css',
  'wp-content/themes/statement-collector-theme/inc/assets.php',
  'wp-content/themes/statement-collector-theme/inc/setup.php',
  'wp-content/themes/statement-collector-theme/inc/woocommerce.php',
  'wp-content/plugins/statement-collector-core/statement-collector-core.php',
  'wp-content/plugins/statement-collector-core/src/Plugin.php',
];
const runtimeFiles = runtimeRoots.flatMap((runtimeRoot) => walk(join(root, runtimeRoot)))
  .map((path) => relative(root, path).replaceAll('\\', '/'));
const hasRuntime = approvedRuntimeFiles.some((path) => runtimeFiles.includes(path));

if (hasRuntime) {
  for (const path of approvedRuntimeFiles) {
    if (!runtimeFiles.includes(path)) fail(`Missing approved runtime file: ${path}`);
  }
  const unexpected = runtimeFiles.filter((path) => !approvedRuntimeFiles.includes(path));
  if (unexpected.length) fail(`Unexpected runtime file: ${unexpected.join(', ')}`);
} else {
  const unexpected = runtimeFiles.filter((path) => !path.endsWith('.gitkeep'));
  if (unexpected.length) fail(`Pre-M1 runtime root contains premature implementation: ${unexpected.join(', ')}`);
}

const allFiles = walk(root);
const textualExtensions = new Set(['', '.css', '.html', '.ini', '.js', '.json', '.jsx', '.md', '.mjs', '.php', '.ps1', '.sh', '.text', '.toml', '.ts', '.tsx', '.txt', '.xml', '.yaml', '.yml']);
const privateKeyPattern = new RegExp('-----BE' + 'GIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----', 'i');
const openAiPattern = new RegExp('\\b' + 'sk-' + '[A-Za-z0-9_-]{20,}\\b');
const githubPattern = new RegExp('\\bgh[pousr]_[A-Za-z0-9]{20,}\\b');
const awsPattern = new RegExp('\\bAKIA[A-Z0-9]{16}\\b');
const assignmentPattern = new RegExp("(?:pass" + "word|api[_-]?key|client[_-]?secret|access[_-]?token)\\s*[:=]\\s*[\"'][^\"'\\r\\n]{8,}[\"']", 'i');
const secretPatterns = [privateKeyPattern, openAiPattern, githubPattern, awsPattern, assignmentPattern];

for (const path of allFiles) {
  const rel = relative(root, path).replaceAll('\\\\', '/');
  if (!textualExtensions.has(extname(path).toLowerCase()) || statSync(path).size > 2_000_000) continue;
  const contents = readFileSync(path, 'utf8');
  if (contents.includes('\0')) fail(`Unexpected NUL byte in text file: ${rel}`);
  if (contents.length && !contents.endsWith('\n')) fail(`Missing final newline: ${rel}`);
  if (contents.split(/\r?\n/).some((line) => /[ \t]+$/.test(line))) fail(`Trailing whitespace: ${rel}`);
  if (secretPatterns.some((pattern) => pattern.test(contents))) fail(`Possible secret pattern: ${rel}`);
}

const zips = allFiles.filter((path) => extname(path).toLowerCase() === '.zip').map((path) => relative(root, path));
if (zips.length) fail(`Generated ZIPs are out of scope: ${zips.join(', ')}`);

const phpLint = lintPhp({ log: false });
if (phpLint.available) {
  if (!phpLint.ok) {
    for (const failure of phpLint.failures) {
      fail(`PHP syntax failed: ${relative(root, failure.file)} (${failure.output})`);
    }
  }
  notes.push(`PHP ${phpLint.php.version} available via ${phpLint.php.source}; linted ${phpLint.files.length} PHP file(s).`);
} else {
  const message = `PHP unavailable; PHP lint not run (${phpLint.files.length} PHP file(s) present).`;
  if (phpLint.files.length) fail(message);
  else notes.push(`LIMITATION: ${message}`);
}

for (const note of notes) console.log(note);
if (failures.length) {
  for (const failure of failures) console.error(`FAIL: ${failure}`);
  console.error(`Foundation verification failed with ${failures.length} issue(s).`);
  process.exit(1);
}

console.log(`PASS: ${requiredFiles.length} required files and ${requiredDirectories.length} required directories found.`);
console.log(`PASS: locked architecture, business-rule, deployment, secret, package, and milestone scope checks passed.`);
