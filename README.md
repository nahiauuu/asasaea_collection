# ASASAEA COLLECTION

**Live site → [formacio.bq.ub.edu/~u321815](https://formacio.bq.ub.edu/~u321815/)**

A quite personal project: a music-related interactive card collection webpage; with pack-opening mechanics and an interactive album. In there, you can also find some other projects made by myself related to database management and web development

---

## What's inside

### 🎴 Card Collection (`index.html`)
A self-contained single-file web app where you collect music cards — one per song or artist — organised into four album categories:

| Category | Colour |
|---|---|
| Rock / Hard Rock / Punk / Alt-Rock | Crimson |
| Pop & Pop Rock | Blue |
| Indie, Electronics & Experimental | Green |
| Urban, Rap & Reggaetón | Amber |

**Features:**
- **Pack opening** — swipe to tear open a pack and reveal 3 random cards with a flip animation
- **Album browser** — tab-based grid showing found and missing cards; click any found card to see its detail
- **Favourites** — heart-button per card, persisted in `localStorage`
- **Friend codes** — redeem a card code shared by a friend to add it to your collection
- **Projects page** — showcases two university database/bioinformatics exercises, plus a FlowCyto predictor tool
- **Contact footer** — inline email form that opens a `mailto:` draft

### 🧬 ClustalOmega Alignment App (`ClustalOmegaAPP.html` + `clustal_api.php`)
A bioinformatics tool for multiple sequence alignment, built as a university exercise (DBW, Exercise 1).

- Fetch protein sequences directly from **UniProt** or **PDB** by accession ID, or paste raw FASTA
- Runs alignment via **ClustalOmega** — locally if installed on the server, or falls back automatically to the **EBI public API**
- Output format options: Clustal, FASTA, MSF, PHYLIP, SELEX, Stockholm, Vienna
- Colour-coded alignment viewer with conservation scoring

---

## Files

| File | Description |
|---|---|
| `index.html` | Main site — card collection, album, packs, projects, contact |
| `ClustalOmegaAPP.html` | Standalone ClustalOmega alignment frontend |
| `clustal_api.php` | PHP backend — fetches sequences and runs/proxies alignment jobs |

---

## Running locally

The collection site (`index.html`) is fully self-contained — just open it in a browser, no server needed.

The ClustalOmega app requires a PHP server with either `clustalo` installed or outbound HTTP access to the EBI API:

```bash
# Install ClustalOmega (Debian/Ubuntu)
sudo apt-get install -y clustalo

# Serve locally
php -S localhost:8000
```

Then visit `http://localhost:8000/ClustalOmegaAPP.html`.

---

## Tech

Built with vanilla HTML, CSS, and JavaScript — no frameworks, no build step. Fonts via Google Fonts (Playfair Display, Space Mono, Nunito, Bebas Neue). Backend in PHP with cURL.

---

*Made by Nahia — Barcelona*
