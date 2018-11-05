var files;

//// Add events
//$(':file').on('change', prepareUpload);
////$("#upload").on( 'click', uploadFiles);
//$("#uploadedFiles").on('click', 'a', removeUploadedFile); // To do ajax call for dynamic component. Refer http://api.jquery.com/on/ delegated events part

// Grab the files and set them to our variable
function prepareUpload(event) {
    $.each(event.target.files, function(i, file){
        if (file.size > 2097152) {
            alert('max upload size is 2MB');
            return;
        }
    });
    files = event.target.files;
    uploadFiles();
}

// Catch the form submit and upload the files
function uploadFiles() {
//    event.stopPropagation(); // Stop stuff happening
//    event.preventDefault(); // Totally stop stuff happening

        // START A LOADING SPINNER HERE
    // Create a formdata object and add the files
    var data = new FormData();
    $.each(files, function(key, value) {
        data.append(key, value);
    });

    $.ajax({
        url: 'upload.php?files',
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function(output, textStatus, jqXHR) {
            if(typeof data.error === 'undefined') {
                // Success so call function to process the form
                $(".uploadedFiles-error").html("");
                if (output.length <= 0) {
                    alert("Unable to upload file");
                    $('#uploadFile').val('');
                    return;
                }
                $fileLink="";
                for (var key in output) {
                    if (output.hasOwnProperty(key)) {
                        $.each(output[key], function (index, value) {
                            $fileLink+= '<span class="link">'+ value +'<a href="#" class="delete-file"><i class="fa fa-times"></i></a></br></span>';
                        });
                    }
                }

                $("#uploadedFiles").append($fileLink);
                $('#uploadFile').val('');
                updateUploadedFiles();
            } else {
                // Handle errors here
                $(".uploadedFiles-error").html("");
                alert("Unable to upload file");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle errors here
            $(".uploadedFiles-error").html("");
            alert("Unable to upload file");
            $('#uploadFile').val('');
            }
        });
    }

function removeUploadedFile() {
	var className = $(this).attr('class');
	if (className != 'delete-file') {
			return;
	}
	$currentSpan=$(this).parent('span');
	$fileName=$currentSpan.text();
	var dataString = 'file=' + $fileName + '&remove=true';
	$.ajax({
        url: 'upload.php',
        type: 'POST',
        data: dataString,
//        cache: false,
        dataType: 'json',
//        processData: false, // Don't process the files
//        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function(output, textStatus, jqXHR) {
            if(textStatus === 'success' && (output['deleted'].indexOf($fileName) != -1 || output['deleted'].indexOf("deleted") != -1)) {
            	$currentSpan.remove();
            	updateUploadedFiles();
            } else {
                // Handle errors here
                $(".uploadedFiles-error").html("");
                alert("Unable to remove uploaded file");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle errors here
            $(".uploadedFiles-error").html("");
//            $("#unuploadFiles").append('Unable to upload file');
            alert("Unable to remove uploaded file");
            // STOP LOADING SPINNER
            }
        });
    }
function updateUploadedFiles() {
    $uploadedFiles="";
	$('.link').each(function(key, val) {
		// here, innerText will return data with new line (\r\n), server need to handle them
		$uploadedFiles+=val.innerText+",";
	});
	$( "#uploaded" ).val($uploadedFiles);
}