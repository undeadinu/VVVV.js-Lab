<?php

include_once('lib/class.database.php');

$db = new databaseLocal();

if (isset($_REQUEST["action"]) && $_REQUEST["action"]=="create")
{
  $db->query("INSERT INTO patch (name, author, screenshot, xml, created_at, parent_id, activated) VALUES('The red Quad returns', 'Zauner', '".$_REQUEST['screenshot_data']."', '".mysql_real_escape_string($_REQUEST['xml'])."', NOW(), ".intval($_REQUEST['parent_id']).", 1)");
  echo "Saved.";
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
  return $ret.'-'.$id;
}

?>

<html>
<head>
<title>VVVV.js Lab</title>
<link rel="stylesheet" type="text/css" href="vvvv_js/vvvviewer/vvvv.css"/>
<link rel="stylesheet" type="text/css" href="index.css"/>
<script language="JavaScript" src="vvvv_js/lib/jquery/jquery-1.4.2.min.js"></script> 
<script language="JavaScript" src="vvvv_js/vvvv.js"></script>
<script language="VVVV" src="index.v4p"></script>
<script language="JavaScript">
  $(document).ready(function() {
    initVVVV('vvvv_js', 'full');
    var vvvviewer = new VVVV.VVVViewer(VVVV.Patches[0], '#thepatch');
  })
</script>
</head>
<body>
  
<div id="menu_bar">
  <h1>VVVV.js <span>Lab</span></h1>
  <div id="display_switch">
    Display 
    <label>Chronic</label>
    <a href="#" id="display_toggle"><div></div></a>
    <label>Evolution</label>
  </div>
</div>

<div id="patchlist">
  <canvas id="connections" height="800" width="800"></canvas>
  <? $db->query("SELECT * FROM patch ORDER BY created_at DESC"); ?>
  <? while ($db->next_record()): ?>
    <? $hirarchy = getHirarchy($db->get("id")); ?>
    <a class="patch_item" href="show.php?id=<?= $db->get("id") ?>" hirarchyhash="<?= $hirarchy ?>" createdat="<?= $db->get("created_at") ?>">
      <div class="screenshot_container"><img src="<?= $db->get("screenshot") ?>"/></div>
      <div class="patch_meta">
        <span class="name"><?= $db->get("name") ?></span>
        <span class="author"><?= $db->get("author") ?></span>
        <span class="created_at"><?= $db->get("created_at") ?></span>
      </div>
    </a>
  <? endwhile; ?>
</div>

<div id='thepatch'></div>

</body>
</html>