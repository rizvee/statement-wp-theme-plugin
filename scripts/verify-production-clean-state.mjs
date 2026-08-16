const defaultSiteUrl = 'https://mystatement.store';

const knownTestSlugs = [
  'test-studio-overshirt',
  'test-monogram-jacket',
  'test-terminal-jacket',
  'test-private-access-jacket',
];

const knownTestSkus = [
  'TEST-LD01-SO',
  'TEST-LD01-MJ',
  'TEST-LD01-TJ',
  'TEST-PD01-PAJ',
  'TEST-TJ01-ARC',
];

export async function verifyProductionCleanState(options = {}) {
  const siteUrl = options.siteUrl ?? process.env.SITE_URL ?? defaultSiteUrl;

  const results = {
    siteUrl,
    testProductsAbsentInStoreApi: false,
    testProductsAbsentInRestApi: false,
    testDropsAbsentInPublicWeb: false,
    testPdpsAbsentInPublicWeb: false,
    qaGatewayAbsentInCheckout: false,
    searchCleanOfTestEntities: false,
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
        knownTestSlugs.includes(p.slug) ||
        p.slug?.startsWith('test-') ||
        p.name?.startsWith('TEST') ||
        knownTestSkus.includes(p.sku) ||
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
    // 2. Check WordPress REST API for TEST- products
    const restRes = await fetch(`${siteUrl}/wp-json/wp/v2/product?per_page=100`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
    });
    if (restRes.ok) {
      const products = await restRes.json();
      const testProducts = Array.isArray(products) ? products.filter((p) =>
        knownTestSlugs.includes(p.slug) ||
        p.slug?.startsWith('test-') ||
        p.title?.rendered?.includes('TEST')
      ) : [];
      if (testProducts.length === 0) {
        results.testProductsAbsentInRestApi = true;
      } else {
        results.findings.push(`Found ${testProducts.length} test products in WP REST API (${testProducts.map((p) => p.slug).join(', ')})`);
      }
    } else if (restRes.status === 404 || restRes.status === 401 || restRes.status === 403) {
      results.testProductsAbsentInRestApi = true;
    }
  } catch (err) {
    results.findings.push(`REST API check error: ${err.message}`);
  }

  try {
    // 3. Check for test drop term endpoint
    const dropRes = await fetch(`${siteUrl}/drop/test-private-drop-01/`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
      redirect: 'manual',
    });
    if (dropRes.status === 404) {
      results.testDropsAbsentInPublicWeb = true;
    } else {
      results.findings.push(`Test drop /drop/test-private-drop-01/ returned HTTP ${dropRes.status} (expected 404 after purge)`);
    }
  } catch (err) {
    results.findings.push(`Drop term check error: ${err.message}`);
  }

  try {
    // 4. Check for test PDP endpoints
    let foundPdp = 0;
    for (const slug of knownTestSlugs) {
      const pdpRes = await fetch(`${siteUrl}/product/${slug}/`, {
        headers: { 'User-Agent': 'Mozilla/5.0' },
        redirect: 'manual',
      });
      if (pdpRes.status !== 404) {
        foundPdp++;
        results.findings.push(`Test PDP /product/${slug}/ returned HTTP ${pdpRes.status} (expected 404 after purge)`);
      }
    }
    if (foundPdp === 0) {
      results.testPdpsAbsentInPublicWeb = true;
    }
  } catch (err) {
    results.findings.push(`PDP check error: ${err.message}`);
  }

  try {
    // 5. Check Checkout for QA Gateway traces
    const checkoutRes = await fetch(`${siteUrl}/checkout/`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
    });
    if (checkoutRes.ok) {
      const body = await checkoutRes.text();
      if (!body.includes('statement_qa_gateway') && !body.includes('TEST ONLY — NO PAYMENT')) {
        results.qaGatewayAbsentInCheckout = true;
      } else {
        results.findings.push('QA Gateway indicator detected on Checkout page');
      }
    } else {
      results.qaGatewayAbsentInCheckout = true;
    }
  } catch (err) {
    results.findings.push(`Checkout check error: ${err.message}`);
  }

  try {
    // 6. Check search endpoint for TEST queries
    const searchRes = await fetch(`${siteUrl}/?s=TEST`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
    });
    if (searchRes.ok) {
      const body = await searchRes.text();
      const hasTestCards = knownTestSlugs.some((slug) => body.includes(`/product/${slug}/`));
      if (!hasTestCards) {
        results.searchCleanOfTestEntities = true;
      } else {
        results.findings.push('Search results loop contains active test product cards');
      }
    }
  } catch (err) {
    results.findings.push(`Search check error: ${err.message}`);
  }

  results.cleanState =
    results.testProductsAbsentInStoreApi &&
    results.testProductsAbsentInRestApi &&
    results.testDropsAbsentInPublicWeb &&
    results.testPdpsAbsentInPublicWeb &&
    results.qaGatewayAbsentInCheckout &&
    results.searchCleanOfTestEntities;

  return results;
}

if (process.argv[1] && process.argv[1].endsWith('verify-production-clean-state.mjs')) {
  verifyProductionCleanState()
    .then((res) => {
      console.log('=== PRODUCTION CLEAN STATE VERIFICATION (READ-ONLY) ===');
      console.log(`Site URL: ${res.siteUrl}`);
      console.log(`Test Products Absent in Store API: ${res.testProductsAbsentInStoreApi ? 'YES' : 'NO'}`);
      console.log(`Test Products Absent in REST API: ${res.testProductsAbsentInRestApi ? 'YES' : 'NO'}`);
      console.log(`Test Drops Absent: ${res.testDropsAbsentInPublicWeb ? 'YES' : 'NO'}`);
      console.log(`Test PDPs Absent: ${res.testPdpsAbsentInPublicWeb ? 'YES' : 'NO'}`);
      console.log(`QA Gateway Absent in Checkout: ${res.qaGatewayAbsentInCheckout ? 'YES' : 'NO'}`);
      console.log(`Search Clean of Test Cards: ${res.searchCleanOfTestEntities ? 'YES' : 'NO'}`);
      console.log(`Overall Clean Production State: ${res.cleanState ? 'CLEAN (READY)' : 'FIXTURES_PRESENT (AWAITING PURGE)'}`);
      if (res.findings.length > 0) {
        console.log('\nActive Fixture Findings (Dry-Run Inventory):');
        res.findings.forEach((f) => console.log(`  - ${f}`));
      }
    })
    .catch((err) => {
      console.error(`Verification error: ${err.message}`);
      process.exit(1);
    });
}
