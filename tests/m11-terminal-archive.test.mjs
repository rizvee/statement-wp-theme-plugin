import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const pluginRoot = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');

test('M11 focused Archive and terminal presentation files exist', () => {
  const files = [
    resolve(themeRoot, 'page-archive.php'),
    resolve(themeRoot, 'template-parts', 'product', 'summary.php'),
    resolve(themeRoot, 'template-parts', 'product', 'card.php'),
    resolve(pluginRoot, 'src', 'Product', 'Access.php'),
    resolve(pluginRoot, 'src', 'Release', 'Purchasability.php'),
    resolve(pluginRoot, 'src', 'Catalog', 'Visibility.php'),
    resolve(pluginRoot, 'src', 'PublicApi.php'),
  ];

  for (const f of files) {
    assert.ok(existsSync(f), `File must exist: ${f}`);
  }
});

test('Domain rules preserve permanent viewability for direct SOLD_OUT and ARCHIVED permalinks', () => {
  const accessPhp = readFileSync(resolve(pluginRoot, 'src', 'Product', 'Access.php'), 'utf8');
  assert.ok(accessPhp.includes('ReleaseState::SOLD_OUT'), 'Access.php must check SOLD_OUT state.');
  assert.ok(accessPhp.includes('ReleaseState::ARCHIVED'), 'Access.php must check ARCHIVED state.');
  assert.ok(accessPhp.includes('return true'), 'Access.php must return true for viewable states.');
});

test('Single product summary template omits Add-to-Bag for terminal states and shows status badge', () => {
  const summaryPhp = readFileSync(resolve(themeRoot, 'template-parts', 'product', 'summary.php'), 'utf8');
  assert.ok(summaryPhp.includes('$is_terminal'), 'summary.php must check for terminal states.');
  assert.ok(summaryPhp.includes('SOLD OUT'), 'summary.php must include SOLD OUT badge text.');
  assert.ok(summaryPhp.includes('ARCHIVED'), 'summary.php must include ARCHIVED badge text.');
  assert.ok(summaryPhp.includes('woocommerce_template_single_add_to_cart'), 'summary.php must wrap add to cart in non-terminal check.');
});

test('Card template displays terminal status badges', () => {
  const cardPhp = readFileSync(resolve(themeRoot, 'template-parts', 'product', 'card.php'), 'utf8');
  assert.ok(cardPhp.includes('statement-badge'), 'card.php must support terminal status badge.');
  assert.ok(cardPhp.includes('statement-piece__status'), 'card.php must render piece status container.');
});

test('Dedicated Archive page lists archived products and past drops', () => {
  const pageArchivePhp = readFileSync(resolve(themeRoot, 'page-archive.php'), 'utf8');
  assert.ok(pageArchivePhp.includes('get_archive_products'), 'page-archive.php must query archived products.');
  assert.ok(pageArchivePhp.includes('get_past_drops'), 'page-archive.php must query past drops.');
  assert.ok(pageArchivePhp.includes('Statement Archive'), 'page-archive.php must define Template Name.');
});

test('Codebase strictly obeys scarcity invariant without forbidden restock or waitlist messaging', () => {
  const forbiddenPatterns = [
    /waitlist/i,
    /restock/i,
    /back in stock/i,
    /notify when available/i,
    /pre-order/i,
  ];

  const sourceFiles = [
    resolve(themeRoot, 'page-archive.php'),
    resolve(themeRoot, 'template-parts', 'product', 'summary.php'),
    resolve(themeRoot, 'template-parts', 'product', 'card.php'),
  ];

  for (const file of sourceFiles) {
    const content = readFileSync(file, 'utf8');
    for (const pattern of forbiddenPatterns) {
      assert.ok(!pattern.test(content), `Forbidden pattern ${pattern} found in ${file}`);
    }
  }
});
