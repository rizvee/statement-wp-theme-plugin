import { execSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, readdirSync, rmSync, statSync } from 'node:fs';
import { extname, join, relative, resolve } from 'node:path';
import { lintPhp } from './php-lint.mjs';

const root = resolve(import.meta.dirname, '..');

const forbiddenFilesAndFolders = [
  '.git',
  '.github',
  '.ai',
  'tests',
  'scripts',
  'docs',
  '.local-tools',
  'node_modules',
  'dist',
  'coverage',
  'tmp',
  'logs',
  '.DS_Store',
  'Thumbs.db',
  '.vscode',
  '.idea',
  'php.ini',
  '.env',
  'package-lock.json',
];

const secretPatterns = [
  /-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----/i,
  /\bsk-[A-Za-z0-9_-]{20,}\b/,
  /\bgh[pousr]_[A-Za-z0-9]{20,}\b/,
  /\bAKIA[A-Z0-9]{16}\b/,
  /(?:password|api[_-]?key|client[_-]?secret|access[_-]?token)\s*[:=]\s*["'][^"'\r\n]{8,}["']/i,
];

const scarcityForbidden = [
  /collector[_-]?number/i,
  /serial[_-]?number/i,
  /certificate[_-]?number/i,
  /200 pieces/i,
  /001\/200/i,
  /demand[_-]?based restocking/i,
];

function walkDir(dir, fileList = []) {
  if (!existsSync(dir)) return fileList;
  const items = readdirSync(dir, { withFileTypes: true });
  for (const item of items) {
    const fullPath = join(dir, item.name);
    if (item.isDirectory()) {
      walkDir(fullPath, fileList);
    } else {
      fileList.push(fullPath);
    }
  }
  return fileList;
}

export function verifyPackage(zipPath, targetExpectedVersion = null) {
  if (!existsSync(zipPath)) {
    return { ok: false, errors: [`ZIP file does not exist: ${zipPath}`] };
  }

  let expectedVersion = targetExpectedVersion;
  if (!expectedVersion) {
    const filename = zipPath.replace(/\\/g, '/').split('/').pop();
    const verMatch = filename.match(/-(\d+\.\d+\.\d+(?:-[a-z0-9.]+)?)\.zip$/i);
    if (verMatch) expectedVersion = verMatch[1];
  }

  const extractDir = join(root, 'tmp', `verify-pkg-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`);
  if (existsSync(extractDir)) rmSync(extractDir, { recursive: true, force: true });
  mkdirSync(extractDir, { recursive: true });

  const errors = [];
  let headerVersion = 'UNKNOWN';
  let constantVersion = 'UNKNOWN';

  try {
    const tarCmd = `tar -xf "${zipPath}" -C "${extractDir}"`;
    execSync(tarCmd, { cwd: root, stdio: 'pipe' });

    const topEntries = readdirSync(extractDir, { withFileTypes: true });
    if (1 !== topEntries.length || !topEntries[0].isDirectory()) {
      errors.push(`Package must contain exactly ONE top-level root directory. Found: ${topEntries.map((e) => e.name).join(', ')}`);
      return { ok: false, errors };
    }

    const packageRootName = topEntries[0].name;
    const packageRoot = join(extractDir, packageRootName);

    if ('statement-collector-theme' !== packageRootName && 'statement-collector-core' !== packageRootName && 'statement-client-demo' !== packageRootName) {
      errors.push(`Invalid package root directory name: ${packageRootName}`);
    }

    if ('statement-client-demo' === packageRootName) {
      const requiredDemo = ['statement-client-demo.php', 'src/AdminPage.php', 'src/DemoSeederService.php', 'src/ManifestService.php', 'src/AssetRegistry.php'];
      for (const req of requiredDemo) {
        if (!existsSync(join(packageRoot, req))) {
          errors.push(`Packaged client demo missing required file: ${req}`);
        }
      }

      const demoMainPath = join(packageRoot, 'statement-client-demo.php');
      if (existsSync(demoMainPath)) {
        const mainText = readFileSync(demoMainPath, 'utf8');
        const hMatch = mainText.match(/^[ \t\/*#]*Version:\s*(.+)$/m);
        if (hMatch) headerVersion = hMatch[1].trim();
        else errors.push(`Packaged client demo statement-client-demo.php missing Version header.`);

        const cMatch = mainText.match(/define\(\s*['"]STATEMENT_CLIENT_DEMO_VERSION['"]\s*,\s*['"]([^'"]+)['"]\s*\);/);
        if (cMatch) constantVersion = cMatch[1];
        else errors.push(`Packaged client demo statement-client-demo.php missing STATEMENT_CLIENT_DEMO_VERSION constant.`);
      }

      if (headerVersion !== constantVersion) {
        errors.push(`Packaged client demo version mismatch between statement-client-demo.php header ("${headerVersion}") and STATEMENT_CLIENT_DEMO_VERSION ("${constantVersion}").`);
      }

      if (expectedVersion) {
        if (headerVersion !== expectedVersion) {
          errors.push(`Packaged client demo statement-client-demo.php Version mismatch. Found "${headerVersion}", expected "${expectedVersion}".`);
        }
        if (constantVersion !== expectedVersion) {
          errors.push(`Packaged client demo STATEMENT_CLIENT_DEMO_VERSION constant mismatch. Found "${constantVersion}", expected "${expectedVersion}".`);
        }
      }
    }

    if ('statement-collector-theme' === packageRootName) {
      const requiredTheme = ['style.css', 'functions.php', 'theme.json', 'index.php'];
      for (const req of requiredTheme) {
        if (!existsSync(join(packageRoot, req))) {
          errors.push(`Packaged theme missing required file: ${req}`);
        }
      }

      const stylePath = join(packageRoot, 'style.css');
      if (existsSync(stylePath)) {
        const styleText = readFileSync(stylePath, 'utf8');
        const hMatch = styleText.match(/^[ \t\/*#]*Version:\s*(.+)$/m);
        if (hMatch) headerVersion = hMatch[1].trim();
        else errors.push(`Packaged theme style.css missing Version header.`);
      }

      const fnPath = join(packageRoot, 'functions.php');
      if (existsSync(fnPath)) {
        const fnText = readFileSync(fnPath, 'utf8');
        const cMatch = fnText.match(/define\(\s*['"]STATEMENT_COLLECTOR_THEME_VERSION['"]\s*,\s*['"]([^'"]+)['"]\s*\);/);
        if (cMatch) constantVersion = cMatch[1];
        else errors.push(`Packaged theme functions.php missing STATEMENT_COLLECTOR_THEME_VERSION constant.`);
      }

      if (headerVersion !== constantVersion) {
        errors.push(`Packaged theme version mismatch between style.css header ("${headerVersion}") and STATEMENT_COLLECTOR_THEME_VERSION ("${constantVersion}").`);
      }

      if (expectedVersion) {
        if (headerVersion !== expectedVersion) {
          errors.push(`Packaged theme style.css Version mismatch. Found "${headerVersion}", expected "${expectedVersion}".`);
        }
        if (constantVersion !== expectedVersion) {
          errors.push(`Packaged theme STATEMENT_COLLECTOR_THEME_VERSION constant mismatch. Found "${constantVersion}", expected "${expectedVersion}".`);
        }
      }
    }

    if ('statement-collector-core' === packageRootName) {
      const requiredPlugin = ['statement-collector-core.php', 'src/Plugin.php', 'src/Access/Secrets.php', 'src/Access/SecretVault.php'];
      for (const req of requiredPlugin) {
        if (!existsSync(join(packageRoot, req))) {
          errors.push(`Packaged plugin missing required file: ${req}`);
        }
      }

      const pluginMainPath = join(packageRoot, 'statement-collector-core.php');
      if (existsSync(pluginMainPath)) {
        const mainText = readFileSync(pluginMainPath, 'utf8');
        const hMatch = mainText.match(/^[ \t\/*#]*Version:\s*(.+)$/m);
        if (hMatch) headerVersion = hMatch[1].trim();
        else errors.push(`Packaged plugin statement-collector-core.php missing Version header.`);

        const cMatch = mainText.match(/define\(\s*['"]STATEMENT_COLLECTOR_CORE_VERSION['"]\s*,\s*['"]([^'"]+)['"]\s*\);/);
        if (cMatch) constantVersion = cMatch[1];
        else errors.push(`Packaged plugin statement-collector-core.php missing STATEMENT_COLLECTOR_CORE_VERSION constant.`);
      }

      if (headerVersion !== constantVersion) {
        errors.push(`Packaged plugin version mismatch between statement-collector-core.php header ("${headerVersion}") and STATEMENT_COLLECTOR_CORE_VERSION ("${constantVersion}").`);
      }

      if (expectedVersion) {
        if (headerVersion !== expectedVersion) {
          errors.push(`Packaged plugin statement-collector-core.php Version mismatch. Found "${headerVersion}", expected "${expectedVersion}".`);
        }
        if (constantVersion !== expectedVersion) {
          errors.push(`Packaged plugin STATEMENT_COLLECTOR_CORE_VERSION constant mismatch. Found "${constantVersion}", expected "${expectedVersion}".`);
        }
      }

      // Package ↔ Git Source Parity Check for Core
      try {
        const trackedGitOutput = execSync('git ls-files wp-content/plugins/statement-collector-core', { cwd: root, encoding: 'utf8' });
        const trackedCoreFiles = trackedGitOutput
          .split(/\r?\n/)
          .map(f => f.trim().replace(/^wp-content\/plugins\/statement-collector-core\//, '').replace(/\\/g, '/'))
          .filter(Boolean);

        for (const gitFile of trackedCoreFiles) {
          const expectedPackagedPath = join(packageRoot, gitFile);
          if (!existsSync(expectedPackagedPath)) {
            errors.push(`Package ↔ Git Source Parity failure: Tracked Git file "${gitFile}" missing from packaged plugin ZIP.`);
          }
        }
      } catch (err) {
        errors.push(`Failed to verify Package ↔ Git Source Parity: ${err.message}`);
      }
    }

    const allPackagedFiles = walkDir(packageRoot);

    for (const filePath of allPackagedFiles) {
      const relPath = relative(packageRoot, filePath).replace(/\\/g, '/');

      for (const forbidden of forbiddenFilesAndFolders) {
        if (relPath === forbidden || relPath.startsWith(`${forbidden}/`) || relPath.endsWith(`/${forbidden}`)) {
          errors.push(`Packaged artifact contains forbidden dev file/folder: ${relPath}`);
        }
      }

      if (statSync(filePath).size > 2_000_000) continue;

      const ext = extname(filePath).toLowerCase();
      const textualExts = new Set(['.php', '.css', '.js', '.json', '.md', '.txt', '.xml', '.html']);
      if (!textualExts.has(ext)) continue;

      const content = readFileSync(filePath, 'utf8');

      for (const pattern of secretPatterns) {
        if (pattern.test(content)) {
          errors.push(`Packaged file contains possible secret pattern (${pattern}): ${relPath}`);
        }
      }

      for (const pattern of scarcityForbidden) {
        if (pattern.test(content)) {
          errors.push(`Packaged file violates scarcity model (${pattern}): ${relPath}`);
        }
      }
    }

    const phpLint = lintPhp({ roots: [packageRoot], log: false });
    if (phpLint.available && !phpLint.ok) {
      for (const failure of phpLint.failures) {
        errors.push(`Packaged PHP syntax error in ${relative(packageRoot, failure.file)}: ${failure.output}`);
      }
    }

    return {
      ok: 0 === errors.length,
      errors,
      rootFolder: packageRootName,
      fileCount: allPackagedFiles.length,
      phpCount: allPackagedFiles.filter((f) => f.endsWith('.php')).length,
      headerVersion,
      constantVersion,
    };
  } finally {
    if (existsSync(extractDir)) rmSync(extractDir, { recursive: true, force: true });
  }
}

if (process.argv[1] && process.argv[1].endsWith('verify-package.mjs')) {
  const targetZip = process.argv[2];
  if (!targetZip) {
    console.error('Usage: node scripts/verify-package.mjs <path-to-zip>');
    process.exit(1);
  }
  const result = verifyPackage(resolve(targetZip));
  if (result.ok) {
    console.log(`PASS: Package verification clean for ${targetZip} (${result.fileCount} files, ${result.phpCount} PHP files).`);
  } else {
    console.error(`FAIL: Package verification failed for ${targetZip}:`);
    for (const err of result.errors) console.error(`  - ${err}`);
    process.exit(1);
  }
}
