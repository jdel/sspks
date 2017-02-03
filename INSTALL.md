Installation instructions
=========================

Clone the repository wherever you want

```sh
$ cd /home/git/
$ git clone https://github.com/jdel/sspks.git
```

Get Composer (to install dependencies): Please see the
[Composer download instructions](https://getcomposer.org/download/)
on how to do this.

Install all dependencies

```sh
$ ./composer.phar install --no-dev
```

**Note:** With newer *Web Station* versions, you might have also installed
different versions of PHP, likely PHP 5.6 and PHP 7.0. If the command above
gives you errors, try running one of these commands:

(And if you have open_basedir() restrictions enabled, don't forget to add
`/root/.composer` to the list of allowed directories.)

```sh
$ php56 composer.phar install --no-dev
$ php70 composer.phar install --no-dev
```

Now, just symlink the sspks directory into
any place already served by apache, for example:

```sh
$ ln -s /home/git/sspks /var/www/sspks
```

You need PHP with the Phar extension and `allow_url_fopen` enabled.


How to work it ?
================

sspks comes with dummy packages for the sake of having something
that works out of the box. They WILL NOT WORK in your Synology!
They are located in the `packages/` directory and can all be removed.

sspks is a database-less SPK server. If you are developing your own SPKs,
you already have all you need:

1. Copy the SPK (let's call it transmission_cedarview_2.77-5.spk)
   in the `packages/` directory

That's it. Sspks will automatically extract the `INFO` file and the package's
icon.

Additionally, you can also create a 120x120x thumb named
`transmission_cedarview_2.77-5_thumb_120.png` and place screenshots named
`transmission_cedarview_2.77-5_screen_1.png`, 
`transmission_cedarview_2.77-5_screen_2.png` … and so on. These will
appear in the detailed package view in the Package Center.

Now browse to http://yourserver/sspks/ or stick this address in your
Synology package center and enjoy!


Integration with spksrc
=======================

If you have cloned https://github.com/SynoCommunity/spksrc.git, you
can delete the `packages/` directory from sspks and symlink spksrc's 
`package` directory there. This way, you only need to copy the INFO file
from work-<arch>/INFO and convert the icon from src/<package-name>.png
to spksrc's package directory after you've cross compiled a package.
Now that's called self service.
