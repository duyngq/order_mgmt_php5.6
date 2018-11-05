<?php
error_reporting(E_ALL ^ E_DEPRECATED);
session_start(); /// initialize session
if (!isset($_SESSION['loggedIn']) || (isset($_SESSION['loggedIn']) && !$_SESSION['loggedIn'])) {
    header("location:login.php");
}
function generateUploadDir() {
	$pos1 = rand(0, 25);
	$pos2 = rand(26, 51);
	$a_z = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$randomLetter1 = $a_z[$pos1];
	$randomLetter2 = $a_z[$pos2];
	return $randomLetter1 . $_SESSION['user_id'] . $randomLetter2;
}
$data = array();
$path = generateUploadDir();
if(isset($_GET['files'])) {
    $error = false;
    $errorFiles = array();
    $files = array();
    $uploaddir = './uploads/'.$path.'/';
    if (!file_exists($uploaddir)) {
        mkdir($uploaddir, 0755, true);
    }
    if (is_dir($uploaddir) && is_writable($uploaddir)) {
        foreach($_FILES as $file) {
            $fileName = $file['name'];
            $uploadPath = $uploaddir.basename($fileName);
            $name = pathinfo($fileName, PATHINFO_FILENAME);
			$extension =pathinfo($fileName, PATHINFO_EXTENSION);
			$counter = 1;
			// add suffix to avoid duplicate name
			while (file_exists( $uploadPath )) {
			    $fileName= $name . '_' . $counter++ . '.' . $extension;
			    $uploadPath = $uploaddir.basename($fileName);
			}
            if(move_uploaded_file($file['tmp_name'], $uploadPath)) {
//                chmod($uploadPath, 0755);
                $files[] = $path.DIRECTORY_SEPARATOR.$fileName;
            } else {
                array_push($errorFiles, $file);
                $error = true;
            }
        }
    } else {
        $error = true;
    }
    if ($error) {
        array_push($errorFiles, 'There was an error uploading your files');
    }
    $data = ($error) ? array('error' => $errorFiles) : array('files' => $files);
} else if(isset($_POST['file']) && isset($_POST['remove']) && $_POST['remove'] == 'true' ){
    $deletingFile = dirname(__FILE__).DIRECTORY_SEPARATOR .'uploads'.DIRECTORY_SEPARATOR .$_POST['file'];
    if (!is_file($deletingFile) || !file_exists($deletingFile)) {
        $data = array('deleted' => "File already deleted");
    } else if (unlink($deletingFile)) {
        $data = array('deleted' => $deletingFile);
    } else {
        throw new Exception('Unable to delete file');
    }
} else {
    $data = array('success' => 'Form was submitted', 'formData' => $_POST);
}
echo json_encode($data);
?>