import { parseArgs } from 'node:util';

const defaultSiteUrl = 'https://mystatement.store';
const defaultPrivateSlug = 'test-private-access-jacket';

export async function testPrivateAccessApi(options = {}) {
  const siteUrl = options.siteUrl ?? process.env.SITE_URL ?? defaultSiteUrl;
  const privateSlug = options.privateSlug ?? process.env.PRIVATE_SLUG ?? defaultPrivateSlug;

  const results = {
    siteUrl,
    privateSlug,
    restProducts: null,
    storeApiProducts: null,
    privateProductExposedAnonymously: false,
    liveProductsAccessible: false,
  };

  try {
    const restRes = await fetch(`${siteUrl}/wp-json/wp/v2/product?per_page=100`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
    });
    if (restRes.ok) {
      const text = await restRes.text();
      results.restProducts = {
        status: restRes.status,
        containsPrivateSlug: text.includes(privateSlug),
      };
      if (text.includes(privateSlug)) {
        results.privateProductExposedAnonymously = true;
      }
      if (text.includes('studio-overshirt') || text.includes('monogram-jacket')) {
        results.liveProductsAccessible = true;
      }
    }
  } catch (err) {
    results.restProductsError = err.message;
  }

  try {
    const storeApiRes = await fetch(`${siteUrl}/wp-json/wc/store/v1/products?per_page=100`, {
      headers: { 'User-Agent': 'Mozilla/5.0' },
    });
    if (storeApiRes.ok) {
      const text = await storeApiRes.text();
      results.storeApiProducts = {
        status: storeApiRes.status,
        containsPrivateSlug: text.includes(privateSlug),
      };
      if (text.includes(privateSlug)) {
        results.privateProductExposedAnonymously = true;
      }
    }
  } catch (err) {
    results.storeApiProductsError = err.message;
  }

  return results;
}

if (process.argv[1] && process.argv[1].endsWith('test-private-access-api.mjs')) {
  testPrivateAccessApi()
    .then((res) => {
      console.log('=== PRIVATE ACCESS ANONYMOUS API PRIVACY AUDIT ===');
      console.log(`Site URL: ${res.siteUrl}`);
      console.log(`Target Private Slug: ${res.privateSlug}`);
      console.log(`Private Product Exposed Anonymously? ${res.privateProductExposedAnonymously ? 'FAIL (EXPOSED)' : 'PASS (NOT EXPOSED)'}`);
      console.log(`Live Products Accessible? ${res.liveProductsAccessible ? 'YES' : 'NO'}`);
    })
    .catch((err) => {
      console.error(`API test error: ${err.message}`);
      process.exit(1);
    });
}
