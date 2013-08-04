<?php

/**
 * Config singleton singleton
 * @method \JsonConfig instance() Description
 */

class JsonConfig extends \Util\Singleton {

   const SESSION_NAME = "username";

   private $configname;
   private $sessionuser;

   protected function init() {

   }

   public function getSkeleton() {
      $stdclass = array(
          "filebase" => "files/",
          "public_group" => "anonymous",
          "settings" => array(
               "upload_maxsize" => 1024, // 1GB
               "windowwidth" => 1024,
               "windowheight" => 600,
               "uitheme" => "gray",
               "upload" => false,
               "copy" => false,
               "mkdir" => false,
               "delete" => false,
               "imageviewer" => false,
               "move_rename" => false,
               "about_content" => "Please modify this text in settings.",
          ),
          "users" => array(
              "admin" => array(
                  "admin" => true,
                  "password" => "d033e22ae348aeb5660fc2140aec35850c4da997",
                  "groups" => array(
                      "admins", "anonymous"
                  )
              ),
          ),
          "groups" => array(
              "anonymous" => array(
                  "deletable" => false,
                  "shares" => array(
                      array(
                          "path" => "public",
                          "read" => true,
                          "protected" => false,
                          "delete" => false,
                          "upload" => false,
                          "mkdir" => false,
                          "copy" => false,
                          "move_rename" => false,
                          "download" => true
                      ),
                  ),
              ),
              "admins" => array(
                  "shares" => array(
                      array(
                          "path" => "admin",
                          "read" => true,
                          "protected" => true,
                          "delete" => true,
                          "upload" => true,
                          "mkdir" => true,
                          "copy" => true,
                          "move_rename" => true,
                          "download" => true
                      ),
                  ),
              ),
          ),
      );

      return $stdclass;

   }

   private function configurationExists() {
      return (file_exists($this->configname) && is_file($this->configname));
   }

   public function createConfiguration($newconfig=null) {
      $skel = $this->getSkeleton();
      $content  = "<?php\n";
      $content .= "// This is the share configuration. Do not edit this file manually!\n\n";
      $content .= "\$config = <<<HEREDOC\n";
      $content .= json_encode((is_null($newconfig) ? $skel : $newconfig))."\n";
      $content .= "HEREDOC;\n\n// EOF";
      $result = file_put_contents($this->configname, $content);

      if($result===false) {
         throw new \Exception("Could not create config");
      }
   }

   public function setConfigName($name) {
      $this->configname = $name;
   }

   public function setSessionUsername($name) {
      $this->sessionuser = $name;
      $_SESSION[self::SESSION_NAME] = $name;
   }

   public function getSessionUsername() {
      return $this->sessionuser;
   }

   public function loadConfiguration() {
      if(!$this->configurationExists()) {
         $this->createConfiguration();
      }

      include($this->configname);
      if(!isset($config)) {
         throw new \Exception("Configuration not found");
      }

      $config = @json_decode(trim($config), true);
      if(!is_array($config)) {
         throw new \Exception("Could not decode configuration");
      }

      return $config;
   }

   public function getSettings()
   {
      $skel = $this->getSkeleton();
      $cfg = $this->loadConfiguration();

      foreach($skel['settings'] as $key => &$item) {
         if(isset($cfg['settings'][$key])) {
            $item = $cfg['settings'][$key];
         }
      }
      unset($item);
      return $skel['settings'];
   }

   public function getSetting($key)
   {
      $cfg = $this->getSettings();
      if(isset($cfg[$key])) {
         return $cfg[$key];
      }

      throw new Exception("Key '".$key."' not found.");

   }

   public function getUser($username=null) {
      if($username==null) {
         $username = $this->sessionuser;
      }
      $config = $this->loadConfiguration();
      if(isset($config['users'][$username])) {
         return $config['users'][$username];
      } else {
         throw new \Exception("User not found");
      }
   }

   public function isLoggedIn() {
      try {
         $this->getUser();
         return true;
      } catch(Exception $ex) {
         return false;
      }
   }

   public function userExist($user) {
      $config = $this->loadConfiguration();
      return isset($config['users'][$user]);
   }

   public function groupExist($group) {
      $config = $this->loadConfiguration();
      return isset($config['groups'][$group]);
   }

   public function isAdmin() {
      try {
         $usr = $this->getUser();
         return $usr['admin'];
      } catch(Exception $ex) {
         return false;
      }
   }

   public function getUserShares($username=null) {
      $cfg = \JsonConfig::instance()->loadConfiguration();
      if($username==null) {
         $username = \JsonConfig::instance()->getSessionUsername();
      }

      $groups = array();
      if(isset($cfg['users'][$username]['groups'])) {
         $groups = $cfg['users'][$username]['groups'];
      } else {
         $groups = array($cfg['public_group']);
      }

      $shares = array();
      foreach($groups as $group) {
         foreach($cfg['groups'][$group]['shares'] as $share) {
            if(!in_array($share['path'], $shares) && $share['read']===true)
            {
               $shares[] = $share['path'];
            }
         }
      }

      return $shares;
   }

   public function hasUserShareProperty($share, $property, $value, $username=null) {
      $cfg = \JsonConfig::instance()->loadConfiguration();
      if($username==null) {
         $username = \JsonConfig::instance()->getSessionUsername();
      }

      $groups = array();
      if(isset($cfg['users'][$username]['groups'])) {
         $groups = $cfg['users'][$username]['groups'];
      } else {
         $groups = array($cfg['public_group']);
      }

      foreach($groups as $group) {
         foreach($cfg['groups'][$group]['shares'] as $ishare) {
            if($ishare['path']!=$share) {
               continue;
            }
            if(isset($ishare[$property]) && $ishare[$property]==$value) {
               return true;
            }
         }
      }

      return false;
   }

   public function isShareProtected($share)
   {
      $cfg = \JsonConfig::instance()->loadConfiguration();

      foreach($cfg['groups'] as $groupname => $groupdata) {
         foreach($groupdata['shares'] as $sharename => $sharesettings) {
            if(isset($sharesettings['protected']) &&
               $sharesettings['protected']==true &&
               $sharesettings['path']==$share)
            {
               return true;
            }
         }
      }

      return false;
   }

   public function isSharePublic($share)
   {
      $cfg = \JsonConfig::instance()->loadConfiguration();

      foreach($cfg['groups'][$cfg['public_group']]['shares'] as $ishare) {
         if($ishare['path']==$share) {
            return true;
         }
      }

      return false;
   }

}
