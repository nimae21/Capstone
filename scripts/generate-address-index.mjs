import { createHash } from 'node:crypto';
import { readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const sourcePath = resolve(root, 'public/data/barangay.json');
const outputPath = resolve(root, 'config/address-data.php');
const source = readFileSync(sourcePath);
const entries = new Map();

let depth = 0;
let inString = false;
let escaped = false;
let objectStart = -1;

for (let index = 0; index < source.length; index += 1) {
    const character = String.fromCharCode(source[index]);

    if (inString) {
        if (escaped) escaped = false;
        else if (character === '\\') escaped = true;
        else if (character === '"') inString = false;
        continue;
    }

    if (character === '"') {
        inString = true;
    } else if (character === '{') {
        if (depth === 0) objectStart = index;
        depth += 1;
    } else if (character === '}') {
        depth -= 1;
        if (depth !== 0) continue;

        const record = JSON.parse(source.subarray(objectStart, index + 1).toString('utf8'));
        const cityCode = String(record.city_code);
        const current = entries.get(cityCode);

        if (current && current.end !== objectStart - 1) {
            throw new Error(`Barangays for city ${cityCode} are not contiguous.`);
        }

        entries.set(cityCode, {
            offset: current?.offset ?? objectStart,
            end: index + 1,
            count: (current?.count ?? 0) + 1,
        });
    }
}

if (depth !== 0 || inString) throw new Error('Barangay JSON is incomplete.');

const lines = [...entries.entries()].map(([cityCode, entry]) => {
    const length = entry.end - entry.offset;
    const decoded = JSON.parse(`[${source.subarray(entry.offset, entry.end).toString('utf8')}]`);
    if (decoded.length !== entry.count || decoded.some((row) => String(row.city_code) !== cityCode)) {
        throw new Error(`Generated range failed verification for city ${cityCode}.`);
    }

    return `        '${cityCode}' => [${entry.offset}, ${length}, ${entry.count}],`;
});

const version = createHash('sha256').update(source).digest('hex').slice(0, 16);
const output = `<?php\n\nreturn [\n    'version' => '${version}',\n    'barangay_file' => 'data/barangay.json',\n    // city_code => [byte offset, byte length, record count]\n    'barangays' => [\n${lines.join('\n')}\n    ],\n];\n`;

writeFileSync(outputPath, output);
console.log(`Indexed ${entries.size} cities in ${outputPath} (dataset ${version}).`);
