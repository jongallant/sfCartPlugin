<div id="sfCartAdminContainer">

  <?php echo include_partial('cartadmin/menu') ?>

  <h1>Settings</h1>

  <div id="save">
    <input type="submit" name="updatesettings" id="updatesettings" value="Save Changes" />
    <span id="messagebox"></span>
  </div>
  
  <?php if ($settings != null) { ?>

      <?php foreach ($settings as $i => $setting) {     
          echo $setting->getName();        
      }
    }      ?>
      
</div>
