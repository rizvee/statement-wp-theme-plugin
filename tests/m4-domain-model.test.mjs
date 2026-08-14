import assert from 'node:assert/strict';
import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { extname, resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const pluginRoot = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const requiredFiles = [
  'src/Drop/Taxonomy.php',
  'src/Product/Admin.php',
  'src/Product/Metadata.php',
  'src/Release/ReleaseState.php',
  'src/Release/Purchasability.php',
];

function read(relativePath) {
  return readFileSync(resolve(pluginRoot, relativePath), 'utf8');
}

function walk(path, output = []) {
  for (const entry of readdirSync(path, { withFileTypes: true })) {
    const child = resolve(path, entry.name);
    if (entry.isDirectory()) walk(child, output);
    else output.push(child);
  }
  return output;
}

test('M4 focused plugin domain files exist and are bootstrapped', () => {
  for (const path of requiredFiles) {
    assert.equal(existsSync(resolve(pluginRoot, path)), true, `missing M4 plugin file: ${path}`);
  }

  const entrypoint = read('statement-collector-core.php');
  const plugin = read('src/Plugin.php');
  for (const path of requiredFiles) {
    assert.match(entrypoint, new RegExp(path.replaceAll('/', '[\\\\/]')));
  }
  assert.match(plugin, /Drop\\Taxonomy::boot\s*\(/);
  assert.match(plugin, /Product\\Admin::boot\s*\(/);
  assert.match(plugin, /Release\\Purchasability::boot\s*\(/);
  assert.match(plugin, /class_exists\(\s*'WooCommerce'\s*\)/);
});

test('statement_drop taxonomy is product-owned, public, REST-visible, and single-control ready', () => {
  const taxonomy = read('src/Drop/Taxonomy.php');

  assert.match(taxonomy, /statement_drop/);
  assert.match(taxonomy, /register_taxonomy\s*\(\s*self::KEY\s*,\s*array\(\s*'product'\s*\)/s);
  for (const setting of ['public', 'hierarchical', 'show_ui', 'show_in_rest', 'show_admin_column', 'show_in_nav_menus']) {
    assert.match(taxonomy, new RegExp(`'${setting}'\\s*=>`), `missing taxonomy setting: ${setting}`);
  }
  assert.match(taxonomy, /'hierarchical'\s*=>\s*false/);
  assert.match(taxonomy, /'meta_box_cb'\s*=>\s*false/);
  assert.match(taxonomy, /'show_in_quick_edit'\s*=>\s*false/);
  assert.match(taxonomy, /'slug'\s*=>\s*'drop'/);
  assert.doesNotMatch(taxonomy, /register_post_type|Drop\s*001|Drop\s*002/i);
});

test('metadata centralizes canonical state and edition keys through WooCommerce CRUD', () => {
  const metadata = read('src/Product/Metadata.php');

  assert.match(metadata, /_statement_release_state/);
  assert.match(metadata, /_statement_edition_label/);
  assert.match(metadata, /get_meta\s*\(/);
  assert.match(metadata, /update_meta_data\s*\(/);
  assert.match(metadata, /delete_meta_data\s*\(/);
  assert.match(metadata, /can_transition\s*\(/);
  assert.match(metadata, /function\s+get_release_owner\s*\(/);
  assert.match(metadata, /get_parent_id\s*\(/);
  assert.match(metadata, /wc_get_product\s*\(/);
  assert.match(metadata, /sanitize_text_field\s*\(/);
  assert.match(metadata, /EDITION_LABEL_MAX_LENGTH\s*=\s*80/);
  assert.equal((metadata.match(/update_meta_data\s*\(\s*self::RELEASE_STATE_KEY/g) ?? []).length, 1);
  assert.doesNotMatch(metadata, /get_post_meta|update_post_meta|add_post_meta|production_cap|edition_total|max_pieces|piece_number|restock/i);
});

test('product admin uses controlled fields and preserves invalid historical input safely', () => {
  const admin = read('src/Product/Admin.php');

  assert.match(admin, /woocommerce_product_options_general_product_data/);
  assert.match(admin, /woocommerce_admin_process_product_object/);
  assert.match(admin, /woocommerce_wp_select\s*\(/);
  assert.match(admin, /woocommerce_wp_text_input\s*\(/);
  assert.match(admin, /statement_collector_drop/);
  assert.match(admin, /ReleaseState::all\s*\(/);
  assert.match(admin, /wp_nonce_field\s*\(/);
  assert.match(admin, /wp_verify_nonce\s*\(/);
  assert.match(admin, /current_user_can\s*\(/);
  assert.match(admin, /wp_is_post_autosave\s*\(/);
  assert.match(admin, /wp_is_post_revision\s*\(/);
  assert.match(admin, /wp_unslash\s*\(/);
  assert.match(admin, /sanitize_text_field\s*\(/);
  assert.match(admin, /absint\s*\(/);
  assert.match(admin, /get_term\s*\(/);
  assert.match(admin, /wp_get_object_terms\s*\(/);
  assert.match(admin, /wp_set_object_terms\s*\(/);
  assert.match(admin, /Metadata::get_release_state\s*\(/);
  assert.match(admin, /ReleaseState::UPCOMING/);
  assert.doesNotMatch(admin, /wp_insert_term|register_post_type|multiple\s*=>\s*true/i);
});

test('release lifecycle and purchasability source protect terminal states without stock mutation', () => {
  const release = read('src/Release/ReleaseState.php');
  const purchasability = read('src/Release/Purchasability.php');
  const source = `${release}\n${purchasability}`;

  for (const state of ['UPCOMING', 'PRIVATE_ACCESS', 'LIVE', 'SOLD_OUT', 'ARCHIVED']) {
    assert.match(release, new RegExp(`const\\s+${state}\\s*=\\s*'${state}'`));
  }
  assert.match(release, /function\s+can_transition\s*\(/);
  assert.match(release, /function\s+is_terminal\s*\(/);
  assert.match(purchasability, /woocommerce_is_purchasable/);
  assert.match(purchasability, /ReleaseState::is_terminal\s*\(/);
  assert.doesNotMatch(source, /set_stock_quantity|set_stock_status|increase_stock|reduce_stock|restock|wp_schedule|Action Scheduler/i);
});

test('theme remains presentation-only and free of M4 domain ownership', () => {
  const themeSource = walk(themeRoot)
    .filter((path) => ['.php', '.js', '.css'].includes(extname(path).toLowerCase()))
    .map((path) => readFileSync(path, 'utf8'))
    .join('\n');

  assert.doesNotMatch(themeSource, /register_taxonomy|_statement_release_state|_statement_edition_label|woocommerce_is_purchasable|ReleaseState::/i);
});

test('M4 plugin runtime contains no later-milestone or forbidden production model', () => {
  const source = walk(pluginRoot)
    .filter((path) => extname(path).toLowerCase() === '.php')
    .map((path) => readFileSync(path, 'utf8'))
    .join('\n');
  const forbidden = [
    /register_rest_route|wp_ajax_|wp_schedule_|as_schedule_/i,
    /CREATE\s+TABLE|\$wpdb/i,
    /magic[_ -]?link|access[_ -]?token|private[_ -]?access[_ -]?(?:handler|enforcement)/i,
    /product[_ -]?card|product[_ -]?gallery|homepage|checkout|certificate/i,
    /archive[_ -]?(?:page|template|scheduler)|reminder[_ -]?email/i,
    /production[_ -]?cap|edition[_ -]?total|max[_ -]?pieces|piece[_ -]?total|\b200\b/i,
    /restock(?:ing)?|replenish(?:ment)?/i,
  ];

  for (const signal of forbidden) assert.doesNotMatch(source, signal);
});
