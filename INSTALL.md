Installation instructions
=========================

Clone the repository wherever you want

```sh
$ cd /home/git/
$ git clone https://github.com/mbirth/sspks.git
```

Get Composer to install dependencies ([Details](https://getcomposer.org/download/))

```sh
$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$ php -r "if (hash_file('SHA384', 'composer-setup.php') === '070854512ef404f16bac87071a6db9fd9721da1684cd4589b1196c3faf71b9a2682e2311b36a5079825e155ac7ce150d') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
$ php composer-setup.php
$ php -r "unlink('composer-setup.php');"
```

Add the [Asset plugin](https://github.com/francoispluchino/composer-asset-plugin) to Composer

```sh
$ ./composer.phar global require "fxp/composer-asset-plugin:~1.1"
```

Install all dependencies

```sh
$ ./composer.phar install
```

Now, just symlink the sspks directory into
any place already served by apache, for example:

```sh
$ ln -s /home/git/sspks/share/sspks/ /var/www/sspks
```


How to work it ?
================

sspks comes with dummy packages for the same of having something
that works out of the box. They WILL NOT WORK in your Synology !
They are located in share/sspks/packages and can all be removed.

sspks is a database-less SPK server. If you are 
developping your own SPKs, you already have all you need:

1. Copy the SPK (let's call it transmission_cedarview_2.77-5.spk)
   in the /var/www/sspks/packages/

2. Extract from that SPK (.tar.gz) the file called INFO copy it in
   the same directory and rename it to transmission_cedarview_2.77-5.nfo

3. Pick the icon, convert it to 72x72 (using convert -thumbnail for example),
   copy it in /var/www/sspks/packages/ as well and rename it to
   transmission_cedarview_2.77-5_thumb_72.png

Additionally, you can also create a 120x120x thumb named transmission_cedarview_2.77-5_thumb_120.png
and place screenshots named transmission_cedarview_2.77-5_screen_1.png
transmission_cedarview_2.77-5_screen_2.png ... and so on. These will
appear in the detailed package view in the Package Center.

Now browse to http://yourserver/sspks/ or stick this address in your 
Synology package center and enjoy !


Integration with spksrc
=======================

If you have cloned https://github.com/SynoComunity/spksrc.git, you
can delete the packages directory from sspks and symlink spksrc's 
package directory there. This way, you only need to copy the INFO file
from work-<arch>/INFO and convert the icon from src/<package-name>.png
to spksrc's package directory after you've cross compiled a package.
Now that's called self service.
