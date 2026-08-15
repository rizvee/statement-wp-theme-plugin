import { randomBytes } from 'node:crypto';
import { existsSync, mkdirSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const targetFile = resolve(root, '.local-runtime', 'private-access-wp-config.php');
const rotateFlag = process.argv.includes('--rotate');

export function generateSecrets(options = {}) {
  const forceRotate = options.rotate ?? rotateFlag;
  const destination = options.targetPath ?? targetFile;

  if (existsSync(destination) && !forceRotate) {
    return {
      status: 'EXISTS',
      path: destination,
      generated: false,
    };
  }

  const identityKey = randomBytes(32).toString('hex');
  const rateLimitKey = randomBytes(32).toString('hex');
  const encKeyV1 = randomBytes(32).toString('hex');

  const encryptionKeysJson = JSON.stringify({ v1: encKeyV1 });

  const snippet = `<?php
/**
 * Statement Private Access wp-config Secrets
 * Generated locally on ${new Date().toISOString()}
 * DO NOT COMMIT THIS FILE OR PASTE SECRETS INTO CHAT/LOGS.
 */
define( 'STATEMENT_ACCESS_IDENTITY_KEY', '${identityKey}' );
define( 'STATEMENT_ACCESS_RATE_LIMIT_KEY', '${rateLimitKey}' );
define( 'STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION', 'v1' );
define( 'STATEMENT_ACCESS_ENCRYPTION_KEYS', '${encryptionKeysJson}' );
`;

  const dir = dirname(destination);
  if (!existsSync(dir)) {
    mkdirSync(dir, { recursive: true });
  }

  writeFileSync(destination, snippet, { mode: 0o600, encoding: 'utf8' });

  return {
    status: 'GENERATED',
    path: destination,
    generated: true,
  };
}

if (process.argv[1] && process.argv[1].endsWith('generate-private-access-secrets.mjs')) {
  try {
    const res = generateSecrets();
    if (res.status === 'EXISTS') {
      console.log('Private Access secret configuration already exists.');
      console.log(`Path: ${res.path}`);
      console.log('Use --rotate to regenerate.');
    } else {
      console.log('Private Access secret configuration generated.');
      console.log(`Path: ${res.path}`);
    }
    console.log('Secrets were NOT printed.');
    console.log('Secrets were NOT committed.');
  } catch (err) {
    console.error(`FATAL: Secret generation failed: ${err.message}`);
    process.exit(1);
  }
}
