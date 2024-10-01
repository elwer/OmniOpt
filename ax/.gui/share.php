<?php
        require "_header_base.php";
?>
        <div id="share_main" style="display: none"></div>
</div>
<script>
	alert = console.error;

	var already_initialized_tables = [];
	var last_load_content = "";
	var last_hash = "";
	var countdownInterval;
	var currently_switching = false;

	var tab_ids = ["out_files_tabs", "main_tabbed"];

	var activeTabIndices = {};

	function saveActiveTab() {
		for (var i = 0; i < tab_ids.length; i++) {
			try {
				var tab_id = tab_ids[i];
				var _active_tab = $("#" + tab_id).tabs().tabs("option", "active");

				log(`Checking tab ${tab_id}:`, _active_tab);

				if(typeof(_active_tab) == "number") {
					activeTabIndices[tab_id] = _active_tab;
				} else {
					console.warn(`Error while saveActiveTab: typeof(activeTabIndices[${tab_id}]) == ${typeof(activeTabIndices[tab_id])}:`, activeTabIndices[tab_id]);
				}
			} catch (e) {
				console.warn(e);
			}
		}
	}

	function restoreActiveTab() {
		for (var i = 0; i < tab_ids.length; i++) {
			var tab_id = tab_ids[i];

			if (Object.keys(activeTabIndices).includes(tab_id)) {
				var _saved_active_tab = activeTabIndices[tab_id];

				if(typeof(_saved_active_tab) == "number") {
					try {
						var _tab_id = "#" + tab_id;
						log(`Trying to set ${_tab_id} to ${_saved_active_tab}`);

						$(_tab_id).tabs("option", "active", _saved_active_tab);

						log(`Set ${_tab_id} to ${_saved_active_tab}`);
					} catch (e) {
						if(!("" + e).includes("cannot call methods on tabs prior to initialization")) {
							console.error(e);
						}
					}
				} else {
					log(`Error: _saved_active_tab is not an integer, but ${typeof(_saved_active_tab)}:`, _saved_active_tab);
				}
			} else {
				log(`No saved active tab for #${tab_id}`);
			}
		}
	}

	function getParameterByName(name) {
		var regex = new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)');
		var results = regex.exec(window.location.search);
		return results === null ? '' : decodeURIComponent(results[1]);
	}

	async function load_content(msg) {
		while (currently_switching) {
			await sleep(10_000);
		}

		currently_switching = true;
		var queryString = window.location.search;
		var requestUrl = 'share_internal.php' + queryString;

		showSpinnerOverlay(msg);

		$.ajax({
		url: requestUrl,
			method: 'GET',
			success: function(response) {
				saveActiveTab();
				if (response != last_load_content) {
					$('#share_main').html(response).show();
					last_load_content = response;
				}

				already_initialized_tables = [];
				$("[id*='autotable_']").remove();
				$(".toggle_raw_data").remove();

				initialize_autotables();
				restoreActiveTab();
				removeSpinnerOverlay();
				currently_switching = false;
			},
			error: function() {
				showSpinnerOverlay(msg);
				console.error('Error loading the content.');
				$('#share_main').html('Error loading the requested content!').show();
				removeSpinnerOverlay();
				currently_switching = false;
			}
		});
	}

	function fetchHashAndUpdateContent(interval) {
		if(currently_switching) {
			return;
		}
		var share_internal_url = window.location.toString();
		share_internal_url = share_internal_url.replace(/share\.php/, "share_internal.php");
		var hashUrl = share_internal_url + '&get_hash_only=1';

		$.ajax({
		url: hashUrl,
			method: 'GET',
			success: async function(response) {
				var newHash = response.trim(); // Ensure no extra spaces or newlines

				if (newHash !== last_hash) {
					console.log(`${new Date().toString()}: Hash changed, reloading content.`);
					last_hash = newHash;
					await load_content(`Loading new jobs...`);
				} else {
					console.log(`${new Date().toString()}: Hash unchanged, no reload necessary.`);
				}
			},
			error: function() {
				console.error('Error fetching the hash.');
			}
		});
	}

	$(document).ready(function() {
		load_content("Loading OmniOpt-Share...");

		var auto_update = getParameterByName('update');

		if (auto_update) {
			var interval = 5000;
			setInterval(function() {
				fetchHashAndUpdateContent(interval);
			}, interval);
		}
	});
</script>
