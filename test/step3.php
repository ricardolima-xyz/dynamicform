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
<script>
function toggleShow()
{
  var x = document.getElementById("variables_area");
  if (x.style.display === "none") x.style.display = "block";
  else x.style.display = "none";
}
</script>
<div class="toolbar">
<button onclick="toggleShow()">Variables</button>
</div>
<pre id="variables_area">
<?php
require_once '../custominput.class.php';

$customInput = new CustomInput($_POST['s'], $_POST['c']);


var_dump($_POST);
var_dump($customInput);

?>
</pre>
<form method="post" action="step3.php" enctype="multipart/form-data">
<div style="background-color: white; margin: 15% auto; padding: 20px; border: 1px solid #333; width: 80%; display: grid; grid-gap: 0.5em; grid-template-columns: 1fr;">
<?php
echo $customInput->outputControls('s', 'c');
?>
</div>
<button type="submit">Enviar</button>
</form>
</body>
</html>