<?php
add_option('uploaded_product_file', '', '', 'yes');
// Upload file
if (isset($_POST['upload_file'])) {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    function change_temp_dir_upload($dirs)
    {
        $dirs['subdir'] = '/cedcommerce_product_file';
        $dirs['path'] = $dirs['basedir'] . '/cedcommerce_product_file';
        $dirs['url'] = $dirs['baseurl'] . '/cedcommerce_product_file';
        return $dirs;
    }

    if ($_FILES['file']['name'] != '') {
        add_filter('upload_dir', 'change_temp_dir_upload');
        $uploadedfile = $_FILES['file'];
        if ($uploadedfile['type'] == 'application/json') { // Checking File Type If Json Will Upload  otherwise Return false
            $overrides = array(
                'test_form' => false,
            );
            $movefile = wp_handle_upload($uploadedfile, $overrides);
            if ($movefile && !isset($movefile['error'])) {
                $current_value = get_option('uploaded_product_file', 1);
                $name = $uploadedfile['name'];
                if (empty($current_value)) {
                    $current_value = array($name);
                    update_option('uploaded_product_file', $current_value);
                } else {
                    if (in_array($name, $current_value)) {
                        _e("File Already Exist");
                    } else {
                        if (!empty($current_value)) {
                            $current_value[] = $name;
                        } else {
                            $current_value = array($name);
                        }
                        update_option('uploaded_product_file', $current_value);
                    }
                }

                echo '<div class="notice is-dismissible notice-success">
                    <p>File Uplodaed Successfully</p>
                </div>';


                remove_filter('upload_dir', 'change_temp_dir_upload');
            } else {
                echo $movefile['error'];
            }
        } else {
            echo " This type of File is Not allowed ";
        }
    }
}

?>
<h1>Upload File</h1>
<table class="form-table woocommerce-importer-options">
    <tbody>
        <tr>
            <th scope="row">
                <label for="upload">
                    Choose a file from your computer:</label>
            </th>
            <td>
                <form method='post' action='' name='myform' enctype='multipart/form-data'>
                    <input type="file" id="upload" name="file" size="25">
                    <input type='submit' name='upload_file' value='Upload File'>
            </td>
            </form>
        </tr>
    </tbody>
</table>