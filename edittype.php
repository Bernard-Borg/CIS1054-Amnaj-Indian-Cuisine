<?php
	require_once 'dbwrapper.php';
	require_once 'bootstrap.php';
	require_once 'resources/includes/handleimages.php';
	require_once 'resources/includes/validate.php';
	session_start();

	if (isset($_SESSION['usergroup'])){
		if($_SESSION['usergroup'] == 1){
			$db = new Db();
			$val = new Validate();
			$himg = new HandleImages();
			
			if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eTypeName']) && isset($_FILES['eTypePhoto']) && isset($_POST['eTypeId'])){
				$name = $_POST["eTypeName"];
				$photo = $_FILES["eTypePhoto"];
				$id = $_POST['eTypeId'];
				$photoCheck = true;
				$validations = array();
				$formvalues = array();

				$vid = $val->validateInt($id, 3);
				$idCheck = $db->select("SELECT typeid FROM types WHERE typeid = ".$db->quote($id));
				
				if($id != $vid){
					$validations['id'] = $vid;
				}else if($idCheck === false){
					$validations['id'] = "Please enter an existing id";
				}

				$vname = $val->validateString($name, 20);
				
				if($name != $vname){
					$validations['name'] = $vname;
				}

				if($photo['size'] !== 0){
					$upload = $himg->upload($photo['name'], $photo['tmp_name'], $photo['size'], $photo['error'], "icons");

					if(strpos($upload, $photo['name']) === false){
						$validations['photo'] = $upload;
					}
				}else{
					$photoCheck = false;
				}

				if(!empty($validations)){
					$formvalues['name'] = $name;
					$formvalues['photo'] = $photo;
					$formvalues['id'] = $id;

					echo $twig->render("edittype.html", ['validations' => $validations, 'formvalues' => $formvalues]);
				}else{
					if($photoCheck === true){
						$sql = $db->query("UPDATE types SET type =".$db->quote($name).", typeimg=".$db->quote($upload)." WHERE typeid=".$db->quote($id));
					}else{
						$sql = $db->query("UPDATE types SET type =".$db->quote($name)." WHERE typeid=".$db->quote($id));
					}
					
					header("Location: viewtypes.php?success=true");
					exit();
				}
			}else{
				echo $twig->render("edittype.html");
			}
		}else{
			header("Location: index.php", true, 403);
			exit();
		}
	} else {
		header("Location: signin.php");
		exit();
	}