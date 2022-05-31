Mugo i18n extension for eZ Publish
==================================

This extension helps you to create language translations for your eZ Publish extensions.

How to use this extension:

1) In your extension, make sure to use the i18n operator in your templates and the 'ezpI18n::tr' method in your PHP files.

2) In your extension, create a translation folder and subfolders for each translation you would like to create. Here is an
example strucutre

* myextension
    * translations
        * ger-DE
        * fre-FR

2.1) Re-generate eZ Publish's autoload array to make the extensions classes available.

php bin/php/ezpgenerateautoloads.php -e -p 

3) Use following script to extract all translation strings from 'myextension':

php extension/mugo_i18n/scripts/create_translation_file.php -t myextension > extension/myextension/translations/ger-DE/translation.ts

As you can see the script takes the extension name as a parameter. We also redirect the output of the script into a file that we place into the German translation folder. If you want to create translation files for multiple languages you would need to rerun the script for other translation folders.

4) Make sure that the translation files a writable for the web server.

5) In the admin interface, go to "Setup", "Mugo i18n". Select your extension and the target language.

6) Start translating the strings.

Create a CSV export of a translation file:

To export a translation file as a CSV, build a URL like this
mugo_i18n/csv/&lt;extension_name&gt;/&lt;locale&gt;

In short, the "csv" module converts a .ts file by looking at the path: &lt;extension_name&gt;/translations/&lt;locale&gt;/translation.ts

So for the base translation export, you would load a URL like this:
mugo_i18n/csv/mugoqueue/untranslated


Advanced use example
--------------------
Creating:
- a new translations file for the demoextension (-t)
- taking an existing translations file for the extension into account (-e)
- extending the .tpl regex patterns (-x) used to find strings marked by a custom translation operator (lcb18n)
- and extending the .php regex patterns (-X) used to find strings marked by a custom translation method (LCB18n::tr)

php extension/mugo_i18n/scripts/create_translation_file.php \
-t demoextension \
-e extension/demoextension/translations/fre-CA/translation.ts \
-x $'#["]([^"]+)["]\|lcb18n\([ |]*[\\\'|"](.*?)[\\\'|"][ |]*[,|\)]#' \
-x $'#[\\\']([^\\\']+)[\\\']\|lcb18n\([ |]*[\\\'|"](.*?)[\\\'|"][ |]*[,|\)]#' \
-X $'#(?:LCB18n::tr|lcb18n)\( *[\\\'|"](.*?)[\\\'|"] *, *[\\\'|"](.*?)[\\\'|"] *[,|\)]#is' > extension/demoextension/translations/fre-CA/translation.new

See php extension/mugo_i18n/scripts/create_translation_file.php -h for all available flags