# my_date_views_field
Module for using the MySQL TIMESTAMP database field in Views when connecting to an external database using Views Database Connector. Should work in any condition where an external database is used with Views, but has only been tested with VDC.

For more info: https://www.drupal.org/project/views_database_connector

## Installation & Instructions
You will need to customize the dependencies in the info.yml to match your installation, as well as the views.inc to match your schema. It is recommended to not change the 'my_date' id where it is found as a lot keys in on this value. Or just drop the MyDate class into your application and set your application's views.inc as needed.

Once you have set your environmental variables, place the my_date_views_field folder in Drupal's modules/custom folder, enable the module, and you should be set to add the field in Views when the conditions in the views.inc are met.

## Details
This was a quick one off for a custom application that captured data create and update date with a MySQL timestamp. It was quickly discovered that these values confuse Drupal Views, so a custom Views Field was created. Threads on the module's issue board did have solutions, but they wouldn't work in this particular environment (and you should never edit a 3rd party module's code if you can avoid it).

https://www.drupal.org/project/views_database_connector/issues/2887381

This solution is a lot like the default Date field for Views, except it casts the value from the database as a Drupal DateTime object before displaying. You may wish to change this if it does not meet your particular needs.

This module is shared in the hope that it can assist you in your projects.
