# CakePHP PgUtils Plugin

[![CakePHP PgUtils CI](https://github.com/voronoy/cakephp-pgutils/workflows/CakePHP%20PgUtils%20CI/badge.svg)](https://github.com/voronoy/cakephp-pgutils/actions)
[![Coverage Status](https://img.shields.io/codecov/c/gh/voronoy/cakephp-pgutils?style=flat)](https://codecov.io/gh/voronoy/cakephp-pgutils)
[![Latest Version](https://img.shields.io/packagist/v/voronoy/cakephp-pgutils.svg?style=flat)](https://packagist.org/packages/voronoy/cakephp-pgutils)
[![Total Downloads](https://img.shields.io/packagist/dt/voronoy/cakephp-pgutils.svg?style=flat)](https://packagist.org/packages/voronoy/cakephp-pgutils)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat)](https://packagist.org/packages/voronoy/cakephp-pgutils)

CakePHP plugin that extends ORM with PostgreSQL specific features.

## What's included?

- Common PostgreSQL/PostGIS types.
- Upsert behavior to generate bulk upsert queries.
- Materialized View support.

## Installation

Require the plugin through Composer:

```bash
composer require voronoy/cakephp-pgutils
```

Load the plugin:

```bash
bin/cake plugin load Voronoy/PgUtils
```

## Documentation

- [Database Types](docs/types.md)
- [Upsert Behavior](docs/upsert-behavior.md)
- [Materialized View](docs/materialized-view.md)

## License

Licensed under the MIT License.
