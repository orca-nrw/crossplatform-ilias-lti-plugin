ORCAContent plugin
=============================

If you have installed the version 0.1.0 of the ORCA plugin, please first deactivate and remove it completely.

Installation
------------

A. From ZIP file
If you downloaded the Plugin as ZIP file from GitHub, please rename the extracted directory to *ORCAContent*
(remove the branch suffix, e.g. -main).

1. Copy the ORCAContent directory to your ILIAS installation at the following path
(create subdirectories, if neccessary): `Customizing/global/plugins/Services/Repository/RepositoryObject`
2. Go to Administration > Plugins
3. Choose action "Update" for the ORCAContent plugin
4. Choose action "Activate" for the ORCAContent plugin

B. From github

1. `cd /var/www/html/ilias/Customizing/global/plugins/Services/Repository/RepositoryObject`
2. `git clone git@github.com:orca-nrw/crossplatform-ilias-lti-plugin.git ORCAContent`
3. `cd ORCAContent`
4. `git checkout main`

Update
------
- Just update the branch main from github, either by using `git pull` or by manual replacing files from a newer ZIP.

Server Configuration Notes
--------------------------

If you want to use the LTI outcome service with PHP-FPM behind an Apache web server, please add a following configuration
to your virtual host or a directory configuration in Apache:

`SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1`

Usage
-----

Version History
===============
- 28.11.2022 - pre-release (in the `main` branch)
