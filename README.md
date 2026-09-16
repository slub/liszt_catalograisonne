The Digital Franz Liszt Catalogue Raisonné
==========================================

[![TYPO3 12](https://img.shields.io/badge/TYPO3-12-orange.svg)](https://get.typo3.org/version/12)
[![License](https://img.shields.io/github/license/slub/liszt_catalograisonne)](LICENSE)

TYPO3 v12 extension (`liszt_catalograisonne`) that runs the presentation
system for the Franz Liszt Catalogue Raisonné. It renders work and source
listings for site visitors, and provides backend commands to index work and
source documents into Elasticsearch.

The Digital Franz Liszt Catalogue Raisonné is a cornerstone of the research
infrastructure on the pianist and composer Franz Liszt. The research
software and data repository are built in a DFG funded project by the
following institutions:

- [Heidelberg University](https://uni-heidelberg.de)
- [Goethe and Schiller Archive Weimar](https://klassik-stiftung.de)
- [Saxon State Library (SLUB) Dresden](https://slub-dresden.de)

## Data Sources

The catalogue raisonné uses data compiled from different repositories.
Work data are managed via a local installation of
[MerMEId](https://github.com/Edirom/MerMEId). Source data are retrieved from
[RISM](https://rism.info). Bibliographic data are connected using
[liszt_bibliography](https://github.com/slub/liszt_bibliography).

## Tech Stack

- PHP, TYPO3 CMS v12 (Extbase/Fluid), installed as a `typo3-cms-extension` via Composer
- Elasticsearch (indexed via the bundled console commands)
- MerMEId XML-RPC service (`Classes/Services/MermeidXmlRpcService.php`) for retrieving MerMEId documents
- Depends on `slub/liszt-common` (installed from a git repository, branch `7-upgrade-to-typo3-v12`)
- PHPUnit 9 + TYPO3 testing-framework 7 for tests, PHPStan 1 for static analysis
- Dockerized CI via TYPO3's `Build/Scripts/runTests.sh` (used both locally and in GitHub Actions)

## Install

This extension is installed as a Composer dependency of a TYPO3 v12 site,
not run standalone.

```bash
composer require slub/liszt-catalograisonne
```

To work on the extension itself, clone it and install dependencies:

```bash
git clone <this-repo-url>
cd liszt_catalograisonne
composer install
```

`slub/liszt-common` is pulled from a separate git repository
(`https://github.com/dikastes/liszt_common`, see the `repositories` block in
`composer.json`), so `git` must be able to reach that URL during install.

## Configuration

Extension settings are declared in `ext_conf_template.txt` and set via the
TYPO3 backend (Admin Tools > Settings > Extension Configuration >
`liszt_catalograisonne`):

| Setting | Purpose |
|---|---|
| `elasticWorkIndexName` | Elasticsearch index name for works |
| `elasticSourceIndexName` | Elasticsearch index name for sources |
| `mermeidUrl` | Base URL of the MerMEId instance |
| `mermeidPwd` | MerMEId password |
| `mermeidFileFolder` | File folder for MerMEId documents |
| `marcFileFolder` | File folder for RISM MARC documents |

## Usage

The extension registers an Extbase plugin (`LisztCatalograisonne`,
`WorkListing`) for rendering work listings on the frontend, and two TYPO3
console commands for indexing:

```bash
# from the TYPO3 site root; composer.json sets bin-dir to "bin"
bin/typo3 liszt-catalograisonne:index-work <filename>
bin/typo3 liszt-catalograisonne:index-source <filename>
```

## Build / CI

CI runs through Composer scripts defined in `composer.json`, backed by
TYPO3's `Build/Scripts/runTests.sh` in Docker:

```bash
composer ci:install        # install dependencies (Dockerized)
composer ci:php:stan       # PHPStan static analysis
composer ci:tests:unit     # unit tests
composer ci:tests:functional  # functional tests
composer ci                # runs install + php + tests in sequence
```

`.github/workflows/ci.yml` runs these same Composer scripts on every push,
except its `functional-tests` job installs dependencies with
`composer update --no-progress` instead of `composer ci:install`.

## Tests

```bash
composer ci:tests:unit
composer ci:tests:functional
```

Unit tests live in `Tests/Unit/`, functional tests in `Tests/Functional/`
(currently empty aside from a `.gitkeep`). Both suites run via
`Build/Scripts/runTests.sh -s unit -b docker` /
`-s functional -b docker`, using the PHPUnit configs in `Build/phpunit/`.

## License

GNU General Public License. `composer.json` declares
`GPL-2.0-or-later`; the `LICENSE` file in this repository contains the
GPLv3 text.

## Maintainers

If you have any questions or encounter any problems, please do not
hesitate to contact us.

- [Matthias Richter](https://github.com/dikastes)
