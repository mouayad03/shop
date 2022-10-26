<?php
    require "model/database.php";
    require "controller/error.php";

    function get_category($category_id) {
        global $database;

        $result = $database->query("SELECT * FROM `category` WHERE category_id = $category_id");
        if ($result) {
			$category = $result->fetch_assoc();
            
		    return $category;
		}
    }

    function create_new_category($active, $name) {
        global $database;

        $result = $database->query("INSERT INTO `category` (`active`, `name`) VALUES ('$active', '$name');");

		return true;
    }

    function update_category($category_id, $name, $active) {
		global $database;

		$result = $database->query("UPDATE `category` SET name = '$name', age = $age WHERE category_id = $category_id");

		if (!$result) {
			error("An error while saving the category.", 500);
		}
		else {
			error("The category was succsessfuly updated.", 200);
            return true;
		}
	}

    function delete_category($category_id) {
		global $database;

		$category_id = intval($category_id);

		$result = $database->query("DELETE FROM `category` WHERE category_id = $category_id");
        
		return true;
	}
?>