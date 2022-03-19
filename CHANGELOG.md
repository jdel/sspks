# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

<!-- CHANGELOGGER -->

## [1.2.1] - 2022-03-16

### Feature removal (1 change)

- Remove Krypt.co client (props Björn Strausmann)

### Feature change (2 changes)

- Cleanup docker build image in .dockerignore (props Björn Strausmann)
- Changed ENV vendor/bin for composer (props Björn Strausmann)

### New feature (4 changes)

- Added changelogs/unreleased folder structure (props Björn Strausmann)
- Added git commit with gpg signature support (props Björn Strausmann)
- Added automatic activate the intelephense license (props Björn Strausmann)
- Added optimize the detection of https requrest (props Björn Strausmann)


## [1.2.0] - 2022-02-19

### Feature change (2 changes)

- Filtering differently on DSM <7 and >=7 by @picrap in #84
- Better DSM 7 support by @MartinRothschink in #88
- Fix/update alpine php by @jdel in #90
- Migrate to Github Actions by @jdel in #91

## [1.1.6] - 2021-07-19	
### Added

* Support for DSM 7
* Support for locales
* Add GitPod Support [@strausmann](https://github.com/strausmann)
* Update with all new models [@strausmann](https://github.com/strausmann)

### Changed

* Description for Docker ENV added to the `README.md`
* Update with all new models
* Added new x86_64 avoton models (#60) [@eburud](https://github.com/eburud)

## [1.1.3] - 2018-07-25
### Added

* Add missing support_url and auto_upgrade_from fields [@MartinRothschink](https://github.com/MartinRothschink) (PR #55)
* Add more envirinment variables to override configuration (for use with docker)

### Changed

* Upgrade Docker to jdel/alpine:3.8, php7, composer 1.6.5, remove supervisor, change volumes to `/packages` and `/cache`
* Update `README` with docker instructions
* Update synology models (#50) [@4sag](https://github.com/4sag)


## [1.1.2] - 2018-01-19
### Added

* Implement qflags logic + some scrutinizer bits

## [1.1.1] - 2017-08-15
### Added

* Add version info from env variables (#41)

Override from ENV variables:

  - site.name (SSPKS_SITE_NAME)
  - site.theme (SSPKS_SITE_THEME)
  - site.redirectindex (SSPKS_SITE_REDIRECTINDEX)
  
And please Scrutinizer.
### Changed

* Minor update mostly useful for Docker.


## [1.1.0] - 2017-05-15
### Added

* A License
* Automated builds, tests and ci
* Facelift with Material Design

### Changed

* Updated models
* Extracted files are put in a cache/ dir
* Locales support for packages display name and description (#47)
* Locales in pkg name & description JSON result
* Fixed tests
* Scrutinizer didn't like string interpolation
* JsonOutput $langue default to 'enu'
* Docker image

## [1.0.0] - 2017-04-04
### Added

* Make Scrutinizer happy.

## [0.2.0] - 2017-01-16
### Changed

* Fix Dockerfile

## [0.1.0] - 2017-01-16
### Changed

* Fix Dockerfile

## [0.0.1] - 2013-04-12
### Added

* Initial version. Base functionality is there. 
* Requires more work on HTML5/CSS3 design.