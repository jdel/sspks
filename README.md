[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jdel/sspks/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jdel/sspks/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jdel/sspks/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jdel/sspks/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/jdel/sspks/badges/build.png?b=master)](https://scrutinizer-ci.com/g/jdel/sspks/build-status/master)
[![Build Status](https://travis-ci.org/jdel/sspks.svg?branch=master)](https://travis-ci.org/jdel/sspks)

[![Open in Gitpod](https://gitpod.io/button/open-in-gitpod.svg)](https://gitpod.io/#https://github.com/jdel/sspks)

Simple SPK Server
=================

A very simple Synology Package Server, reverse engineered from
the official Synology package repository and SynoCommunity.

This php script will serve SPKs to a Synology Package Center
while also offering regular HTTP browsing through the available
SPKs.

Installation
============

Please see the [INSTALL](INSTALL.md) file for instructions.

Docker
======

Docker images are built automatically from this repository and are available on [Docker Hub](https://hub.docker.com/r/jdel/sspks/tags/).

In order to use them you will need a working installation of Docker.

Simply run the following command:

```bash
docker run -d --name sspks \
  -v /path/to/your/local/packages:/packages \
  -v /path/to/your/local/cache:/cache \
  -p 9999:8080 \
  -e SSPKS_SITE_NAME="My Packages" \
  -e SSPKS_PACKAGES_DISTRIBUTOR_URL=https://cake.com \
  jdel/sspks
```

More environment variables are available to configure `SSPKS`:


| Variables                      | Description                                                                                             | Values                      |
| -------------------------------- | --------------------------------------------------------------------------------------------------------- | ----------------------------- |
| `SSPKS_SITE_NAME`                | Define the Site Name                                                                                    | Synology Repository         |
| `SSPKS_SITE_THEME`               | Allows the selection of the theme used                                                                  | classic, material (default) |
| `SSPKS_PACKAGES_FILE_MASK`       | Defines the format of the package to be processed.                                                      | *.spk                       |
| `SSPKS_PACKAGES_MAINTAINER`      | Name of the developer                                                                                   | String                      |
| `SSPKS_PACKAGES_MAINTAINER_URL`  | Url of the developer, if available the maintainer is shown as link                                      | URL                         |
| `SSPKS_PACKAGES_DISTRIBUTOR`     | Package Center shows the publisher of the package                                                       | String                      |
| `SSPKS_PACKAGES_DISTRIBUTOR_URL` | If a package is installed and has a "help" webpage, Package Center will show a link to let user open it | URL                         |
| `SSPKS_PACKAGES_SUPPORT_URL`     | Package Center shows a support link to allow users to seek technical support when needed                | URL                         |
| `SSPKS_SITE_REDIRECTINDEX`       | Instead of listing the packages, a direct redirect to the defined URL is set.                           | URL                         |

In the command above, replace `/path/to/your/local/packages` with the directory containing your packages, `/path/to/your/local/cache` with the directory that will hold the cache files and `-p 9999` with the port you intend to serve packages on.

Should you want SSL/TLS (you really should), you need to handle SSL/TLS termination externally, for example with [Traefik](https://traefik.io/) that will automatically fetch [Let's Encrypt](https://letsencrypt.org/) certificates for you.

<br>

Gitpod Environment Variables
==========

The following features can be enabled through environment variables that have been set in your [Gitpod preferences](https://gitpod.io/variables).:
<br />
\* _Please note that storing sensitive data in environment variables is not ultimately secure but should be OK for most development situations._
- ### Sign Git commits with a GPG key
   - `GPG_KEY_ID` (required)
     - The ID of the GPG key you want to use to sign your git commits
   - `GPG_KEY` (required)
     - Base64 encoded private GPG key that corresponds to your `GPG_KEY_ID`
   - `GPG_MATCH_GIT_TO_EMAIL` (optional)
     - Sets your git user.email in `~/.gitconfig` to the value provided
   - `GPG_AUTO_ULTIMATE_TRUST` (optional)
     - If the value is set to `yes` or `YES` then your `GPG_KEY` will be automatically ultimately trusted
- ### Activate an Intelliphense License Key
  - `INTELEPHENSE_LICENSEKEY`
    - Creates `~/intelephense/licence.txt` and will contain the value provided
    - This will activate [Intelliphense](https://intelephense.com/) for you each time the workspace is created or restarted

<br>

Contribute
==========

Feel free to contribute, improve the code or the design by forking
https://github.com/jdel/sspks.git
