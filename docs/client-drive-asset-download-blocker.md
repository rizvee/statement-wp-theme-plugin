# Google Drive Direct Download Preflight & Asset Ingestion Report

**Date:** 2026-08-18
**Status:** Ingested via local intake repository (`more image video assets/`)

---

## 1. Direct Unauthenticated Download Behavior

Attempted programmatic download of client Google Drive files via `https://drive.google.com/uc?export=download&id=<FILE_ID>`.
Google returned HTTP 303 redirecting to `https://accounts.google.com/v3/signin/` (requiring authenticated Google session cookie for direct export).

### Unresolved Direct Public Download IDs:

**White Hoodie / Patterned Jacket (10 items):**
- `1Ook2f4UXuLf9qJ7zVJ-piL90rWeoDQih`
- `1rUYqDgiY0kvnFyx7HCv0MRVxDkcEalsX`
- `1hZgirXEk77q42I2Erg3peZwZ0ChX5Ale`
- `1JK7_LQJGKpqcSktRuW-62w7euj5Rpr06`
- `1i8QpPVqgeu_py9m7G69d6wluYDigoP-Q`
- `17UZ9YHrW5cYF8RGzr3d8sC_-biw0ZcF2`
- `1mjGbVi2ZDenW6RwrJBpjq_uw2rz6winf`
- `1HvR_uhZKGh4j2AcPA50BeVwZsYdFeLHB`
- `1cOlMlX2rqfrIArhPNNLWxcOeH5uXtlh-`
- `1fAgsUQiK-bWw_DAkAAgM2C2JDZoe4eFW`

**Dune Jacket / Black Jacquard (8 items):**
- `1s6F-_T5neKTxJC_DXUFPnb6YR-WraoB9`
- `12mIWcAI83nrKdjWFgACLsDlO-hfaiRm7`
- `15xyhr_TQFCXBX1_EzSNZDaB1Ysd8rgqZ`
- `1oAghBLGRFzZNDhBREAZ_A821FUfq4tsE`
- `1XjXJTegoC9m-Rz4AwL8vHIIxa_qoT9jF`
- `17JnMY4VdoBqK0XwOEdpZ2INpAVJHHEyz`
- `1SDiVDvbloJ_teWp8gAooQXStjRv7Nq4p`
- `1fV0r_01c_6FHfgElDYg3Srm0xp5yfIeO`

---

## 2. Ingestion Resolution

The exact client Drive contents (8 Dune Jacket assets and 10 White Hoodie assets) were provided locally in `more image video assets/`.
All 18 assets have been copied to `.local-assets/client-drive/`, verified for valid image signatures (JPEG/PNG), and ingested into the production image optimization pipeline.
Zero synthetic or stock garment structures were substituted.
