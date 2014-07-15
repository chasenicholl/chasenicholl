Comet.isReady( function() {
        
    $J('#upload_stl').on( 'click', function() {
        $J('#stl_file').trigger('click');
    } ); 
    
    $J('#stl_file').on( 'change', function() {
        if ($J(this).val().length > 0) {
            var file_name = $J(this).val();
            var ext = file_name.substr(file_name.lastIndexOf('.') + 1);
            if (ext != 'stl') {
                alert('Sorry, we only except *.stl file types!');
                return false;
            }
            $J('#upload_form').submit(); // Upload file
            //console.log('kick off upload');
        }
    } );
     
} );