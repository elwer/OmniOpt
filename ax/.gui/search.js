const log = console.log;
const l = log;

var searchTimer; // Globale Variable für den Timer
var lastSearch = "";

async function start_search() {
	var searchTerm = $('#search').val();

	if(searchTerm == lastSearch) {
		return;
	}

	lastSearch = searchTerm;

	// Funktion zum Abbrechen der vorherigen Suchanfrage
	function abortPreviousRequest() {
		if (searchTimer) {
			clearTimeout(searchTimer);
			searchTimer = null;
		}
	}

	abortPreviousRequest();

	// Funktion zum Durchführen der Suchanfrage
	async function performSearch() {
		// Abbrechen der vorherigen Anfrage, falls vorhanden
		abortPreviousRequest();

		if (!/^\s*$/.test(searchTerm)) {
			$("#delete_search").show();
			$("#searchResults").show();
			$("#mainContent").hide();
			$.ajax({
			url: 'search.php',
				type: 'GET',
				data: { regex: searchTerm },
				success: async function (response) {
					await displaySearchResults(searchTerm, response);
				},
				error: function (xhr, status, error) {
					console.error(error);
				}
			});
		} else {
			$("#delete_search").hide();
			$("#searchResults").hide();
			$("#mainContent").show();
		}
	}

	// Starten der Suche nach 10 ms Verzögerung
	searchTimer = setTimeout(performSearch, 10);
}

// Funktion zur Anzeige der Suchergebnisse
async function displaySearchResults(searchTerm, results) {
	var $searchResults = $('#searchResults');
	$searchResults.empty();

	if (results.length > 0) {
		$searchResults.append('<h2>Search results:</h2>');

		results.forEach(function(result) {
			log("result:", result);
			var result_line = `- <a href="${result.link}">${result.content}</a><br>`;
			$searchResults.append(result_line);
		});

		// Hintergrundladen und Austauschen der Vorschaubilder
		$('.loading-thumbnail-search').each(function() {
			var $thumbnail = $(this);
			var originalUrl = $thumbnail.attr('data-original-url');

			// Bild im Hintergrund laden
			var img = new Image();
			img.onload = function() {
				$thumbnail.attr('src', originalUrl); // Bild austauschen, wenn geladen
			};
			img.src = originalUrl; // Starte das Laden des Bildes im Hintergrund
		});
	} else {
		$searchResults.append('<p>No results found.</p>');
	}
}
