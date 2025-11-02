
---

## 📘 2. `Documentation/Index.rst` (for TER / docs.typo3.org)

This is the **ReStructuredText** version suitable for your extension’s documentation directory.

**File:** `Documentation/Index.rst`

```rst
.. include:: ../Includes.txt

====================
Hide by Countries
====================

:Extension key:
   hidebycountries
:Version:
   1.0.0
:Language:
   en
:Author:
   Oussema Harrabi
:License:
   GPL-2.0-or-later
:Rendered:
   |today|

----

A TYPO3 extension that allows integrators and editors to **show or hide content elements based on the visitor’s country**, using a GeoIP middleware and cached lookup.

----

Features
=========

* 🌍 Hide or show content elements by country
* ⚙️ Pluggable GeoIP API service
* 💾 Caching for improved performance
* 🍪 Country stored securely in a cookie
* 🧩 PSR-15 middleware integration
* 🔌 Optional backend Page Module indicator
* ✅ Compatible with TYPO3 v12 and v13

----

Installation
============

Via Composer:

.. code-block:: bash

   composer require oussema/hidebycountries

Or install via the Extension Manager in the TYPO3 backend.

----

Configuration
=============

The extension can be configured under:

**Admin Tools → Settings → Extension Configuration → Hide by Countries**

.. list-table::
   :header-rows: 1

   * - Option
     - Description
     - Default
   * - developemntMode
     - Enable static test IP for debugging
     - 1
   * - publicIpAddressForTesting
     - IP used when development mode is active
     - 234.162.28.227
   * - classNameSpace
     - GeoIP service class name
     - \Oussema\HideByCountries\Utility\Apis\AetherEpiasGeoLocationService
   * - showBackendRestrictionIndicator
     - Show restricted indicator in backend preview
     - 1

----

How It Works
============

1. The middleware detects the visitor’s country by IP and sets a cookie.
2. The country code is registered in the frontend as ``{$TSFE->register['user_country']}``.
3. Content elements with a restricted country list are hidden dynamically.
4. An optional backend indicator shows restricted CEs in the Page module.

----

Screenshots
===========

.. figure:: ../Images/backend-preview.png
   :class: with-shadow

   Example of restricted content element indicator in the Page module.

----

Developers
===========

You can implement your own GeoIP lookup class by extending the base interface and updating the namespace in your configuration.

Example:

.. code-block:: php

   namespace Vendor\Extension\Utility\Apis;

   use Oussema\HideByCountries\Domain\Model\Dto\CountryCode;

   class CustomGeoService
   {
       public function getCountryForIp(string $ip): CountryCode
       {
           // your implementation
       }
   }

----

License
=======

This extension is released under the GNU General Public License, version 2  
Copyright © 2025 Oussema Harrabi

----

Index
=====

* :ref:`Installation`
* :ref:`Configuration`
* :ref:`Developers`
