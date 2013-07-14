<?php
   include('init.php');

   $settings = \JsonConfig::instance()->getSettings();
   $uitheme = "-".$settings['uitheme'];
   if($uitheme=="-classic") $uitheme = "";
?>
<!DOCTYPE html>
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <title>webshelf file explorer @ <?php echo $_SERVER['HTTP_HOST']; ?></title>

      <script type="text/javascript">
         var hostname = "<?php echo $_SERVER['HTTP_HOST']; ?>";
         var separator = "<?php echo addslashes(DIRECTORY_SEPARATOR); ?>";
         <?php
            echo "var Settings = {\n";
            $varlist = array();
            foreach($settings as $key => $value) {
               if(is_bool($value)) {
                  $varlist[] = "            '".$key."': ".($value ? "true" : "false");
               } elseif(is_int($value)) {
                  $varlist[] = "            '".$key."': ".$value;
               } else {
                  $varlist[] = "            '".$key."': \"".str_replace('"', "\\\"", $value)."\"";
               }
            }
            echo implode(",\n", $varlist)."\n";
            echo "         };\n";
         ?>
      </script>

      <link rel='StyleSheet' type='text/css' href='ext/resources/css/ext-all<?php echo $uitheme ?>.css'>
      <link rel='StyleSheet' type='text/css' href='style.css'>
      <link rel='StyleSheet' type='text/css' href='ux/upload/css/upload.css'>

      <script type="text/javascript" src="ext/ext-all<?php echo ($_SERVER['HTTP_HOST']=="raspberrypi" || $_SERVER['HTTP_HOST']=="192.168.1.128" ? "-debug" : "") ?>.js"></script>
      <script type="text/javascript" src="app/msg.js"></script>
      <script type="text/javascript" src="app/tools.js"></script>
      <script type="text/javascript" src="app/hashmanager.js"></script>

      <script type="text/javascript">
         HashManager.init();
      </script>

      <script type="text/javascript" src="app/app.js"></script>

   </head>
   <body>

      <noscript>
         <div style="position:absolute; top:32px; left:32px; font-size:16px; color:red; font-weight: bold; font-family: Arial;">
            Please enable Java Script!
         </div>
      </noscript>

      <img src="ajax.gif" style="display:block; margin:200px auto 0px auto;" alt="Loading..." title="Loading...">

   </body>
</html>
