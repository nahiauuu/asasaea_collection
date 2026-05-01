# ASASAEA COLLECTION

I proudly present to you my most personal project up to date:
Asasaea Collection is a **music-related interactive card collection** webpage; with pack-opening mechanics and an interactive album. In there, you can also find some other projects made by myself related to database management and web development

**Go to the WebPage → [ASASAEA COLLECTION](https://formacio.bq.ub.edu/~u321815/)**

---

## The main thing: A Card Collection (`index.html`)
A self-contained single-file web app where you collect music cards — one per song or artist — organised into four album categories:

| Album Category | Colour |
|---|---|
| Rock, Hard/Alt-Rock & Punk | Red |
| Pop & Pop Rock | Blue |
| Indie, Electronics & Experimental | Green |
| Urban, Hip-Hop, Rap & Reggaetón | Yellow |

**Features:**
- **Pack opening** → swipe to tear open a pack and reveal 3 random cards (with animation and all)
- **Album browser** → tab-based grid showing found and missing cards; you can click any found card to see its details
- **Favourites** → heart-button per card (on the upper-right corner) that you can click and its saved in your local storage; this way you can choose your own favourite cards from the album!!
- **Friend codes** → enter a card-code shared by a friend to add specific cards to your own collection (simulating card exchanges with friends in real life)
- **Contact footer** → quick links to my other internet sites; you can also email me in this space


## Other projects:
Some other unrelated projects are also showcased, regarding database/bioinformatics exercises, webside development & APIs.


### 1. FlowCyto Predictor APP
- Live site → [FlowCyto APP](https://formacio.bq.ub.edu/flowcyto/)
- Original GitHub repository → [flowcyto_predictor](https://github.com/PauVillen/flowcyto_predictor.git)

FlowCyto is a web application developed using Flask (Python) and MySQL, that predicts the most likely blood-cell type based on a list of input genes: it compares the input gene list with a database of known cell type markers and calculates a prediction score for each cell type. Finally, it returns a ranking according to their scores.

### 2. Clinical Trials Database design
A data model schema design for a hospital clinical trials support service. This project was carried out using MySQL.


### 3. 3D-VUV: A Protein Binding-Site Predictor
- Original GitHub repository → [pred_binding-site](https://github.com/nahiauuu/pred_binding-site.git)

A standalone program based on both geometric and machine learning algorithms that analyses the 3D structure of an inputed protein through its PDB file and predicts its potential ligand binding sites.

### 4. ClustalOmega Alignment APP (`ClustalOmegaAPP.html` + `clustal_api.php`)
This is a useful bioinformatics tool for carrying out multiple sequence alignments. It detects various input formats, and the output format can be customizable.

Pipeline:
- Fetch protein sequences directly from **UniProt** or **PDB** by accession ID, or paste raw FASTA file
- Runs alignment via **ClustalOmega** — locally if installed on the server, or falls back automatically to the **EBI public API**
- Output format options: Clustal, FASTA, MSF, PHYLIP, SELEX, Stockholm, Vienna
- Colour-coded alignment viewer with conservation scoring

---

## Files in this repository

| File | Description |
|---|---|
| `index.html` | Main site: card collection, album, packs, projects, contact |
| `FlowCytoPredictor.pdf` | PDF with additional information on the Flowcyto Predictor tool |
| `ClustalOmegaAPP.html` | ClustalOmega alignment frontend |
| `clustal_api.php` | ClustalOmega alignment backend |


---

## Running locally

The Asasaea collection site (`index.html`) is fully self-contained; it needs to just open it in a browser:)

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

Main website and ClustalOmega app's frontend were built using **HTML**, **CSS**, and **JSON**. The fonts are from Google Fonts (Playfair Display, Space Mono, Nunito, Bebas Neue).

Backend of ClustalOmega is in **PHP**.

FlowCyto and 3D-VUV Predictors are built with **PYTHON** and **MySQL**.

---

**FlowCyto and 3D-VUV are collaborative projects, made with friends (they can be found in the corresponding repos of those projects)*

*Main Webpage made by me, Nahia :)*
