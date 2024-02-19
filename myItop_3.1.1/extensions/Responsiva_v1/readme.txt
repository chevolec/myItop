sample-add-menu

This extension is a sample extension for iTop 2.0.0 (or newer) that demonstrates
the usage of the iPopupMenuExtension interface.

This sample demonstrates the following:

1) On any list of objects in iTop, this extension adds a new menu item (in the "Other actions..." popup menu)
   that just calls a custom javascript function and does an "alert(...)" with the number of elements in the list

2) On the "details" page of any object in iTop, the extension add an extra menu item "Google this..." that
   open the Google search page (in a new tab/window), with the search text filled with the name of the selected
   itop object

3) On the "details" page of Contacts (i.e. Teams or Persons), two extra menu items are added:
   - A separator line (works only with iTop 2.0.1-beta or newer)
   - A menu items that calls a custom JS function... just to popup an alert(...) with the name of the object

The interesting part of the PHP code is located in the file "main.sample-add-menu.php"
The custom JS functions are located in the file "js/sample.js"

Feel free to modify this sample to adjust it to your needs... and make something useful out of it.
