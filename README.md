WHMCS - Allopass Gateway
========================

# Introduction

 - Work on WHMCS 6.x

# Installation

## On Allopass

In Allopass Website : 
 - Create a "Virtual Currency" PAge
 - You can fill information as you want, excepted for : 
 	- callback URL : http://`YOURWEBSITE`/modules/gateways/callback/allopass.php
 	- product access URL : http://`YOURWEBSITE`/index.php?m=allopass
 - When complete, find your IDS(ID Site) and IDP(ID Product), you will need them. 
        ( on the main products page on allopass.com)


## Your FTP 

Copy folders :
 - copy the content of "gateways" in "modules/gateways/" of your FTP. 
 - copy the content of "addons" in "modules/addons/" of your FTP. 


## Your Back Office

Back Office Install : 
 - BackOffice -> Setup -> Addon -> Allopass Addon -> Activate
 	- You have nothing else to do with the Addon. 
 	- You can change the template if you want. 
 - BackOffice -> Setup -> Payments -> Payments Gateway -> Allopass -> Activate
 	- Fill information


PS : This module use your currencies. 
To be sure you manage all currencies, add them in your BackOffice. 
If you don't want any problem with currency, avoid to add all country in your allopass document



## Contribute

Feel free to help giving your code improvements.

Any donation is a great help, you can follow this link to donate with paypal: 
[Paypal Donation](https://www.paypal.com/cgi-bin/webscr&cmd=_s-xclick&hosted_button_id=EM33UJFXQFFUN)


