<?php
add_option('uploaded_order_file', '', '', 'yes');
// Upload file
if (isset($_POST['upload_order_file'])) {
	$nonce = isset($_REQUEST['nonce_verify']) ? sanitize_text_field($_REQUEST['nonce_verify']) : false;
	if (wp_verify_nonce($nonce, 'upload_file_nonce')) {
		if (!function_exists('wp_handle_upload')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		function change_temp_dir_upload( $dirs) {
			$dirs['subdir'] = '/cedcommerce_order_file';
			$dirs['path']   = $dirs['basedir'] . '/cedcommerce_order_file';
			$dirs['url']    = $dirs['baseurl'] . '/cedcommerce_order_file';
			return $dirs;
		}
		if ('' != isset($_FILES['file']['name']) ? sanitize_text_field($_FILES['file']['name']) : false) {
			add_filter('upload_dir', 'change_temp_dir_upload');
			$uploadedfile = isset($_FILES['file']) ?  $_FILES['file']  : false;
			$upload       = wp_upload_dir();
			$upload_dir   = $upload['basedir'];
			$upload_dir   = $upload_dir . '/cedcommerce_order_file/' . $uploadedfile['name'];
			$getFile      =			 @file_get_contents($upload_dir);
			if (  false !== $getFile) {
				echo '<div class="notice is-dismissible notice-error">
						<p>File Already Exist</p>
					</div>';
			} else {
				if ('application/json' == $uploadedfile['type']) { // Checking File Type If Json,it Will Upload  otherwise Return false
					$overrides = array(
						'test_form' => false,
					);
					$movefile  = wp_handle_upload($uploadedfile, $overrides);
					if ($movefile && !isset($movefile['error'])) {
						$current_value = get_option('uploaded_order_file', 1);
						$name          = $uploadedfile['name'];
						if (empty($current_value)) {
							$current_value = array($name);
							update_option('uploaded_order_file', $current_value);
						} else {
							if (in_array($name, $current_value)) {
								echo '<div class="notice is-dismissible notice-error">
							<p>File Already Exist</p>
						</div>';
							} else {
								if (!empty($current_value)) {
									$current_value[] = $name;
								} else {
									$current_value = array($name);
								}
								update_option('uploaded_order_file', $current_value);
								echo '<div class="notice is-dismissible notice-success">
							<p>File Uplodaed Successfully</p>
						</div>';
							}
						}
						remove_filter('upload_dir', 'change_temp_dir_upload');
					} else {
						echo esc_html($movefile['error']);
					}
				} else {
					echo '<div class="notice is-dismissible notice-error">
					<p>File Type Not Allowed</p>
				</div>';
				}
			}
		}
	}
}

?>

<table class="form-table woocommerce-importer-options">
	<tbody>
		<tr>
			<th scope="row">
				<label for="upload">
					Choose a file for create a Order:</label>
			</th>
			<td>
				<form method='post' action='' name='myform' enctype='multipart/form-data'>

					<input type="file"  id="upload" name="file" size="25">
					<input type="hidden" value="<?php echo esc_html(wp_create_nonce('upload_file_nonce')); ?>" name="nonce_verify">
					<input type='submit' class='uploadbtn' name='upload_order_file' value='Upload Order File'>
			</td>
			</form>
		</tr>
	</tbody>
</table>
<div id="message">
</div>
