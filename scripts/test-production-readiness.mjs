const defaultSiteUrl = 'https://mystatement.store';

export async function testProductionReadiness(options = {}) {
  const siteUrl = options.siteUrl ?? process.env.SITE_URL ?? defaultSiteUrl;

  const routes = [
    { path: '/', name: 'Homepage', allowedStatuses: [200] },
    { path: '/shop/', name: 'Shop Catalog', allowedStatuses: [200] },
    { path: '/archive/', name: 'Archive', allowedStatuses: [200, 404] },
    { path: '/cart/', name: 'Cart / Bag', allowedStatuses: [200] },
    { path: '/checkout/', name: 'Checkout', allowedStatuses: [200, 302] },
    { path: '/my-account/', name: 'My Account', allowedStatuses: [200] },
  ];

  const results = {
    siteUrl,
    routes: [],
    passedCount: 0,
    failedCount: 0,
    hasFatalErrors: false,
  };

  for (const r of routes) {
    const routeRes = {
      name: r.name,
      path: r.path,
      status: null,
      passed: false,
      hasFatalText: false,
      hasQaGatewayIndicator: false,
    };

    try {
      const res = await fetch(new URL(r.path, siteUrl), {
        headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36' },
        redirect: 'manual',
      });
      routeRes.status = res.status;
      const body = await res.text();

      routeRes.hasFatalText = /fatal error|uncaught exception|parse error/i.test(body);
      routeRes.hasQaGatewayIndicator = body.includes('statement_qa_gateway') && !body.includes('class="');

      if (r.allowedStatuses.includes(res.status) && !routeRes.hasFatalText) {
        routeRes.passed = true;
        results.passedCount += 1;
      } else {
        results.failedCount += 1;
      }

      if (routeRes.hasFatalText) {
        results.hasFatalErrors = true;
      }
    } catch (err) {
      routeRes.error = err.message;
      results.failedCount += 1;
    }

    results.routes.push(routeRes);
  }

  return results;
}

if (process.argv[1] && process.argv[1].endsWith('test-production-readiness.mjs')) {
  testProductionReadiness()
    .then((res) => {
      console.log('=== PRODUCTION READINESS STOREFRONT SMOKE TEST (READ-ONLY) ===');
      console.log(`Site URL: ${res.siteUrl}`);
      console.log(`Routes Checked: ${res.routes.length} (Passed: ${res.passedCount}, Failed: ${res.failedCount})`);
      console.log(`Fatal Errors Detected: ${res.hasFatalErrors ? 'YES (FAIL)' : 'NO (PASS)'}`);
      console.log('');
      res.routes.forEach((r) => {
        console.log(`[${r.name}] ${r.path} -> Status ${r.status} (${r.passed ? 'PASS' : 'FAIL'}) | Fatals: ${r.hasFatalText ? 'YES' : 'NO'}`);
      });
    })
    .catch((err) => {
      console.error(`Smoke test error: ${err.message}`);
      process.exit(1);
    });
}
