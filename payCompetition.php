<?php
include_once "GunnarCore.php";
session_start();

/*
//////////////////////////////////////////////////////////////////////////////////////////
This script may help convert an old DIBS payment window implementation into the new schema.
Or help make new implementations follow the new schema.
Copyright (C) Lars Wichmann Hansen / Kaka Consult (post (a) kaka-consult.dk)
Version 1.0b (Oct 2005)

This script is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This script is distributed in the hope that it may be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
//////////////////////////////////////////////////////////////////////////////////////////
*/

/*
//////////////////////////////////////////////////////////////////////////////////////////
Background:
Due to security reasons the previous method for implementing the DIBS payment window may cause trouble.
DIBS has therefore relased a new way (schema) to implement the DIBS payement window.
This new method involves opening the popup-window on one of the shops own pages. 
Then submit the payment form to DIBS from within the popup-window rather than from the parent window (as previously).
The answer from DIBS is then also recieved in the popup-window (previously in the parent window)
In other words, two intermediate steps that take place in the popup-window are now required. 
For existing implementations (or new ones) that follow the old implementation method this may be a bit of a hassle. 
The following PHP script automatically makes those extra intermediate steps without the user having to do anything.

Usage:
This script assumes that you are familiar with the DIBS terminology and how to implement the payment window.

The following customisations are required in this script:
   - Input your original accepturl (without parameters) in the variable $AcceptURL
   - Input your original cancelurl (without parameters) in the variable $CancelURL
   - Input the name of your parent window in the variable $ParentWindow

The following customisations are required in your (orginial) form-script:
   - You have to make sure, that the javascript that opens the popup window also names the parent window.
     e.g. add the following javascript prior to your popup opener script.
        window.name = 'ThisIsTheNameOfMyParentWindow';
   - Alter the action in your form, so it points to this script rather than https://payment.architrade.com/payemnt/start.pml.
   - Add the following fields to your form
     <input type="hidden" name="popaction" value="PopUp">
   - Alter the accepturl to:
     <input type="hidden" name="accepturl" value="http://www.yoursite.com/ThisScript?popresult=accept&popaction=PopDown">
     If you require other parameters to be sent to your original accepturl on return from DIBS, please add these to the above URL.
   - Alter the cancelurl to:
     <input type="hidden" name="cancelurl" value="http://www.yoursite.com/ThisScript?popresult=cancel&popaction=PopDown">
     If you require other parameters to be sent to your original cancelurl on return from DIBS, please add these to the above URL.

Please note the following: 
If you are using a callbackurl which relies on session variables this script-workaround will not work.

//////////////////////////////////////////////////////////////////////////////////////////
*/

//////////////////////////////////////////////////////////////////////////////////////////
//Define site-specific variables here
$AcceptURL = "enterCompetition.php"; //Your original accepturl without parameters
$CancelURL = "enterCompetition.php"; //Your original cancelurl without parameters
$ParentWindow = "enterCompetition"; //The name the window that opened the popup
//////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////
//This is the main script
//On the way to DIBS payment window
if ( $_REQUEST['popaction'] == "PopUp" ) {
  PopUp();
}

//On the way back from DIBS payment window
if ( $_REQUEST['popaction'] == "PopDown") {
  if ( $_REQUEST['popresult'] == "Accept" ) {
    PopDown($AcceptURL,$ParentWindow);
  }
  if ( $_REQUEST['popresult'] == "Cancel" ) {
    PopDown($CancelURL,$ParentWindow);
  }
}
//////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////
//A function that acts as an intermediate step in opening the DIBS payment window.
function PopUp() {
  printf ("<html>");
  printf ("<body onload=\"document.DIBSForm.submit();\" >");
  printf ("<form method=\"post\" name=\"DIBSForm\" action=\"https://payment.architrade.com/payment/start.pml\">");
  while (list($key, $val) = each($_REQUEST)) {
    printf ("<input type=\"hidden\" name=\"%s\" value=\"%s\">",$key,$val);
  }
  printf ("</form>");
  printf ("</body></html>");
  ///////
}
//////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////
//A function that acts as an intermediate step in comming back from the DIBS payment window.
function PopDown($URL,$Window) {
  printf ("<html>");
  printf ("<body onload=\"document.ReturnFromDIBSForm.submit(); setTimeout('self.close()',250);\" >");
  printf ("<form method=\"post\" name=\"ReturnFromDIBSForm\" action=\"%s\" target=\"%s\" >",$URL,$Window);
  while (list($key, $val) = each($_REQUEST)) {
    printf ("<input type=\"hidden\" name=\"%s\" value=\"%s\">",$key,$val);
  }
  printf ("</form>");
  printf ("</body>");
  printf ("</html>");
  //////
}
//////////////////////////////////////////////////////////////////////////////////////////


?>