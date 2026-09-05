# Geo — countries, regions, cities

The `Geo` module fills three reference tables from an open dataset, so a project can offer
country/region/city pickers without maintaining the data by hand. It has no public routes:
it is a dataset plus two console commands plus admin screens.

Module key: `geo` (switchable off in `config/modules.php`).

---

## Tables

| Table | Columns |
|---|---|
| `geo_countries` | `display`, `rank`, `title`, `iso2`, `iso3`, `phone_code`, `currency_code`, `timezone` |
| `geo_regions` | `display`, `rank`, `geo_country_id`, `title`, `code` |
| `geo_cities` | `display`, `rank`, `geo_country_id`, `geo_region_id`, `title`, `latitude`, `longitude`, `external_id` |

Models: `App\Modules\Geo\Models\{GeoCountry,GeoRegion,GeoCity}`. All three use the shared
`display`/`rank` conventions, so the usual `visible()` scope and admin ordering apply.

---

## Configuration — `config/geo.php`

```php
'countries' => ['GB'],   // which countries to import; the file lists the common codes
'regions'   => true,     // import regions at all
'cities'    => true,     // import cities at all
'sources'   => [ ... ],  // dataset URLs (dr5hn/countries-states-cities-database)
```

**`countries` is the important one.** The dataset covers the whole world; the importer
keeps only the listed ISO2 codes. Ship a project with the countries it actually sells to —
importing everything with `cities => true` writes millions of rows.

---

## Commands

| Command | What it does |
|---|---|
| `php artisan geo:download` | Downloads `countries.xml`, `states.xml` and the gzipped cities dump into `storage/app/public/geo/`, unpacking the archive. Files already present and non-empty are skipped, so re-running is cheap. |
| `php artisan geo:import` | Streams those XML files with `XMLReader` and `updateOrCreate`s the rows, honouring `geo.countries`, `geo.regions` and `geo.cities`. |

Run them in that order. `GeoTablesSeeder` does exactly that (`Artisan::call('geo:download')`
then `geo:import`), and `GeoSeeder` — the module seeder picked up by `DatabaseSeeder` — calls
it, so a full `db:seed` populates geo data on its own. Both commands come from
`app/Modules/Geo/Console/Commands/` and disappear with the module when `geo` is off.

⚠️ `geo:download` fetches from GitHub. It needs outbound network access, and the download
step is the slow one (the cities archive is large) — the HTTP timeout is 600s.

---

## Admin

Three Filament resources under `app/Filament/Resources/Geo/` — `GeoCountries`,
`GeoRegions`, `GeoCities` — registered behind `ModuleGate::isEnabled('geo')` in
`AdminPanelProvider`. They are plain reference tables: search, `display` toggle, ordering.

---

[Back to main readme](../../README.md)
