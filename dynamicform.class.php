<?php

// TODO DO I HAVE TO LIST THEM ALL?
require_once 'custominputitem.class.php';
require_once 'custominputitemarray.class.php';
require_once 'custominputitembigtext.class.php';
require_once 'custominputitemcheck.class.php';
require_once 'custominputitemenum.class.php';
require_once 'custominputitemfile.class.php';
require_once 'custominputitemtext.class.php';

class CustomInput
{
    private $structure;
    private $customInputItemClasses;

    /**
     * Returns a human-readable HTML table with the fields and their contents
     * 
     * If $unrestrictOnly == true, only the unrestrict items will be shown
     * 
     * Optionally the parameters $htmlClass and/or $htmlId can be passed, if the table
     * needs to be styled with css or modified with javascript. As can be deduced, if
     * these parameters are not null, the table will be output with class="$htmlClass"
     * and/or id="$htmlId" attributes.
     */
    function getHtmlFormattedContent($unrestrictOnly = false, $htmlClass = null, $htmlId = null)
    {
        $result = "<table";
        if ($htmlClass !== null) $result .= " class=\"$htmlClass\"";
        if ($htmlId !== null) $result .= " id=\"$htmlId\"";
        $result .= ">";
        foreach ($this->structure as $structureItem)
            if ($structureItem->unrestrict || !$unrestrictOnly)
                $result .= $structureItem->getHtmlFormattedContent();
        $result .= "</table>";
        return $result;
    }

    function getJSONStructure() {
        $result = '[';
        foreach ($this->structure as $structureItem)
            $result .= $structureItem->getJSONStructure().",";
        $result = rtrim($result,',');
        $result .= ']';
        return $result;
    }

    function getJSONContent() {
        $result = '[';
        foreach ($this->structure as $structureItem)
            $result .= "'".$structureItem->content."',";
        $result = rtrim($result,',');
        $result .= ']';
        return $result;
    }

    function outputControls($strName, $cntName, $active = true)
    {
        $result  = "
        <!-- Begin of CustomInput's automatically generated code-->
        <input type=\"hidden\" name=\"$strName\" value=\"".htmlentities($this->getJSONStructure())."\" />";
        foreach ($this->structure as $index => $structureItem)
            $result .= $structureItem->outputControls($cntName, $index, $active);
        $result .= "
        <!-- End of CustomInput's automatically generated code-->";
        return $result;
    }

    function outputStructureTable($strName)
    {
        $result  = "
        <!-- Begin of CustomInput's automatically generated code-->
        <script>
        var str_{$strName} = {$this->getJSONStructure()};
        function move_item_{$strName}(i, offset)
		{
			if (i+offset >= str_{$strName}.length || i+offset < 0) return;
			current_item = str_{$strName}[i];
			moved_item = str_{$strName}[i+offset];
			str_{$strName}[i+offset] = current_item;
			str_{$strName}[i] = moved_item;
			update_table_{$strName}();
			update_field_{$strName}();
		}

		function delete_item_{$strName}(i)
		{
			if(confirm('Você realmente deseja apagar o item na posição: ' + (i+1) + '?'))
			{
				str_{$strName}.splice(i, 1);
				update_table_{$strName}();
				update_field_{$strName}();
			}
        }
        
        function update_field_{$strName}()
		{
			document.getElementById('field_{$strName}').value = JSON.stringify(str_{$strName});
		}

		function update_table_{$strName}()
		{
			var table = document.getElementById('structure_table_body_{$strName}');
			while (table.rows.length > 0) table.deleteRow(-1);
			for (j = 0; j < str_{$strName}.length; j++)
			{ 
				var tr = table.insertRow(-1);
				tr.insertCell(-1).innerHTML = j+1;
				tr.insertCell(-1).innerHTML = str_{$strName}[j].type;
				tr.insertCell(-1).innerHTML = str_{$strName}[j].description;
				tr.insertCell(-1).innerHTML = (str_{$strName}[j].unrestrict) ? '&#8226;' : '';
				tr.insertCell(-1).innerHTML = (str_{$strName}[j].mandatory) ? '&#8226;' : '';
				tr.insertCell(-1).innerHTML = '<button type=\"button\" onclick=\"move_item_{$strName}('+j+', -1)\">UP</button></td>';
				tr.insertCell(-1).innerHTML = '<button type=\"button\" onclick=\"move_item_{$strName}('+j+', +1)\">DOWN</button>';
        ";

        // CustomItems edit functions
        foreach ($this->customInputItemClasses as $customInputItemClass)
            $result .= "
                if (str_{$strName}[j].type == '{$customInputItemClass::getType()}') tr.insertCell(-1).innerHTML = '<button type=\"button\" onclick=\"{$customInputItemClass::javascriptEditMethod()}_{$strName}('+j+')\">EDIT</button>';";
        
        $result .= "
                tr.insertCell(-1).innerHTML = '<button type=\"button\" onclick=\"delete_item_{$strName}('+j+')\">DELETE</button>';
				tr.childNodes[3].style.textAlign = 'center';
				tr.childNodes[4].style.textAlign = 'center';
			}
        }
        </script>
        <div>";

        // CustomItems add Buttons
        foreach ($this->customInputItemClasses as $customInputItemClass)
            $result .= $customInputItemClass::outputCustomInputStructureAddButton($strName);
        
        $result .= "
        </div>
        <table id=\"structure_table_{$strName}\">
        <thead><th>Posição</th><th>Tipo</th><th>Descrição</th><th>Visão irrestrita</th><th>Obrigatório</th><th colspan=\"4\">Opções</th></thead>
        <tbody id=\"structure_table_body_{$strName}\"></tbody>
        </table>
        <input type=\"hidden\" name=\"{$strName}\" id=\"field_{$strName}\"/>
        <script>
        update_table_{$strName}();
        update_field_{$strName}();
        </script>
        <!-- End of CustomInput's automatically generated code -->";
        return $result;        
    }

    /** 
     * Performs the validation of the content on Dynamic Form. If there is file information, 
     * it will try to upload the files to upload folder and update the content with the
     * uploaded file path.
     * 
     * This function returns an array with pairs of indexes and error codes. The indexes are
     * in the order of the structure elements (from 0 to n-1). The error codes are constants
     * defined in the class DynamicFormValidationError.
     * 
     * Notice that this function can upload a file to server if all the validations for this
     * field are successful and still return validation errors for other fields.
     * 
     * If the validation is successful, an empty array is returned.
     */
    function validate()
    {
        $validationErrors = array();
        foreach($this->structure as $i => $structureItem)
        {
            $validationMessages = $structureItem->validate();
            if (!empty($validationMessages)) $validationErrors[$i] = $validationMessages;
        }
        return $validationErrors;
    }

    /**
     * DynamicForm constructor
     * 
     * This is the start point for constructing DynamicForms
     * 
     * $structure - String - A JSON Description of the structure
     * $content - Array - An ordered array with the content
     * $files - The $_FILES['$contentName'] containing the uploaded files
     * $uploadPath - The upload path for files, if $files is not null
     */
    function __construct($structure = null, $content = null, $files = null, $uploadPath = null)
	{
        // Loading all Declared Classes that are of type CustomInputItem
        $this->customInputItemClasses = array();
        foreach( get_declared_classes() as $class ){
          if( is_subclass_of( $class, 'CustomInputItem' ) )
            $this->customInputItemClasses[] = $class;
        }

        // Creating CustomInputItem's array
        $this->structure = array();

        // Reading $files and organizing its information
        $newFiles = null;
        if (is_array($files))
        {
            $newFiles = array();
            foreach ($files as $file_field => $file_info)
                foreach ($file_info as $index => $info) {
                    $newFiles[$index][$file_field] = $info;
                    $newFiles[$index]['upload_path'] = $uploadPath;
                }
        }
        
        // Populating CustomInputItems array if a structure is passed
        if (!empty($structure)) {
            foreach (json_decode($structure) as $i => $structureItem) {
                foreach ($this->customInputItemClasses as $customInputItemClass) {
                    if ($customInputItemClass::getType() == $structureItem->type) {
                        $content_item = null;
                        if (isset($content[$i])) $content_item = $content[$i];
                        else if (isset($newFiles[$i])) $content_item = $newFiles[$i];
                        $this->structure[] = new $customInputItemClass($structureItem, $content_item);                        
                    }
                }
            }
        }   
    }
}

?>