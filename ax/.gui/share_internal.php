<?php
	error_reporting(E_ALL);
	set_error_handler(function ($severity, $message, $file, $line) {
		throw new \ErrorException($message, $severity, $severity, $file, $line);
	});

	ini_set('display_errors', 1);

	$BASEURL = dirname((isset($_SERVER["REQUEST_SCHEME"]) ? $_SERVER["REQUEST_SCHEME"] : "http")."://".(isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "localhost")."/".$_SERVER["SCRIPT_NAME"]);
	$sharesPath = './shares/';

	$user_id = $_GET['user_id'] ?? null;
	$share_on_list_publically = $_GET['share_on_list_publically'] ?? null;
	$experiment_name = $_GET['experiment_name'] ?? null;

	$acceptable_files = ["best_result", "job_infos", "parameters", "results", "ui_url", "cpu_ram_usage", "get_next_trials", "run_uuid"];
	$acceptable_file_names = ["best_result.txt", "job_infos.csv", "parameters.txt", "results.csv", "ui_url.txt", "cpu_ram_usage.csv", "get_next_trials.csv", "run_uuid"];

	$GLOBALS["time_start"] = microtime(true);

	include_once("share_functions.php");

	$update_uuid = isset($_GET["update_uuid"]) ? $_GET["update_uuid"] : null;
	$uuid_folder = null;
	if($update_uuid) {
		$uuid_folder = findMatchingUUIDRunFolder($update_uuid);
	}

	if ($user_id !== null && $experiment_name !== null) {
		if(!$uuid_folder) {
			$userFolder = createNewFolder($sharesPath, $user_id, $experiment_name);
		} else {
			$userFolder = $uuid_folder;
		}
		$run_id = preg_replace("/.*\//", "", $userFolder);

		$added_files = 0;

		$num_offered_files = 0;
		$new_upload_md5_string = "";

		$offered_files = [];
		$i = 0;
		foreach ($acceptable_files as $acceptable_file) {
			$offered_files[$acceptable_file] = array(
				"file" => $_FILES[$acceptable_file]['tmp_name'] ?? null,
				"filename" => $acceptable_file_names[$i]
			);
			$i++;
		}

		foreach ($_FILES as $_file) {
			if(preg_match("/log.(err|out)$/", $_file["name"])) {
				$_file_without_ending = pathinfo($_file["name"], PATHINFO_FILENAME);
				if(!isset($offered_files[$_file_without_ending])) {
					if(isset($_file["name"])) {
						if($_file["error"] != 0) {
							print("File ".htmlentities($_file["name"])." could not be uploaded. Error-Code: ".$_file["error"]);
						} else {
							if ($_file["size"] > 0) {
								$num_offered_files++;
								$offered_files[$_file_without_ending] = array(
									"file" => $_file["tmp_name"] ?? null,
									"filename" => $_file["name"]
								);

							} else {
								#print("File ".htmlentities($_file["name"])." had filesize 0 and will be ignored.\n");
							}
						}
					} else {
						print("Could not determine filename for at least one uploaded file");
					}
				} else {
					print("$_file_without_ending coulnd't be found in \$offered_files\n");
				}
			} else {
				#print("File ".htmlentities($_file["name"])." will be ignored.\n");
			}
		}

		foreach ($offered_files as $offered_file) {
			$filename = $offered_file["filename"];
			$file = $offered_file["file"];
			if($file) {
				$content = file_get_contents($file);
				$new_upload_md5_string = $new_upload_md5_string . "$filename=$content";
				$num_offered_files++;
			}
		}

		if ($num_offered_files == 0) {
			print("Error sharing job. No offered files could be found");
			exit(1);
		}

		$project_md5 = hash('md5', $new_upload_md5_string);

		$found_hash_file_data = searchForHashFile("shares/*/*/*/hash.md5", $project_md5, $userFolder);

		$found_hash_file = $found_hash_file_data[0];
		$found_hash_file_dir = $found_hash_file_data[1];

		if($found_hash_file && is_null($update_uuid)) {
			list($user, $experiment_name, $run_id) = extractPathComponents($found_hash_file_dir);
			echo "This project already seems to have been uploaded. See $BASEURL/share.php?user=$user_id&experiment=$experiment_name&run_nr=$run_id\n";
			exit(0);
		} else {
			if(!$uuid_folder || !is_dir($uuid_folder)) {
				foreach ($offered_files as $offered_file) {
					$file = $offered_file["file"];
					$filename = $offered_file["filename"];
					if ($file && file_exists($file)) {
						$content = file_get_contents($file);
						$content_encoding = mb_detect_encoding($content);
						if($content_encoding == "ASCII" || $content_encoding == "UTF-8") {
							if(filesize($file)) {
								move_uploaded_file($file, "$userFolder/$filename");
								$added_files++;
							} else {
								$empty_files[] = $filename;
							}
						} else {
							dier("$filename: \$content was not ASCII, but $content_encoding");
						}
					}
				}

				if ($added_files) {
					if(isset($_GET["update"])) {
						echo "See $BASEURL/share.php?user=$user_id&experiment=$experiment_name&run_nr=$run_id&update=1 for a live-trace.\n";
					} else {
						echo "Run was successfully shared. See $BASEURL/share.php?user=$user_id&experiment=$experiment_name&run_nr=$run_id\nYou can share the link. It is valid for 30 days.\n";
					}
					exit(0);
				} else {
					if (count($empty_files)) {
						$empty_files_string = implode(", ", $empty_files);
						echo "Error sharing the job. The following files were empty: $empty_files_string. \n";
					} else {
						echo "Error sharing the job. No Files were found. \n";
					}
					exit(1);
				}
			} else {
				foreach ($offered_files as $offered_file) {
					$file = $offered_file["file"];
					$filename = $offered_file["filename"];
					if ($file && file_exists($file)) {
						$content = file_get_contents($file);
						$content_encoding = mb_detect_encoding($content);
						if($content_encoding == "ASCII" || $content_encoding == "UTF-8") {
							if(filesize($file)) {
								move_uploaded_file($file, "$uuid_folder/$filename");
								$added_files++;
							} else {
								$empty_files[] = $filename;
							}
						} else {
							dier("$filename: \$content was not ASCII, but $content_encoding");
						}
					}
				}

				if ($added_files) {
					echo "See $BASEURL/share.php?user=$user_id&experiment=$experiment_name&run_nr=$run_id&update=1 for a live-trace.\n";
					exit(0);
				} else {
					if (count($empty_files)) {
						$empty_files_string = implode(", ", $empty_files);
						echo "Error sharing the job. The following files were empty: $empty_files_string. \n";
					} else {
						echo "Error sharing the job. No Files were found. \n";
					}
					exit(1);
				}
			}
		}
	} else {
		include_once("_functions.php");

		$dir_path = ".";
		if(preg_match("/\/tutorials\/?$/", dirname($_SERVER["PHP_SELF"]))) {
			$dir_path = "..";
		}
		if(!isset($_GET["get_hash_only"])) {
?>
			<script src='plotly-latest.min.js'></script>
			<script src='share.js'></script>
			<script src='share_graphs.js'></script>
			<link href="<?php echo $dir_path; ?>/share.css" rel="stylesheet" />

			<div id="breadcrumb"></div>
<?php
		}
	}

	// Liste aller Unterordner anzeigen
	if (isset($_GET["user"]) && !isset($_GET["experiment"])) {
		$user = $_GET["user"];
		if(preg_match("/\.\./", $user)) {
			print("Invalid user path");
			exit(1);
		}

		$user = preg_replace("/.*\//", "", $user);

		$experiment_subfolders = glob("$sharesPath/$user/*", GLOB_ONLYDIR);
		if (count($experiment_subfolders) == 0) {
			print("Did not find any experiments for $sharesPath/$user/*");
			exit(0);
		} else if (count($experiment_subfolders) == 1) {
			show_run_selection($sharesPath, $user, $experiment_subfolders[0]);
			$this_experiment_name = "$experiment_subfolders[0]";
			$this_experiment_name = preg_replace("/.*\//", "", $this_experiment_name);
			print("<!-- $user/$experiment_name/$this_experiment_name -->");
			print_script_and_folder("$user/$experiment_name/$this_experiment_name");
		} else {
			foreach ($experiment_subfolders as $experiment) {
				$experiment = preg_replace("/.*\//", "", $experiment);
				echo "<a class='share_link' href=\"share.php?user=$user&experiment=$experiment\">$experiment</a><br>";
			}
			print("<!-- $user/$experiment_name/ -->");
			print_script_and_folder("$user/$experiment_name/");
		}
	} else if (isset($_GET["user"]) && isset($_GET["experiment"]) && !isset($_GET["run_nr"])) {
		$user = $_GET["user"];
		$experiment_name = $_GET["experiment"];

		show_run_selection($sharesPath, $user, $experiment_name);
		print("<!-- $user/$experiment_name/ -->");
		print_script_and_folder("$user/$experiment_name/");
	} else if (isset($_GET["user"]) && isset($_GET["experiment"]) && isset($_GET["run_nr"])) {
		$user = $_GET["user"];
		$experiment_name = $_GET["experiment"];
		$run_nr = $_GET["run_nr"];

		$run_folder = "$sharesPath/$user/$experiment_name/$run_nr/";
		if(isset($_GET["get_hash_only"])) {
			echo calculateDirectoryHash($run_folder);

			exit(0);
		} else {
			print("<!-- $user/$experiment_name/$run_nr -->");

			print_script_and_folder("$user/$experiment_name/$run_nr");
			show_run($run_folder);
		}
	} else {
		$user_subfolders = glob($sharesPath . '*', GLOB_ONLYDIR);
		if(count($user_subfolders)) {
			foreach ($user_subfolders as $user) {
				$user = preg_replace("/.*\//", "", $user);
				echo "<a class='share_link' href=\"share.php?user=$user\">$user</a><br>";
			}
		} else {
			echo "No users found";
		}

		print("<!-- startpage -->");
		print_script_and_folder("");
	}
?>
</div>
</body>
</html>
