# Changelog

All notable changes to `akira/laravel-debugger` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

# [1.2.0](/compare/v1.1.0...v1.2.0) (2025-11-22)

### Features

* update Symfony and Rector version constraints in composer.json 5e78c01

## v1.1.0

* fix: enable AddOverrideAttributeToOverriddenMethodsRector in rector.php configuration (5019946)
* chore: update PHP and Laravel version requirements in composer.json and README.md (80b03a1)
* feat: add Pest.php file with strict types declaration (f2b71cc)
* fix: remove no-cache option from Pest test command in tests.yml (126c19c)
* fix: update test command for coverage in composer.json (3e6185f)
* fix: update test command for coverage in composer.json (5abd843)
* fix: remove unused test command for type coverage in composer.json (a00cd8f)
* fix: remove unused test command for architecture in composer.json (0f0f548)
* refactor: apply PHP 8.1 features and improve type hinting across multiple files (3496f1c)
* fix: update rector configuration to use src and tests directories (8c65176)
* fix: add missing newlines at the end of files (e1b0553)
* fix: update pestphp dependencies to support version 4.0 (470280b)
* fix: update PHP version requirement to ^8.2 and adjust dependencies in composer.json and README (f6e9c7c)
* docs: update README to reflect PHP version support and enhance usage examples (c1692bf)
* fix: update PHP version requirement to support 8.0 and add PHP 8.5 to CI matrix (a4fa9b5)
* fix: correct username typo in Discord release webhook configuration (cf56fda)
