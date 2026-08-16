import { parseArgs } from 'node:util';

const defaultSiteUrl = 'https://mystatement.store';

export async function verifyProductionCleanState(options = {}) {
  const siteUrl = options.siteUrl ?? process.env.SITE_URL ?? defaultSiteUrl;

  const results = {
    siteUrl,
    testProductsAbsentInStoreApi: false,
    testDropsAbsentInStoreApi: false,
    qaGatewayAbsentInStoreApi: false,
    cleanState: false,
    findings: [],
  };

  try {
    // 1. Check Store API for TEST- products
    const storeRes = await fetch(`${siteUrl}/wp-json/wc/store/v1/products?per_page=100`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
    });
    if (storeRes.ok) {
      const products = await storeRes.json();
      const testProducts = products.filter((p) =>
        p.slug?.startsWith('test-') ||
        p.name?.startsWith('TEST') ||
        p.sku?.startsWith('TEST-')
      );
      if (testProducts.length === 0) {
        results.testProductsAbsentInStoreApi = true;
      } else {
        results.findings.push(`Found ${testProducts.length} test products in Store API (${testProducts.map((p) => p.slug).join(', ')})`);
      }
    }
  } catch (err) {
    results.findings.push(`Store API check error: ${err.message}`);
  }

  try {
    // 2. Check for test drop term endpoint
    const dropRes = await fetch(`${siteUrl}/drop/test-private-drop-01/`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
      redirect: 'manual',
    });
    if (dropRes.status === 404) {
      results.testDropsAbsentInStoreApi = true;
    } else {
      results.findings.push(`Test drop /drop/test-private-drop-01/ returned HTTP ${dropRes.status} (expected 404 after cleanup)`);
    }
  } catch (err) {
    results.findings.push(`Drop term check error: ${err.message}`);
  }

  try {
    // 3. Check for test product endpoint
    const pdpRes = await fetch(`${siteUrl}/product/test-private-access-jacket/`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
      redirect: 'manual',
    });
    if (pdpRes.status === 404) {
      results.qaGatewayAbsentInStoreApi = true;
    } else {
      results.findings.push(`Test PDP /product/test-private-access-jacket/ returned HTTP ${pdpRes.status} (expected 404)`);
    }
  } catch (err) {
    results.findings.push(`PDP check error: ${err.message}`);
  }

  results.cleanState = results.testProductsAbsentInStoreApi && results.testDropsAbsentInStoreApi && results.qaGatewayAbsentInStoreApi;

  return results;
}

if (process.argv[1] && process.argv[1].endsWith('verify-production-clean-state.mjs')) {
  verifyProductionCleanState()
    .then((res) => {
      console.log('=== PRODUCTION CLEAN STATE VERIFICATION (READ-ONLY) ===');
      console.log(`Site URL: ${res.siteUrl}`);
      console.log(`Test Products Absent in Store API: ${res.testProductsAbsentInStoreApi ? 'YES' : 'NO'}`);
      console.log(`Test Drops Absent: ${res.testDropsAbsentInStoreApi ? 'YES' : 'NO'}`);
      console.log(`Test PDPs Absent: ${res.qaGatewayAbsentInStoreApi ? 'YES' : 'NO'}`);
      console.log(`Overall Clean Production State: ${res.cleanState ? 'CLEAN (READY)' : 'FIXTURES_PRESENT (AWAITING PURGE)'}`);
      if (res.findings.length > 0) {
        console.log('Active Fixture Findings:');
        res.findings.forEach((f) => console.log(`  - ${f}`));
      }
    })
    .catch((err) => {
      console.error(`Verification error: ${err.message}`);
      process.exit(1);
    });
}
