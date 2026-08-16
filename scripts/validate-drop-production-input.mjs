import { readFileSync, existsSync } from 'node:fs';
import { resolve } from 'node:path';

export function validateDropInputData(data) {
  const errors = [];
  const warnings = [];

  if (!data || typeof data !== 'object') {
    return { valid: false, errors: ['Input data must be a valid JSON object.'], warnings: [] };
  }

  // 1. Drop validation
  if (!data.drop || typeof data.drop !== 'object') {
    errors.push('Missing required "drop" configuration object.');
  } else {
    const drop = data.drop;
    if (!drop.title || drop.title.includes('TBD') || drop.title.includes('REQUIRED')) {
      errors.push('Drop title is required and cannot be a TBD placeholder.');
    }
    if (!drop.slug || !/^[a-z0-9-]+$/.test(drop.slug)) {
      errors.push('Drop slug is invalid or missing.');
    }
    if (typeof drop.private_access_duration !== 'number' || drop.private_access_duration <= 0) {
      errors.push('Drop private_access_duration must be a positive integer.');
    }
    if (!['days', 'hours', 'minutes'].includes(drop.duration_unit)) {
      errors.push('Drop duration_unit must be "days", "hours", or "minutes".');
    }
    if (!drop.closes_at_utc || drop.closes_at_utc.includes('TBD')) {
      errors.push('Drop closes_at_utc is required.');
    }
  }

  // 2. Products validation
  if (!Array.isArray(data.products) || data.products.length === 0) {
    errors.push('At least one production product is required in "products" array.');
  } else {
    const seenSkus = new Set();
    const seenSlugs = new Set();

    data.products.forEach((p, idx) => {
      const prefix = `Product [${idx + 1}] (${p.title || 'Untitled'}): `;

      // Invariant check: No public total edition counters or production cap fields allowed
      if (p.production_cap !== undefined || p.total_lifetime_units !== undefined || p.public_cap !== undefined) {
        errors.push(prefix + 'Forbidden production-cap / public-total field detected. Scarcity invariant violation.');
      }

      if (!p.title || p.title.includes('TBD') || p.title.includes('REQUIRED')) {
        errors.push(prefix + 'Title is required and cannot be a TBD placeholder.');
      }
      if (!p.slug || !/^[a-z0-9-]+$/.test(p.slug)) {
        errors.push(prefix + 'Slug is invalid or missing.');
      } else if (seenSlugs.has(p.slug)) {
        errors.push(prefix + `Duplicate slug "${p.slug}".`);
      } else {
        seenSlugs.add(p.slug);
      }

      if (!['simple', 'variable'].includes(p.type)) {
        errors.push(prefix + 'Type must be "simple" or "variable".');
      }

      if (!p.edition_label || p.edition_label.includes('TBD')) {
        errors.push(prefix + 'Edition label is required.');
      }

      if (p.release_state !== 'PRIVATE_ACCESS' && p.release_state !== 'UPCOMING' && p.release_state !== 'LIVE') {
        errors.push(prefix + 'Release state must be "PRIVATE_ACCESS", "UPCOMING", or "LIVE".');
      }

      if (typeof p.regular_price_aud !== 'number' || p.regular_price_aud <= 0) {
        errors.push(prefix + 'Regular price in AUD must be a positive number.');
      }

      if (!p.sku || p.sku.includes('TBD')) {
        errors.push(prefix + 'SKU is required.');
      } else if (seenSkus.has(p.sku)) {
        errors.push(prefix + `Duplicate SKU "${p.sku}".`);
      } else {
        seenSkus.add(p.sku);
      }

      if (!p.featured_image || p.featured_image.includes('TBD')) {
        errors.push(prefix + 'Featured image asset filename is required.');
      }

      if (!p.seo_title || p.seo_title.includes('TBD')) {
        errors.push(prefix + 'SEO title is required.');
      }
      if (!p.seo_description || p.seo_description.includes('TBD')) {
        errors.push(prefix + 'SEO description is required.');
      }

      // Simple vs Variable checks
      if (p.type === 'simple') {
        if (typeof p.stock_quantity !== 'number' || p.stock_quantity <= 0) {
          errors.push(prefix + 'Simple product requires positive integer "stock_quantity".');
        }
      } else if (p.type === 'variable') {
        if (!Array.isArray(p.variations) || p.variations.length === 0) {
          errors.push(prefix + 'Variable product requires at least one variation.');
        } else {
          p.variations.forEach((v, vIdx) => {
            const vPrefix = `${prefix} Variation [${vIdx + 1}] (${v.value || 'Unnamed'}): `;
            if (!v.attribute || !v.value) {
              errors.push(vPrefix + 'Attribute and value are required.');
            }
            if (!v.sku || v.sku.includes('TBD')) {
              errors.push(vPrefix + 'Variation SKU is required.');
            } else if (seenSkus.has(v.sku)) {
              errors.push(vPrefix + `Duplicate variation SKU "${v.sku}".`);
            } else {
              seenSkus.add(v.sku);
            }
            if (typeof v.stock !== 'number' || v.stock <= 0) {
              errors.push(vPrefix + 'Variation stock must be a positive integer.');
            }
          });
        }
      }
    });
  }

  return {
    valid: errors.length === 0,
    errors,
    warnings,
  };
}

if (process.argv[1] && process.argv[1].endsWith('validate-drop-production-input.mjs')) {
  const filePath = process.argv[2] || resolve(import.meta.dirname, '..', 'config', 'drop-001.example.json');
  if (!existsSync(filePath)) {
    console.error(`Error: File not found at ${filePath}`);
    process.exit(1);
  }

  try {
    const raw = readFileSync(filePath, 'utf8');
    const data = JSON.parse(raw);
    const result = validateDropInputData(data);

    console.log('=== PRODUCTION DROP INPUT VALIDATOR ===');
    console.log(`Validated File: ${filePath}`);
    console.log(`Validation Status: ${result.valid ? 'PASS (READY FOR IMPORT)' : 'FAIL (INPUT CORRECTIONS REQUIRED)'}`);
    if (result.errors.length > 0) {
      console.log('\nValidation Errors:');
      result.errors.forEach((e) => console.log(`  - ${e}`));
    }
    if (result.warnings.length > 0) {
      console.log('\nWarnings:');
      result.warnings.forEach((w) => console.log(`  - ${w}`));
    }

    if (!result.valid) {
      process.exit(1);
    }
  } catch (err) {
    console.error(`Validation parse error: ${err.message}`);
    process.exit(1);
  }
}
