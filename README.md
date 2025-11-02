# Hide by Countries

[![TYPO3](https://img.shields.io/badge/TYPO3-12%2B-orange?logo=typo3)](https://typo3.org)
[![License](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Build](https://img.shields.io/github/actions/workflow/status/oussemaharrbi/hidebycountries/tests.yml?branch=main)](https://github.com/oussemaharrbi/hidebycountries/actions)
[![Packagist](https://img.shields.io/packagist/v/oussema/hidebycountries.svg)](https://packagist.org/packages/oussema/hidebycountries)

> A TYPO3 extension that allows you to **show or hide content elements based on the visitor’s country** — using GeoIP detection.

---

## 🚀 Features

- 🌍 Hide or show Content Elements based on country
- ⚙️ GeoIP lookup with pluggable APIs (e.g. Aether, IP-API, etc.)
- 💾 Caching support for country lookups
- 🍪 Country stored in secure cookie for performance
- 🧩 Middleware-based implementation (PSR-15)
- 🔌 Optional backend indicator in Page module preview (can be disabled)
- ✅ Fully compatible with TYPO3 v12 & v13

---

## 🧰 Installation

### Via Composer
```bash
composer require oussema/hidebycountries

---

## ⚙️ Configuration
