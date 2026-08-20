import { readdirSync } from 'node:fs';
import { resolve, join } from 'node:path';
import { execSync } from 'node:child_process';

const root = resolve(import.meta.dirname, '..');
const phpExe = join(root, '.local-tools/php/php.exe');
const phpTestsDir = join(root, 'tests/php');

const files = readdirSync(phpTestsDir).filter(f => f.endsWith('.php'));
console.log(`Running ${files.length} PHP contract tests with ${phpExe}...\n`);

let passed = 0;
let failed = 0;

for (const file of files) {
	const filePath = join(phpTestsDir, file);
	try {
		const out = execSync(`"${phpExe}" "${filePath}"`, { encoding: 'utf8' });
		console.log(`PASS: ${file}`);
		if (out.trim()) {
			console.log(`  ${out.trim().replace(/\n/g, '\n  ')}`);
		}
		passed++;
	} catch (err) {
		console.error(`FAIL: ${file}`);
		console.error(err.stdout || err.message);
		failed++;
	}
}

console.log(`\nResults: ${passed} passed, ${failed} failed (${files.length} total).`);
if (failed > 0) {
	process.exit(1);
}
