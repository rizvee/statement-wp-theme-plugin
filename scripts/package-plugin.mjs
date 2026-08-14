import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const defaultVersion = '0.13.0-rc.1';

export const approvedPluginFiles = [
  'statement-collector-core.php',
  'src/Plugin.php',
  'src/PublicApi.php',
  'src/Catalog/Visibility.php',
  'src/Cart/Integrity.php',
  'src/Drop/Taxonomy.php',
  'src/Product/Admin.php',
  'src/Product/Access.php',
  'src/Product/Metadata.php',
  'src/Release/ReleaseState.php',
  'src/Release/Purchasability.php',
  'src/Access/Secrets.php',
  'src/Access/Crypto.php',
  'src/Access/Schema.php',
  'src/Access/GrantService.php',
  'src/Access/SessionService.php',
  'src/Access/TokenService.php',
  'src/Access/RateLimiter.php',
  'src/Access/ConsentService.php',
  'src/Access/DropConfig.php',
  'src/Access/Precheck.php',
  'src/Access/EligibilityService.php',
  'src/Access/MakeDropLive.php',
  'src/Access/PrivateAccessGate.php',
  'src/Access/OrderAudit.php',
  'src/Access/EmailAccessGranted.php',
  'src/Access/EmailAccessReminder.php',
  'src/Access/ReminderService.php',
  'src/Access/UnsubscribeService.php',
  'src/Access/AdminUi.php',
  'src/Access/RetentionService.php',
  'src/Access/CacheHardening.php',
  'src/Order/Provenance.php',
  'src/Order/Completion.php',
  'src/Order/AdminOrderView.php',
  'src/Order/CustomerOrderView.php',
  'src/Order/EmailIntegration.php',
];

export function packagePlugin(version = defaultVersion) {
  const sourceRoot = join(root, 'wp-content', 'plugins', 'statement-collector-core');
  const distDir = join(root, 'dist');
  const stagingParent = join(root, 'tmp', 'pkg-plugin');
  const stagingDir = join(stagingParent, 'statement-collector-core');
  const zipName = `statement-collector-core-${version}.zip`;
  const zipPath = join(distDir, zipName);

  if (existsSync(stagingParent)) rmSync(stagingParent, { recursive: true, force: true });
  mkdirSync(stagingDir, { recursive: true });
  if (!existsSync(distDir)) mkdirSync(distDir, { recursive: true });

  let fileCount = 0;
  let phpCount = 0;

  for (const relFile of approvedPluginFiles) {
    const srcFile = join(sourceRoot, relFile);
    const destFile = join(stagingDir, relFile);

    if (!existsSync(srcFile)) {
      throw new Error(`Missing approved plugin runtime file: ${relFile}`);
    }

    mkdirSync(dirname(destFile), { recursive: true });
    writeFileSync(destFile, readFileSync(srcFile));

    fileCount++;
    if (relFile.endsWith('.php')) phpCount++;
  }

  if (existsSync(zipPath)) rmSync(zipPath, { force: true });

  const tarCmd = `tar -caf "${zipPath}" -C "${stagingParent}" "statement-collector-core"`;
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
    rootFolder: 'statement-collector-core',
  };
}

if (process.argv[1] && process.argv[1].endsWith('package-plugin.mjs')) {
  const result = packagePlugin();
  console.log(`Packaged Plugin: ${result.name} (${result.sizeBytes} bytes, SHA-256: ${result.sha256})`);
}
