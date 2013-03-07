<?php 
$cryptinstall="./cryptographp.fct.php";
include $cryptinstall; 
?>

<html>
<div align="center">


<form action="verifier.php?<?PHP echo SID; ?>" method="post">
<table cellpadding=1>
  <tr><td align="center"><?php dsp_crypt(0,1); ?></td></tr>
  <tr><td align="center">Recopier le code:<br><input type="text" name="code"></td></tr>
  <tr><td align="center"><input type="submit" name="submit" value="Envoyer"></td></tr>
</table>
<br><br><br>
</form>

</div>
</html>


