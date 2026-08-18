const BASE_URL = process.env.STATEMENT_LIVE_URL || 'https://mystatement.store';

const ROUTES_TO_AUDIT = [
  { name: 'HOME', path: '/' },
  { name: 'SHOP', path: '/shop/' },
  { name: 'DROPS_INDEX', path: '/drops/' },
  { name: 'CURRENT_DROP', path: '/drop/drop-001-monogram-study/' },
  { name: 'MONOGRAM_PDP', path: '/product/monogram-jacquard-jacket/' },
  { name: 'PANELLED_HOOD_PDP', path: '/product/panelled-hood-jacket/' },
  { name: 'ARCHIVE', path: '/archive/' },
  { name: 'ABOUT', path: '/about/' },
  { name: 'CONTACT', path: '/contact/' },
  { name: 'JOURNAL', path: '/journal/' },
  { name: 'CART', path: '/cart/' },
  { name: 'CHECKOUT', path: '/checkout/' },
  { name: 'MY_ACCOUNT', path: '/my-account/' },
  { name: 'SEARCH_MONOGRAM', path: '/?s=monogram' },
  { name: 'SEARCH_EMPTY', path: '/?s=nonexistentxyz' },
  { name: 'NOT_FOUND_404', path: '/nonexistent-statement-test-404/' },
];

export async function runStorefrontAudit() {
  console.log(`\n==================================================`);
  console.log(`STATEMENT LIVE STOREFRONT ACCEPTANCE AUDIT`);
  console.log(`Base URL: ${BASE_URL}`);
  console.log(`==================================================\n`);

  const results = [];
  let totalPass = 0;
  let totalDefects = 0;

  for (const route of ROUTES_TO_AUDIT) {
    const url = `${BASE_URL}${route.path}`;
    try {
      const response = await fetch(url, {
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
          'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        },
        redirect: 'follow',
      });

      const status = response.status;
      const html = await response.text();

      // Extract Document Title
      const titleMatch = html.match(/<title>([^<]*)<\/title>/i);
      const title = titleMatch ? titleMatch[1].trim() : 'N/A';

      // Extract H1
      const h1Match = html.match(/<h1[^>]*>([\s\S]*?)<\/h1>/i);
      const h1 = h1Match ? h1Match[1].replace(/<[^>]+>/g, '').trim() : 'N/A';

      // Check for Fatal / PHP Warning Strings
      const hasFatal = /Fatal error|Parse error|Warning:|Notice:|uncaught exception/i.test(html);

      // Check for QA Fixture Leaks (excluding safe test fixtures on private paths if expected)
      const hasFixtureLeak = /\bTEST\s*—|\bTEST-[A-Z0-9-]+\b|\bIntegration\s+Edition\b/.test(html);

      // Check for Raw WooCommerce Bullet / Unstyled markers
      const hasRawWooBullets = /<ul class="products[^"]*">\s*<li[^>]*style="list-style:\s*disc/i.test(html);

      // Defect Classification
      const defects = [];
      if (hasFatal) defects.push('PHP_FATAL_OR_NOTICE');
      if (hasFixtureLeak) defects.push('QA_FIXTURE_LEAK');
      if (route.name !== 'NOT_FOUND_404' && status !== 200 && status !== 301 && status !== 302) {
        defects.push(`HTTP_STATUS_${status}`);
      }
      if (route.name === 'NOT_FOUND_404' && status !== 404) {
        defects.push(`EXPECTED_404_GOT_${status}`);
      }

      // Check for duplicate drop titles on PDP
      if (route.name.includes('PDP')) {
        const provenanceMatch = html.match(/class="statement-product__provenance"[\s\S]*?<\/p>/i);
        if (provenanceMatch && /Drop\s*001[\s\S]*MONOGRAM\s*STUDY/i.test(provenanceMatch[0])) {
          defects.push('DUPLICATE_DROP_METADATA');
        }
      }

      const isPass = defects.length === 0;
      if (isPass) {
        totalPass++;
      } else {
        totalDefects++;
      }

      const result = {
        name: route.name,
        path: route.path,
        url,
        status,
        title,
        h1,
        isPass,
        defects,
        htmlLength: html.length,
      };
      results.push(result);

      console.log(`[${isPass ? 'PASS' : 'DEFECT'}] ${route.name.padEnd(20)} HTTP ${status} | Title: "${title.slice(0, 40)}" | H1: "${h1.slice(0, 30)}" ${defects.length ? '-> ' + defects.join(', ') : ''}`);
    } catch (err) {
      totalDefects++;
      console.error(`[ERROR] ${route.name.padEnd(20)} Failed to fetch ${url}: ${err.message}`);
      results.push({
        name: route.name,
        path: route.path,
        url,
        status: 0,
        title: 'FETCH_FAILED',
        h1: 'FETCH_FAILED',
        isPass: false,
        defects: ['FETCH_FAILED: ' + err.message],
      });
    }
  }

  console.log(`\n--------------------------------------------------`);
  console.log(`AUDIT SUMMARY: ${totalPass} PASSED, ${totalDefects} DEFECTS OUT OF ${ROUTES_TO_AUDIT.length} ROUTES`);
  console.log(`--------------------------------------------------\n`);

  return { results, totalPass, totalDefects };
}

if (process.argv[1]?.endsWith('audit-live-storefront.mjs')) {
  runStorefrontAudit().then(summary => {
    if (summary.totalDefects > 0) {
      console.log('Live storefront audit captured findings for remediation.');
    }
  });
}
