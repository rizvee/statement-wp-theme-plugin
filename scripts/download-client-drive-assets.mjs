import fs from 'node:fs';
import path from 'node:path';
import https from 'node:https';

const WHITE_HOODIE_ASSETS = [
  { id: '1Ook2f4UXuLf9qJ7zVJ-piL90rWeoDQih', name: 'white_hoodie_01' },
  { id: '1rUYqDgiY0kvnFyx7HCv0MRVxDkcEalsX', name: 'white_hoodie_02' },
  { id: '1hZgirXEk77q42I2Erg3peZwZ0ChX5Ale', name: 'white_hoodie_03' },
  { id: '1JK7_LQJGKpqcSktRuW-62w7euj5Rpr06', name: 'white_hoodie_04' },
  { id: '1i8QpPVqgeu_py9m7G69d6wluYDigoP-Q', name: 'white_hoodie_05' },
  { id: '17UZ9YHrW5cYF8RGzr3d8sC_-biw0ZcF2', name: 'white_hoodie_06' },
  { id: '1mjGbVi2ZDenW6RwrJBpjq_uw2rz6winf', name: 'white_hoodie_07' },
  { id: '1HvR_uhZKGh4j2AcPA50BeVwZsYdFeLHB', name: 'white_hoodie_08' },
  { id: '1cOlMlX2rqfrIArhPNNLWxcOeH5uXtlh-', name: 'white_hoodie_09' },
  { id: '1fAgsUQiK-bWw_DAkAAgM2C2JDZoe4eFW', name: 'white_hoodie_10' }
];

const DUNE_JACKET_ASSETS = [
  { id: '1s6F-_T5neKTxJC_DXUFPnb6YR-WraoB9', name: 'dune_jacket_01' },
  { id: '12mIWcAI83nrKdjWFgACLsDlO-hfaiRm7', name: 'dune_jacket_02' },
  { id: '15xyhr_TQFCXBX1_EzSNZDaB1Ysd8rgqZ', name: 'dune_jacket_03' },
  { id: '1oAghBLGRFzZNDhBREAZ_A821FUfq4tsE', name: 'dune_jacket_04' },
  { id: '1XjXJTegoC9m-Rz4AwL8vHIIxa_qoT9jF', name: 'dune_jacket_05' },
  { id: '17JnMY4VdoBqK0XwOEdpZ2INpAVJHHEyz', name: 'dune_jacket_06' },
  { id: '1SDiVDvbloJ_teWp8gAooQXStjRv7Nq4p', name: 'dune_jacket_07' },
  { id: '1fV0r_01c_6FHfgElDYg3Srm0xp5yfIeO', name: 'dune_jacket_08' }
];

const OUT_DIR = path.resolve('.local-assets', 'client-drive');
fs.mkdirSync(OUT_DIR, { recursive: true });
fs.mkdirSync(path.join(OUT_DIR, 'white-hoodie'), { recursive: true });
fs.mkdirSync(path.join(OUT_DIR, 'dune-jacket'), { recursive: true });

function downloadFile(url, destPath) {
  return new Promise((resolve, reject) => {
    https.get(url, (res) => {
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        return downloadFile(res.headers.location, destPath).then(resolve).catch(reject);
      }
      if (res.statusCode !== 200) {
        return reject(new Error(`HTTP ${res.statusCode}`));
      }
      const fileStream = fs.createWriteStream(destPath);
      res.pipe(fileStream);
      fileStream.on('finish', () => {
        fileStream.close(() => {
          const stats = fs.statSync(destPath);
          const buf = Buffer.alloc(16);
          const fd = fs.openSync(destPath, 'r');
          fs.readSync(fd, buf, 0, 16, 0);
          fs.closeSync(fd);

          let ext = '.bin';
          if (buf[0] === 0xFF && buf[1] === 0xD8 && buf[2] === 0xFF) ext = '.jpg';
          else if (buf[0] === 0x89 && buf[1] === 0x50 && buf[2] === 0x4E && buf[3] === 0x47) ext = '.png';
          else if (buf[0] === 0x52 && buf[1] === 0x49 && buf[2] === 0x46 && buf[3] === 0x46) ext = '.webp';

          resolve({ size: stats.size, ext, buf });
        });
      });
      fileStream.on('error', reject);
    }).on('error', reject);
  });
}

async function run() {
  console.log('Downloading White Hoodie Assets...');
  for (const item of WHITE_HOODIE_ASSETS) {
    const tempPath = path.join(OUT_DIR, 'white-hoodie', `${item.name}.tmp`);
    try {
      const url = `https://drive.google.com/uc?export=download&id=${item.id}`;
      const res = await downloadFile(url, tempPath);
      const finalPath = path.join(OUT_DIR, 'white-hoodie', `${item.name}${res.ext}`);
      fs.renameSync(tempPath, finalPath);
      console.log(`[PASS] ${item.name} (${item.id}) -> ${path.basename(finalPath)} (${res.size} bytes)`);
    } catch (err) {
      console.error(`[FAIL] ${item.name} (${item.id}): ${err.message}`);
    }
  }

  console.log('\nDownloading Dune Jacket Assets...');
  for (const item of DUNE_JACKET_ASSETS) {
    const tempPath = path.join(OUT_DIR, 'dune-jacket', `${item.name}.tmp`);
    try {
      const url = `https://drive.google.com/uc?export=download&id=${item.id}`;
      const res = await downloadFile(url, tempPath);
      const finalPath = path.join(OUT_DIR, 'dune-jacket', `${item.name}${res.ext}`);
      fs.renameSync(tempPath, finalPath);
      console.log(`[PASS] ${item.name} (${item.id}) -> ${path.basename(finalPath)} (${res.size} bytes)`);
    } catch (err) {
      console.error(`[FAIL] ${item.name} (${item.id}): ${err.message}`);
    }
  }
}

run();
