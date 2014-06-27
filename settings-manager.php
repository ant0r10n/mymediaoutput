<?php
	function detect_config($redirect_to_setup = true){
		$config_exists = is_readable(dirname(__FILE__) . '/config.php');

		if(!$config_exists && $redirect_to_setup){
			$url = (request_outside_admin_folder() ? '' : '../') . 'setup.php';

			if(!headers_sent()){
				@header("Location: $url");
			}else{
				echo '<META HTTP-EQUIV="Refresh" CONTENT="0;url=' . $url . '">' .
					 '<script>window.location = "' . $url . '";</script>';
			}
			exit;
		}

		return $config_exists;
	}

	function migrate_config(){
		$curr_dir = dirname(__FILE__);
		if(!is_readable($curr_dir . '/admin/incConfig.php') || !detect_config(false)){
			return false; // nothing to migrate
		}

		@include($curr_dir . '/admin/incConfig.php');
		@include($curr_dir . '/config.php');

		$config_array = array(
			'dbServer' => $dbServer,
			'dbUsername' => $dbUsername,
			'dbPassword' => $dbPassword,
			'dbDatabase' => $dbDatabase,
			'adminConfig' => $adminConfig
		);

		if(save_config($config_array)){
			@rename($curr_dir . '/admin/incConfig.php', $curr_dir . '/admin/incConfig.bak.php');
			@unlink($curr_dir . '/admin/incConfig.php');
			return true;
		}

		return false;
	}

	function save_config($config_array = array()){
		$curr_dir = dirname(__FILE__);
		if(!count($config_array) || !count($config_array['adminConfig'])) return array('error' => 'Invalid config array');

		$new_admin_config = '';
		foreach($config_array['adminConfig'] as $admin_var => $admin_val){
			$new_admin_config .= "\t\t'" . addslashes($admin_var) . "' => \"" . str_replace(array("\n", "\r", '"'), array('\n', '\r', '\"'), $admin_val) . "\",\n";
		}
		$new_admin_config = substr($new_admin_config, 0, -2) . "\n";

		$new_config = "<?php\n" . 
			"\t\$dbServer = '" . addslashes($config_array['dbServer']) . "';\n" .
			"\t\$dbUsername = '" . addslashes($config_array['dbUsername']) . "';\n" .
			"\t\$dbPassword = '" . addslashes($config_array['dbPassword']) . "';\n" .
			"\t\$dbDatabase = '" . addslashes($config_array['dbDatabase']) . "';\n" .

			"\n\t\$adminConfig = array(\n" . 
				$new_admin_config .
			"\t);";

		if(detect_config(false)){
			// attempt to back up config
			@copy($curr_dir . '/config.php', $curr_dir . '/config.bak.php');
		}

		if(!$fp = @fopen($curr_dir . '/config.php', 'w')) return array('error' => 'Unable to write to config file', 'config' => $new_config);
		fwrite($fp, $new_config);
		fclose($fp);

		return true;
	}

	function request_outside_admin_folder(){
		return (realpath(dirname(__FILE__)) == realpath(dirname($_SERVER['SCRIPT_FILENAME'])) ? true : false);
	}
