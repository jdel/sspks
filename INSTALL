Installation instructions
=====

Clone the repository wherever you want

> cd /home/git/
> git clone https://github.com/jdel/sspks.git

Just symlink the sspks directory into 
any place already served by apache, for example:

> ln -s /home/git/sspks/share/sspks/ /var/www/sspks

How to work it ?
=====

sspks comes with dummy packages for the same of having something
that works out of the box. They WILL NOT WORK in your Synology !
They are located in share/sspks/packages and can all be removed.

sspks is a database-less SPK server. If you are 
developping your own SPKs, you already have all you need:

1. Copy the SPK (let's call it transmission_cedarview_2.77-5.spk)
   in the /var/www/sspks/packages/

2. Extract from that SPK the file called INFO copy it in 
   the same directory and rename it to transmission_cedarview_2.77-5.nfo

3. Pick the icon, convert it to 72x72 (using convert -thumbnail for example),
   copy it in /var/www/sspks/packages/ as well and rename it to
   transmission_cedarview_2.77-5.png

The Package directory should now contain 3 files with the same name
except for the extension.

Now browse to http://yourserver/sspks/ or stick this address in your 
Synology package center and enjoy !

Few more things
=====

You might want to prevent from browsing directly the share/sspks/packages
in your Apache configuration. I will be adding that config files soon
in the /etc directory.

A Synology package should also be available soon.

Integration with spksrc
=====

If you have cloned https://github.com/SynoComunity/spksrc.git, you
can delete the packages directory from sspks and symlink spksrc's 
package directory there. This way, you only need to copy the INFO file
from work-<arch>/INFO and convert the icon from src/<package-name>.png
to spksrc's package directory after you've cross compiled a package.
Now that's called self service.
