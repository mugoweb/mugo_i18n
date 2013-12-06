Mugo i18n extension for eZ Publish
==================================

This extension helps you to create language translations for your eZ Publish extensions.

How to use this extension:

1) In your extension, make sure to use the i18n operator in your templates and the translation method in your PHP files.

2) In your extension, create a translation folder and subfolders for each translation you would like to create. Here an
example strucutre

myextension
   translations
      ger-DE
      fre-FR

3) Use following script to extract all translation strings from 'myextension':

php extension/mugo_i18n/scripts/create_translation_file.php -t myextension > extension/myextension/translations/ger-DE/translation.ts

As you can see the script takes the extension name as a parameter. We also redirect the output of the script into a file that we place into the German translation folder. If you want to create translation files for multiple languages you would need to rerun the script for other translation folders.

4) Make sure that the translation files a writable for the web server.

5) In the admin interface, go to "Setup", "Mugo i18n". Select your extension and the target language.

6) Start translating the strings.
 
