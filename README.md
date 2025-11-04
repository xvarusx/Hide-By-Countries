# HidebyCountries

[![TYPO3](https://img.shields.io/badge/TYPO3-12%2B-orange?logo=typo3)](https://typo3.org)
[![License](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Build Status](https://img.shields.io/github/actions/workflow/status/xvarusx/Hide-By-Countries/tests.yml?branch=main)](https://github.com/xvarusx/Hide-By-Countries/actions)

> A TYPO3 extension that lets you show or hide content elements based on visitor’s country.

## Features

- Hide or show any content element (CE) depending on visitor’s country code.
- Uses a pluggable GeoIP service API for country detection.
- Cookie-based tracking for visitor’s country for performance.
- Caching support to reduce repeated lookups.
- Optional indicator in backend page preview indicating restricted CEs.
- Compatible with TYPO3 v12 and v13.

## Requirements

- TYPO3 CMS v12 or v13
- PHP 8.1+
- MySQL / MariaDB (or supported database driver)
- Composer to install dependencies (if using Composer mode)

## Installation

#### via composer

```shell
composer require oussema/hidebycountries
```

#### Manual Installation

1. Upload the extension folder to typo3conf/ext/hidebycountries
2. Activate the extension via the Extension Manager
3. Configure via Admin Tools → Settings → Extension Configuration → hidebycountries

---

## Extension Configuration

In the Extension Configuration you will find the following settings:

| Options | Description | Default Values |
| --- | --- | --- |
| developmentMode | Use fixed IP for testing instead of actual client IP | 1 |
| publicIpAddressForTesting | IP address used when development mode is active (its recomand to overwrite this) | 234.162.28.227 |
| classNameSpace | Fully qualified class-name of the GeoIP service implementation | \Oussema\HideByCountries\Utility\Apis\AetherEpiasGeoLocationService |
| showBackendRestrictionIndicator | Show a marker in backend preview for content elements with restrictions | 1 |

---

## How It Works

- A PSR-15 middleware intercepts frontend requests, detects visitor country (by IP or cookie)
- The content element filter logic (configured via EventListener) checks each CE’s tx_hidebycountries field and either renders or hides the CE for the visitor’s country.
- If enabled, the backend preview displays a visual indicator for CEs that have country restrictions.

---

## Developer Guide

#### Using a custom GeoIP service

1- Extend or replace the default service by implementing the GeoLocationApiInterface. Example:

```php
<?php

declare(strict_types=1);

namespace Vendor\Extension\Utility\Apis;

use Oussema\HideByCountries\Utility\Apis\GeoLocationApiInterface;

class CustomGeoService implements GeoLocationApiInterface
{
    public function getCountryForIp(string $ipAddress): string
    {
        // custom logic for fetching country code
    }
}
```

2- In the extension configuration implement ur fully qualified class-name 

![hidebycountries](https://i.postimg.cc/TLFqfyFf/hidebycountries-doc-0.png)

#### Backend preview indicator event

If you’ve enabled the showBackendRestrictionIndicator option, the event listener will hook into the backend module preview and add a visual marker for restricted content elements.

#### Contributing

Contributions are very welcome! Please fork the repository and issue a pull request. Make sure to follow the coding standards and add tests for new features.

#### Testing

Unit and functional tests are located under /Tests. Make sure to have a valid testing database and correct environment variables configured.

---

## Author

[Oussema Harrabi](https://oussemaharrbi.tn/)