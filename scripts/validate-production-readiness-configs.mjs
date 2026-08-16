import { readFileSync, existsSync } from 'node:fs';
import { resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');

export function validateIntakeDoc(filename, requiredMarkers = []) {
  const filePath = resolve(root, 'docs', filename);
  if (!existsSync(filePath)) {
    return { file: filename, exists: false, ready: false, missingMarkers: [], unresolvedPlaceholders: 0 };
  }

  const content = readFileSync(filePath, 'utf8');
  const tbdMatches = (content.match(/\[TBD[^\]]*\]/g) || []).length;
  const reqMatches = (content.match(/BUSINESS_INPUT_REQUIRED/g) || []).length;
  const unresolvedPlaceholders = tbdMatches + reqMatches;

  const missingMarkers = requiredMarkers.filter((marker) => !content.includes(marker));

  return {
    file: filename,
    exists: true,
    ready: unresolvedPlaceholders === 0 && missingMarkers.length === 0,
    unresolvedPlaceholders,
    missingMarkers,
  };
}

export function validateAllProductionPacks() {
  const packs = [
    {
      file: 'drop-001-production-input.md',
      markers: ['Drop Configuration', 'Product Pieces Specification', 'Scarcity & Commerce Verification'],
    },
    {
      file: 'drop-001-media-map.md',
      markers: ['Overview & Aspect Ratio Invariants', 'Storefront Media Role Mapping', '4:5'],
    },
    {
      file: 'legal-content-input.md',
      markers: ['Legal Entity & Corporate Information', 'Refund & Returns Policy', 'Terms of Service', 'Privacy Policy'],
    },
    {
      file: 'shipping-configuration-input.md',
      markers: ['Australia Domestic Shipping', 'Australia Post', 'WooCommerce Configuration Checklist'],
    },
    {
      file: 'payment-configuration-input.md',
      markers: ['WooPayments / Stripe Merchant Details', 'Settlement Currency', 'AUD', 'Gateway Activation Protocol'],
    },
    {
      file: 'seo-production-input.md',
      markers: ['Global Brand & Site Metadata', 'Search Engine Indexing Protocol'],
    },
  ];

  const results = packs.map((p) => validateIntakeDoc(p.file, p.markers));
  const allReady = results.every((r) => r.ready);
  const totalPlaceholders = results.reduce((sum, r) => sum + r.unresolvedPlaceholders, 0);

  return {
    allReady,
    totalPlaceholders,
    packs: results,
  };
}

if (process.argv[1] && process.argv[1].endsWith('validate-production-readiness-configs.mjs')) {
  const summary = validateAllProductionPacks();
  console.log('=== PRODUCTION INTAKE PACKS AUDIT ===');
  console.log(`Overall Readiness for Production Import: ${summary.allReady ? 'READY (ALL INPUTS COMPLETE)' : 'OPERATOR_INPUT_REQUIRED'}`);
  console.log(`Total Unresolved Commercial Placeholders: ${summary.totalPlaceholders}`);
  console.log('');

  summary.packs.forEach((p) => {
    const status = p.ready ? 'COMPLETE (READY)' : `AWAITING INPUT (${p.unresolvedPlaceholders} placeholders remaining)`;
    console.log(`- docs/${p.file} -> ${status}`);
    if (p.missingMarkers.length > 0) {
      console.log(`    Missing required sections: ${p.missingMarkers.join(', ')}`);
    }
  });
}
