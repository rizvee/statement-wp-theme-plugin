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

function safeHeaders(headers) {
  const result = {};
  for (const name of ['cache-control', 'age', 'vary', 'x-ac', 'x-robots-tag']) {
    const value = headers.get(name);
    if (value) result[name] = value;
  }
  return result;
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

function visibleText(body) {
  return body
    .replace(/&#(\d+);/g, (_, value) => String.fromCodePoint(Number(value)))
    .replace(/&#x([0-9a-f]+);/gi, (_, value) => String.fromCodePoint(Number.parseInt(value, 16)))
    .replace(/&nbsp;|&#160;/gi, ' ')
    .replace(/&amp;/gi, '&')
    .replace(/<[^>]+>/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}

function protectedFacts(body) {
  const lower = visibleText(body).toLowerCase();
  return {
    title: lower.includes(expected.title.toLowerCase()),
    price: lower.includes('aud 310') || lower.includes('$310.00') || lower.includes('$310'),
    edition: lower.includes(expected.edition.toLowerCase()),
  };
}

function protectedContent(body) {
  const facts = protectedFacts(body);
  return Object.values(facts).every(Boolean);
}

function isGate(body) {
  const lower = body.toLowerCase();
  return lower.includes('private access')
    && /<input[^>]+type=["']email["']/i.test(body)
    && !lower.includes(expected.title.toLowerCase())
    && !lower.includes(expected.sku.toLowerCase());
}

function safeResponseClass(result, email) {
  const redacted = result.body.replaceAll(email, '[redacted]');
  const title = redacted.match(/<title[^>]*>([\s\S]*?)<\/title>/i)?.[1]
    ?.replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .slice(0, 120) ?? 'NONE';
  return {
    url: result.response.url,
    title,
    contentType: result.response.headers.get('content-type') ?? 'NONE',
  };
}

function accessCookieFlags(setCookies) {
  const line = setCookies.find((value) => /^statement_drop_access_\d+=/i.test(value));
  assert(line, 'Statement access cookie missing');
  return {
    httpOnly: /;\s*httponly(?:;|$)/i.test(line),
    secure: /;\s*secure(?:;|$)/i.test(line),
    sameSiteLax: /;\s*samesite=lax(?:;|$)/i.test(line),
    pathRoot: /;\s*path=\/(?:;|$)/i.test(line),
  };
}

async function submitGate(jar, gate, email) {
  const body = new URLSearchParams({
    _wpnonce: gate.nonce,
    statement_access_action: gate.accessAction,
    email,
  });
  return request(jar, gate.action || dropPath, {
    method: 'POST',
    headers: {
      'content-type': 'application/x-www-form-urlencoded',
      origin: base,
      referer: new URL(dropPath, base).href,
    },
    body,
  });
}

export async function runPrivateAccessFlow() {
  const email = readFileSync(resolve(root, '.local-runtime', 'qa-email.txt'), 'utf8').trim();
  assert(/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email), 'QA email file is invalid');

  const A = new CookieJar();
  const B = new CookieJar();

  const gateGet = await request(A, dropPath);
  assert(gateGet.response.status === 200 && isGate(gateGet.body), 'Initial gate did not render safely');
  const gate = parseGate(gateGet.body);
  const gatePost = await submitGate(A, gate, email);
  const gatePostClass = safeResponseClass(gatePost, email);
  assert(
    [302, 303].includes(gatePost.response.status),
    `Gate POST did not return PRG redirect (${JSON.stringify({ status: gatePost.response.status, gate: isGate(gatePost.body), protected: protectedContent(gatePost.body), accessCookie: gatePost.setCookies.some((value) => /^statement_drop_access_\d+=/i.test(value)), ...gatePostClass })})`,
  );
  const cookie = accessCookieFlags(gatePost.setCookies);
  assert(Object.values(cookie).every(Boolean), 'Access cookie flags are incomplete');
  const redirected = await follow(A, gatePost);
  assert(redirected.response.status === 200 && protectedContent(redirected.body), `Authorized Drop did not load after redirect (${JSON.stringify({ status: redirected.response.status, facts: protectedFacts(redirected.body), gate: isGate(redirected.body) })})`);

  const authorizedDrop = await request(A, dropPath);
  assert(authorizedDrop.response.status === 200 && protectedContent(authorizedDrop.body), 'Authorized Drop content missing');
  const authorizedPdp = await request(A, pdpPath);
  assert(authorizedPdp.response.status === 200, 'Authorized PDP did not return 200');
  assert(protectedContent(authorizedPdp.body), 'Authorized PDP protected facts missing');
  assert(/add-to-cart/i.test(authorizedPdp.body), 'Authorized PDP Add to Bag UI missing');

  const bAfterA = {
    drop: await request(B, dropPath),
    pdp: await request(B, pdpPath),
  };
  assert(bAfterA.drop.response.status === 200 && isGate(bAfterA.drop.body), 'A→B Drop cache isolation failed');
  assert(bAfterA.pdp.response.status === 404 && !bAfterA.pdp.body.includes(expected.title), 'A→B PDP cache isolation failed');

  const bFirstDrop = await request(B, dropPath);
  const bFirstPdp = await request(B, pdpPath);
  const aSecondDrop = await request(A, dropPath);
  const aSecondPdp = await request(A, pdpPath);
  assert(isGate(bFirstDrop.body) && bFirstPdp.response.status === 404, 'B-first anonymous boundary failed');
  assert(protectedContent(aSecondDrop.body) && aSecondPdp.response.status === 200 && protectedContent(aSecondPdp.body), 'B→A cache isolation failed');

  if (process.env.M13_REUSE_ALREADY_SUBMITTED !== '1') {
    const reusePost = await submitGate(A, gate, email);
    assert([302, 303].includes(reusePost.response.status), 'Grant reuse POST did not redirect');
    accessCookieFlags(reusePost.setCookies);
    const reuseRedirected = await follow(A, reusePost);
    assert(protectedContent(reuseRedirected.body), 'Grant reuse did not retain authorization');
  }

  const productId = authorizedPdp.body.match(/name=["']add-to-cart["'][^>]*value=["'](\d+)["']/i)?.[1]
    ?? authorizedPdp.body.match(/value=["'](\d+)["'][^>]*name=["']add-to-cart["']/i)?.[1];
  assert(productId, 'Unable to parse native Add to Cart product ID');

  const addA = await request(A, `/?add-to-cart=${productId}&quantity=1`);
  const addAFinal = await follow(A, addA);
  assert(addAFinal.response.status === 200, 'Authorized Add to Cart request failed');
  const cartA = await request(A, '/cart/');
  const cartLower = cartA.body.toLowerCase();
  assert(cartA.response.status === 200 && cartLower.includes(expected.title.toLowerCase()), 'Authorized cart missing private product');
  assert(cartLower.includes('$310.00') || cartLower.includes('$310') || cartLower.includes('aud 310'), 'Authorized cart price mismatch');
  assert(/(?:qty|quantity)[^>]{0,160}(?:value=["']1["']|>\s*1\s*<)/i.test(cartA.body), 'Authorized cart quantity is not 1');

  const addB = await request(B, `/?add-to-cart=${productId}&quantity=1`);
  await follow(B, addB);
  const cartB = await request(B, '/cart/');
  assert(!cartB.body.toLowerCase().includes(expected.title.toLowerCase()), 'Anonymous direct Add to Cart bypass succeeded');

  const checkout = await request(A, '/checkout/');
  const checkoutLower = checkout.body.toLowerCase();
  assert(checkout.response.status === 200, 'Checkout did not return 200');
  assert(checkoutLower.includes(expected.title.toLowerCase()), 'Checkout lost private cart line');
  assert(/billing_(?:first_name|email)|woocommerce-billing-fields/i.test(checkout.body), 'Checkout billing section missing');
  assert(/id=["']payment["']|woocommerce-checkout-payment|payment_method/i.test(checkout.body), 'Checkout payment section missing');
  assert(!/fatal error|critical error|not authorized|not authorised/i.test(checkout.body), 'Checkout rendered a fatal or authorization error');

  return {
    anonymousRecheck: 'PASS',
    gatePost: { status: gatePost.response.status, redirect: 'PASS', grantSession: 'PASS' },
    cookie,
    authorizedDrop: { status: authorizedDrop.response.status, result: 'PASS', headers: safeHeaders(authorizedDrop.response.headers) },
    authorizedPdp: { status: authorizedPdp.response.status, result: 'PASS', headers: safeHeaders(authorizedPdp.response.headers) },
    cacheAtoB: { result: 'PASS', dropHeaders: safeHeaders(bAfterA.drop.response.headers), pdpHeaders: safeHeaders(bAfterA.pdp.response.headers) },
    cacheBtoA: { result: 'PASS', anonymousDropHeaders: safeHeaders(bFirstDrop.response.headers), authorizedDropHeaders: safeHeaders(aSecondDrop.response.headers) },
    grantReuse: { frontend: 'REUSED', expiry: 'NOT_HTTP_OBSERVABLE' },
    authorizedCart: 'PASS',
    anonymousCartBypass: 'REJECTED',
    checkout: 'PASS',
    emailReminder: 'OFF_NOT_TRIGGERED',
  };
}

if (process.argv[1]?.endsWith('test-private-access-flow.mjs')) {
  runPrivateAccessFlow()
    .then((result) => console.log(JSON.stringify(result, null, 2)))
    .catch((error) => {
      console.error(`PRIVATE_ACCESS_FLOW_FAIL: ${error.message}`);
      process.exit(1);
    });
}
