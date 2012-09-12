<?php

include_once('lib/class.database.php');

$db = new databaseLocal();

if (isset($_REQUEST["action"]) && $_REQUEST["action"]=="create")
{
  $_REQUEST["patch"]["xml"] = mysql_real_escape_string($_REQUEST["patch"]["xml"]);
  $_REQUEST["patch"]["screenshot"] = mysql_real_escape_string($_REQUEST["patch"]["screenshot"]);
  $_REQUEST["patch"]["name"] = mysql_real_escape_string($_REQUEST["patch"]["name"]);
  $_REQUEST["patch"]["author"] = mysql_real_escape_string($_REQUEST["patch"]["author"]);
  if (isset($_REQUEST["patch"]["parent_id"])) {
    $db->query("SELECT * FROM patch WHERE hash='".mysql_real_escape_string($_REQUEST["patch"]["parent_id"])."'");
    if ($db->next_record())
      $_REQUEST["patch"]["parent_id"] = $db->get("id");
    else {
      echo "Error: no patch with id ".$_REQUEST["patch"]["parent_id"]." found. Dying now.";
      die;
    }
  }
  $_REQUEST["patch"]["created_at"] = date('Y-m-d H:i');
  $_REQUEST["patch"]["hash"] = sha1(uniqid(mt_rand(), true));
  $db->add('patch', $_REQUEST["patch"]);
  
  header("Location: ".$_SCRIPT["PHP_SELF"]."#create_success");
  die;
}

function getHirarchy($id)
{
  $id = intval($id);
  $db = new databaseLocal();
  $db->query("SELECT * FROM patch WHERE id=$id");
  $db->next_record();
  $ret = '';
  if ($db->get("parent_id")!="")
    $ret = getHirarchy($db->get("parent_id"));
  return $ret.'-'.str_pad($id, 4, '0', STR_PAD_LEFT);
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>VVVV.js Lab</title>
<link rel="stylesheet" type="text/css" href="vvvv_js-b687505452/vvvviewer/vvvv.css"/>
<link rel="stylesheet" type="text/css" href="main.css"/>
<script language="JavaScript" src="vvvv_js-b687505452/lib/jquery/jquery-1.7.1.min.js"></script>
<script language="JavaScript" src="main.js"></script>
<script language="JavaScript" src="vvvv_js-b687505452/vvvv.js"></script>
<script language="VVVV" src="index.v4p"></script>
<script language="JavaScript">
  $(window).load(function() {
    VVVV.init('vvvv_js-b687505452', 'full', function() {
      $('#patchlist').show();
      //var vvvviewer = new VVVV.VVVViewer(VVVV.Patches[0], '#thepatch');
    });
  })
</script>
</head>
<body>
  
<div id="menu_bar">
  <a class="page_title">VVVV.js <span>Lab</span></a class="page_title">
  <div id="controls">
    <a href="new.php" id="showpatch">New Patch</a>
    <div id="display_switch">
      Display 
      <label>Chronic</label>
      <a href="#" id="display_toggle"><div></div></a>
      <label>Evolution</label>
    </div>
  </div>
</div>

<div class="shelf" id="create_success_shelf">
  <span class="message success"><span>!</span> Your patch has been saved.</span><br/>
  <p>
    We are reviewing your patch to make sure it doesn't contain any mischief. It won't take long, and it will be online shortly!<br/>
  </p>
  <input class="button close" type="button" value="OK"/>
</div>

<div class="shelf" id="welcome_shelf">
  <h2>Welcome to the VVVV.js Lab,</h2>
  <p>
    the place to <b>patch, learn, remix and share</b> VVVV.js. It works kind of like a very simple versioning tool: you can open
    any of the VVVV.js patches below, alter them, and submit your own version to the gallery.
  </p>
  <p>
    The coolest thing about that is: <b>you don't have to deploy VVVV.js anywhere yourself to try it.</b> The only things you have to do is
    downloading the <a href="https://github.com/downloads/zauner/vvvv.js/vvvv_js_sdk-0.2.zip">VVVV.js SDK</a>, extracting it, and adding its
    path to the list of VVVV contribution paths in your root patch.
  </p>
  <p>
    Enjoy!
  </p>
  <input class="button close" type="button" value="OK"/>
</div>

<div id="patchlist">
  <canvas id="connections" height="800" width="800"></canvas>
  <? $db->query("SELECT * FROM patch ORDER BY created_at DESC"); ?>
  <div id="patch_items">
    <? while ($db->next_record()): ?>
      <? $hirarchy = getHirarchy($db->get("id")); ?>
      <a class="patch_item" href="show.php?id=<?= $db->get("hash") ?>" hirarchyhash="<?= $hirarchy ?>" createdat="<?= $db->get("created_at") ?>">
        <div class="screenshot_container"><img src="screenshot.php?id=<?= $db->get("hash") ?>"/></div>
        <div class="patch_meta">
          <span class="name"><?= strlen($db->get("name"))>19 ? substr($db->get("name"), 0, 19)."..." : $db->get("name") ?></span>
          <span class="author"><?= $db->get("author") ?></span>
          <span class="created_at"><?= strftime('%d. %b', strtotime($db->get("created_at"))) ?></span>
        </div>
      </a>
    <? endwhile; ?>
  </div>
</div>

</body>
</html>