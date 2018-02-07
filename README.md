Notice! This Modul is only for developer. this is not a modul for just install and use!

# windowconf
Custom Configuration based Advanced Custom Options from Amasty

This Magento 1.9.x.x Modul is for custom Configurations. The Custom Configurations Options are build in Admin in one simple Product with 3rd Party Extension. All Options should be stored in Magento Tables. The Windowconf Modul generates from all of this Options, which can be dependent on each other, huge json file ans stored this in js folder. On each Request Magento send this file, or it generates a new one. It is your choise. In Frontend the JavaScript Code generates from this json html custom Configurator. Now can Users use the Configurator and create a custom product, put it in the cart and buy it.

## Installation
This Modul is only usefull when you have more then 1000 kombinations of options.

## Installation with modman
git clone this repository to your .modman folder.
edit .modman file
deploy this modul
## Backend Settings
You need one simple Product with all of your configurations. The id from this product shoul be insertet in Block/Windowconf.php line 46
You need second product, which you can buy. The id from this product shoul be insertet in template/windowconf/window.phtml line 92
## Edit the tempalte
Edit the Template, create your Design.
