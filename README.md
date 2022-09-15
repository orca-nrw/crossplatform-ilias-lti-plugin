ORCAContent plugin
=============================

readme to be done

If you had installed Version 0.1.0 of ORCA plugin please first deactivate and remove it completely.

Installation
------------

When you download the Plugin as ZIP file from GitHub, please rename the extracted directory to *ORCAContent*
(remove the branch suffix, e.g. -master).

1. Copy the ORCAContent directory to your ILIAS installation at the followin path
(create subdirectories, if neccessary): Customizing/global/plugins/Services/Repository/RepositoryObject
2. Go to Administration > Plugins
3. Choose action "Update" for the ORCAContent plugin
4. Choose action "Activate" for the ORCAContent plugin

Server Configuration Notes
--------------------------

If you want to use the LTI outcome service with PHP-FPM behind an Apache web server, please add a following configuration
to your virtual host or a directory configuration in Apache:

`SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1`

Usage
-----

Version History
===============
