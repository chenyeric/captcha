<?php

// -----------------------------------------------
// Cryptographp v1.4
// (c) 2006-2007 Sylvain BRISON 
//
// www.cryptographp.com 
// cryptographp@alphpa.com 
//
// Licence CeCILL modifiée
// => Voir fichier Licence_CeCILL_V2-fr.txt)
// -----------------------------------------------


// -------------------------------------
// Configuration du fond du cryptogramme
// -------------------------------------

$cryptwidth  = 140;  // ​​Width of the cryptogram (in pixels)
$cryptheight = 40;   // Height of the cryptogram (in pixels)

$bgR  = 255;         // background color to RGB: Red (0 -> 255)
$bgG  = 255;         // Couleur du fond au format RGB: Green (0->255)
$bgB  = 255;         // Couleur du fond au format RGB: Blue (0->255)

$bgclear = true;     // Transparent background (true / false)
                     // Only valid for PNG

$bgimg = '';                					// The bottom of the cryptogram may be an image

$bgframe = false;    // Add a picture frame (true / false)


// ----------------------------
// Set the character
// ----------------------------

// Color basic character

$charR = 0;     // Font color in RGB: Red (0 -> 255)
$charG = 0;     // Couleur des caractères au format RGB: Green (0->255)
$charB = 255;     // Couleur des caractères au format RGB: Blue (0->255)

$charcolorrnd = false;      // Random choice of color.
$charcolorrndlevel = 2;    

$charclear = 0;  // Intensity of transparency characters (0 -> 127)
															// 0 = opaque, 127 = invisible
							// Interesting if you use an image $ bgimg
							// Only if PHP> = 3.2.1
// Fonts

//$tfont[] = 'Alanden_.ttf';       // The fonts will be used randomly.
//$tfont[] = 'bsurp___.ttf';       // You must copy the corresponding files
//$tfont[] = 'ELECHA__.TTF';       //  on the server.
$tfont[] = 'luggerbu.ttf';         // Add as many rows as you want  
//$tfont[] = 'RASCAL__.TTF';       // case-sensitive!
//$tfont[] = 'SCRAWL.TTF';  
//$tfont[] = 'WAVY.TTF';   


// Allowed Caracteres
// Note that some fonts do not distinguish (or difficult) the upper
// Sensitive. Some characters are easy to confuse, it is
// Recommended to choose the characters used.

$charel = 'ABCDEFGHKLMNPRTWXYZ234569';       // Caractères autorisés

$crypteasy = true;       // Create cryptograms "easy to read" (true / false). Alternatively compounds consonants and vowels.

$charelc = 'BCDFGHKLMNPRTVWXZ';   // consonants to use when $crypteasy = true
$charelv = 'AEIOUY';              // Vowels to use when $crypteasy = true

$difuplow = false;          // Differentiates Maj / Min when entering the code (true, false)

$charnbmin = 5;         // min number of characters
$charnbmax = 5;         // max num of chars

$charspace = 22;        // Space between characters (in pixels)
$charsizemin = 16;      // Minimum size characters
$charsizemax = 18;      // Maximum size of characters

$charanglemax  = 10;     // Maximum angle of rotation of the characters (0-360)
$charup   = true;        // Vertical displacement random characters (true / false)

// Special Effects

$cryptgaussianblur = false; // Transform the final image blurring: Gauss (true / false)
$cryptgrayscal = false;     // Transform the final image in grayscale (true / false)

// ----------------------
// Configuration du bruit
// ----------------------

$noisepxmin = 500;      // Noise: Minimum Number of random pixels
$noisepxmax = 1000;      // Noise: Maximum Number of random pixels

$noiselinemin = 5;     // Noise: minimum Number of random rows
$noiselinemax = 10;     // Noise: Maximum Number of random lines

$nbcirclemin = 0;      // Noise: Nb minimum random circles 
$nbcirclemax = 0;      // Noise: Number max of random circles

$noisecolorchar  = 1; // Noise: writing pixel color, lines, circles: 1: color of font 2: Background Color 3: Random color
$brushsize = 1;        // Font size of princeaiu (in pixels) 
						// 1 to 25 (the higher values ​​may cause 
						// Internal Server Error on some versions of PHP / GD) 
						// Does not work on older configurations PHP / GD

$noiseup = true;      // noise is it above the write (true) or below (false)

// --------------------------------
// System & Security
// --------------------------------


$cryptformat = "png";    // Image file format generated "GIF", "PNG" or "JPG"
					// If you want a transparent background, use "PNG" (not "GIF")
					// Note some versions of the GD library does not gerent GIF!

$cryptsecure = "md5";    					// Method used crytpage "md5", "sha1" or "" (none) 
					// "Sha1" only if PHP> = 4.2.0
					// If no method is specified, the code is stored cyptogramme 
					// To clear the session.
$cryptusetimer = 0;        // Time (in seconds) before being allowed to regenerate a cryptogram

$cryptusertimererror = 3;  

$cryptusemax = 1000;  // Nb maximum time the user may generate the cryptogram

                      
$cryptoneuse = false;  // If you want the page verification is valid only
                       // When the input when reloading the page indicate "true".
// Otherwise, reloading the page will always confirm the entry.                          
                      
?>
