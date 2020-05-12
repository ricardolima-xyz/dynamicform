<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>DynamicForm test</title>
</head>
<body>
<h1>DynamicForm test</h1>
<h2>Step 3 - Input validation and display</h2>
<?php

require_once '../custominput.class.php';
$customInput = new CustomInput($_POST['s'], $_POST['c'], $_FILES['c']);
$validationErrors = $customInput->validate();

?>
<script>
function toggleShow()
{
  var x = document.getElementById("variables_area");
  if (x.style.display == "none") x.style.display = "block";
  else x.style.display = "none";
}
</script>
<div class="toolbar">
<button onclick="toggleShow()">Variables</button>
</div>
<pre id="variables_area">
<?php

echo '$_POST ';
var_dump($_POST);
echo '$_FILES ';
var_dump($_FILES);
echo '$customInput ';
var_dump($customInput);
echo '$validationErrors ';
var_dump($validationErrors);

?>
</pre>
<form method="post" action="step3.php" enctype="multipart/form-data">
<div id="controls">
<?php echo $customInput->outputControls('s', 'c'); ?>
<button type="submit">Enviar</button>
</div>
</form>
</body>
</html>