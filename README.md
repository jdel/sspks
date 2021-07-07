[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jdel/sspks/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jdel/sspks/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jdel/sspks/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jdel/sspks/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/jdel/sspks/badges/build.png?b=master)](https://scrutinizer-ci.com/g/jdel/sspks/build-status/master)
[![Build Status](https://travis-ci.org/jdel/sspks.svg?branch=master)](https://travis-ci.org/jdel/sspks)

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
===========

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
  jdel/sspks:v1.1.3
```

More environment variables are available to configure `SSPKS`:

  - SSPKS_SITE_NAME
  - SSPKS_SITE_THEME
  - SSPKS_SITE_REDIRECTINDEX
  - SSPKS_PACKAGES_FILE_MASK
  - SSPKS_PACKAGES_MAINTAINER
  - SSPKS_PACKAGES_MAINTAINER_URL
  - SSPKS_PACKAGES_DISTRIBUTOR
  - SSPKS_PACKAGES_DISTRIBUTOR_URL
  - SSPKS_PACKAGES_SUPPORT_URL

In the command above, replace `/path/to/your/local/packages` with the directory containing your packages, `/path/to/your/local/cache` with the directory that will hold the cache files and `-p 9999` with the port you intend to serve packages on.

Should you want SSL/TLS (you really should), you need to handle SSL/TLS termination externally, for example with [Traefik](https://traefik.io/) that will automatically fetch [Let's Encrypt](https://letsencrypt.org/) certificates for you.

Contribute
==========

Feel free to contribute, improve the code or the design by forking
https://github.com/jdel/sspks.git
