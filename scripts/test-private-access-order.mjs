import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const base = process.env.SITE_URL ?? 'https://mystatement.store';
const dropPath = '/drop/test-private-drop-01/';
const pdpPath = '/product/test-private-access-jacket/';
const expected = {
  title: 'TEST — Private Access Jacket',
  sku: 'TEST-PD01-PAJ',
  edition: 'Private Integration Edition',
};

function assert(condition, message) {
  if (!condition) throw new Error(message);
}

class CookieJar {
  #cookies = new Map();

  absorb(headers) {
    const lines = typeof headers.getSetCookie === 'function' ? headers.getSetCookie() : [];
    for (const line of lines) {
      const pair = line.split(';', 1)[0];
      const index = pair.indexOf('=');
      if (index < 1) continue;
      const name = pair.slice(0, index).trim();
      const value = pair.slice(index + 1).trim();
      if (value) this.#cookies.set(name, value);
      else this.#cookies.delete(name);
    }
    return lines;
  }

  header() {
    return [...this.#cookies].map(([name, value]) => `${name}=${value}`).join('; ');
  }
}

async function request(jar, path, options = {}) {
  const headers = new Headers(options.headers ?? {});
  headers.set('user-agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/127.0 Safari/537.36');
  headers.set('accept', headers.get('accept') ?? 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
  const cookie = jar.header();
  if (cookie) headers.set('cookie', cookie);
  const response = await fetch(new URL(path, base), { ...options, headers, redirect: 'manual' });
  const setCookies = jar.absorb(response.headers);
  const body = await response.text();
  return { response, body, setCookies };
}

async function follow(jar, first, max = 5) {
  let current = first;
  let redirects = 0;
  while ([301, 302, 303, 307, 308].includes(current.response.status) && redirects < max) {
    const location = current.response.headers.get('location');
    assert(location, 'Redirect omitted Location header');
    current = await request(jar, location);
    redirects += 1;
  }
  return current;
}

function parseGate(body) {
  const forms = [...body.matchAll(/<form\b[^>]*>[\s\S]*?<\/form>/gi)].map((match) => match[0]);
  const form = forms.find((candidate) => /name=["']statement_access_action["']/i.test(candidate));
  assert(form, 'Unable to locate real gate form');
  const nonce = form.match(/name=["']_wpnonce["'][^>]*value=["']([^"']+)["']/i)?.[1]
    ?? form.match(/value=["']([^"']+)["'][^>]*name=["']_wpnonce["']/i)?.[1];
  const action = form.match(/<form[^>]*action=["']([^"']*)["'][^>]*>/i)?.[1] ?? '';
  const accessAction = form.match(/name=["']statement_access_action["'][^>]*value=["']([^"']+)["']/i)?.[1];
  assert(nonce && accessAction, 'Unable to parse real gate form');
  return { nonce, action, accessAction };
}

function parseCheckoutNonce(body) {
  const nonce = body.match(/name=["']woocommerce-process-checkout-nonce["'][^>]*value=["']([^"']+)["']/i)?.[1]
    ?? body.match(/value=["']([^"']+)["'][^>]*name=["']woocommerce-process-checkout-nonce["']/i)?.[1];
  return nonce ?? '';
}

export async function runControlledOrderFlow() {
  const email = readFileSync(resolve(root, '.local-runtime', 'qa-email.txt'), 'utf8').trim();
  assert(/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email), 'QA email file is invalid');

  const jar = new CookieJar();

  // 1. Authorize on Gate
  const gateGet = await request(jar, dropPath);
  const gate = parseGate(gateGet.body);
  const gatePost = await request(jar, gate.action || dropPath, {
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded',
      origin: base,
      referer: new URL(dropPath, base).href,
    },
    body: new URLSearchParams({
      _wpnonce: gate.nonce,
      statement_access_action: gate.accessAction,
      email,
    }),
  });
  await follow(jar, gatePost);

  // 2. Add to Bag
  const pdp = await request(jar, pdpPath);
  const productId = pdp.body.match(/name=["']add-to-cart["'][^>]*value=["'](\d+)["']/i)?.[1]
    ?? pdp.body.match(/value=["'](\d+)["'][^>]*name=["']add-to-cart["']/i)?.[1];
  assert(productId, 'Unable to parse native Add to Cart product ID');

  const add = await request(jar, `/?add-to-cart=${productId}&quantity=1`);
  await follow(jar, add);

  // 3. Open Checkout
  const checkoutGet = await request(jar, '/checkout/');
  assert(checkoutGet.response.status === 200, 'Checkout page failed to load');
  const checkoutNonce = parseCheckoutNonce(checkoutGet.body);

  // 4. Test Checkout Billing Email Mismatch Rejection
  const mismatchParams = new URLSearchParams({
    'billing_first_name': 'QA',
    'billing_last_name': 'Tester',
    'billing_address_1': '100 Test St',
    'billing_city': 'Sydney',
    'billing_state': 'NSW',
    'billing_postcode': '2000',
    'billing_country': 'AU',
    'billing_email': 'mismatch-' + Date.now() + '@example.com',
    'billing_phone': '+61400000000',
    'payment_method': 'statement_qa_gateway',
    'woocommerce-process-checkout-nonce': checkoutNonce,
    '_wpnonce': checkoutNonce,
  });

  const mismatchRes = await request(jar, '/?wc-ajax=checkout', {
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded',
      'x-requested-with': 'XMLHttpRequest',
    },
    body: mismatchParams,
  });

  let mismatchJson = null;
  try {
    mismatchJson = JSON.parse(mismatchRes.body);
  } catch {
    // Non-AJAX checkout fallback
  }

  const mismatchBlocked = mismatchJson
    ? mismatchJson.result === 'failure' && mismatchJson.messages?.includes('billing email address must match')
    : mismatchRes.body.includes('billing email address must match');

  // 5. Submit Valid Controlled QA Order
  const validParams = new URLSearchParams({
    'billing_first_name': 'QA',
    'billing_last_name': 'Tester',
    'billing_address_1': '100 Test St',
    'billing_city': 'Sydney',
    'billing_state': 'NSW',
    'billing_postcode': '2000',
    'billing_country': 'AU',
    'billing_email': email,
    'billing_phone': '+61400000000',
    'payment_method': 'statement_qa_gateway',
    'woocommerce-process-checkout-nonce': checkoutNonce,
    '_wpnonce': checkoutNonce,
  });

  const orderRes = await request(jar, '/?wc-ajax=checkout', {
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded',
      'x-requested-with': 'XMLHttpRequest',
    },
    body: validParams,
  });

  let orderJson = null;
  let orderCreated = false;
  let redirectUrl = '';

  try {
    orderJson = JSON.parse(orderRes.body);
    if (orderJson.result === 'success' && orderJson.redirect) {
      orderCreated = true;
      redirectUrl = orderJson.redirect;
    }
  } catch {
    // Non-AJAX checkout fallback
  }

  return {
    mismatchBlocked: mismatchBlocked ? 'PASS' : 'FAIL',
    orderSubmission: orderCreated ? 'PASS' : 'FAIL',
    orderRedirect: redirectUrl ? 'OBTAINED' : 'NONE',
  };
}

if (process.argv[1]?.endsWith('test-private-access-order.mjs')) {
  runControlledOrderFlow()
    .then((res) => console.log(JSON.stringify(res, null, 2)))
    .catch((err) => {
      console.error(`CONTROLLED_ORDER_FLOW_FAIL: ${err.message}`);
      process.exit(1);
    });
}
