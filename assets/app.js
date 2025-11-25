import "./stimulus_bootstrap.js";
import "./bootstrap.js";
import "./styles/app.css";

console.log("🎬 CineMira JS chargé !");

// ============================================================================
// 🎬 PARTIE 1 — Recherche TMDB dans /actu (NE PAS TOUCHER)
// ============================================================================
function initSearch() {
    console.log("🔍 Tentative d'initialisation...");

    const form = document.getElementById("searchForm");
    const input = document.getElementById("searchInput");
    const resultsDiv = document.getElementById("searchResults");

    console.log("Form:", form);
    console.log("Input:", input);
    console.log("Results div:", resultsDiv);

    if (!form || !input || !resultsDiv) {
        console.log("⚠️ Éléments absents (normal si pas sur cette page)");
        return;
    }

    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);
    const newInput = newForm.querySelector("#searchInput");
    const finalForm = newForm;

    let timeout = null;

    finalForm.addEventListener("submit", function (e) {
        e.preventDefault();
        searchMovies(newInput.value);
    });

    newInput.addEventListener("input", function () {
        const query = newInput.value.trim();

        if (query.length < 2) {
            resultsDiv.innerHTML = "";
            return;
        }

        clearTimeout(timeout);
        timeout = setTimeout(() => {
            searchMovies(query);
        }, 300);
    });

    async function searchMovies(query) {
        if (!query || query.length < 2) {
            resultsDiv.innerHTML = "";
            return;
        }

        resultsDiv.innerHTML =
            '<div class="text-center mt-3"><div class="spinner-border" role="status"></div></div>';

        try {
            const url = `/api/tmdb/search?query=${encodeURIComponent(query)}`;
            const response = await fetch(url);

            if (!response.ok) throw new Error(response.status);

            const movies = await response.json();
            displayResults(movies);
        } catch (error) {
            resultsDiv.innerHTML =
                '<div class="alert alert-danger mt-3">Erreur lors de la recherche</div>';
        }
    }

    function displayResults(movies) {
        if (!movies.length) {
            resultsDiv.innerHTML =
                '<div class="alert alert-info mt-3">Aucun film trouvé</div>';
            return;
        }

        let html = '<div class="list-group mt-3">';

        movies.forEach((movie) => {
            const title = movie.title || "Sans titre";
            const overview = movie.overview || "Pas de description";
            const posterPath = movie.poster_path
                ? `https://image.tmdb.org/t/p/w200${movie.poster_path}`
                : "";

            html += `
                <div class="list-group-item list-group-item-action">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="${posterPath}" class="img-fluid rounded">
                        </div>
                        <div class="col-md-10">
                            <h5>${escapeHtml(title)}</h5>
                            <p>${escapeHtml(overview.substring(0, 200))}...</p>
                        </div>
                    </div>
                </div>
            `;
        });

        html += "</div>";
        resultsDiv.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSearch);
} else {
    initSearch();
}

document.addEventListener("turbo:load", initSearch);

// ============================================================================
// 🎬 PARTIE 2 — Recherche TMDB dans admin/seance/new (AMÉLIORÉE + FIX FERMETURE)
// ============================================================================
document.addEventListener("DOMContentLoaded", () => {
    const input = document.querySelector("#tmdb_search");
    const resultsBox = document.querySelector("#tmdb_results");
    const hiddenTmdbId = document.querySelector("#seance_tmdb_id");

    if (!input || !resultsBox) return;

    let timer = null;
    let isSelecting = false; // 🟦 empêche la réouverture automatique

    input.addEventListener("input", () => {
        if (isSelecting) return; // 🟦 ignore quand on vient de sélectionner un film

        const query = input.value.trim();
        clearTimeout(timer);

        if (query.length < 2) {
            resultsBox.innerHTML = "";
            resultsBox.style.display = "none";
            return;
        }

        timer = setTimeout(() => {
            fetch(`/api/tmdb/search?query=${encodeURIComponent(query)}`)
                .then((res) => res.json())
                .then((data) => {
                    resultsBox.innerHTML = "";

                    if (!data.length) {
                        resultsBox.style.display = "none";
                        return;
                    }

                    data.forEach((movie) => {
                        const item = document.createElement("div");
                        item.classList.add("tmdb-item");

                        const poster = movie.poster_path
                            ? `https://image.tmdb.org/t/p/w92${movie.poster_path}`
                            : "https://via.placeholder.com/92x138?text=No+Image";

                        const year =
                            movie.release_date?.substring(0, 4) ||
                            movie.year ||
                            "";

                        item.innerHTML = `
                            <img src="${poster}" alt="">
                            <div>
                                <strong>${movie.title}</strong><br>
                                <small>${year}</small>
                            </div>
                        `;

                        // 🟦 CLIC SUR LE FILM
                        item.addEventListener("click", () => {
                            input.value = movie.title;
                            hiddenTmdbId.value = movie.id;

                            document.getElementById("film_titre").value =
                                movie.title;
                            document.getElementById("film_annee").value =
                                movie.release_date?.substring(0, 4) || "";
                            document.getElementById("film_poster").value =
                                movie.poster_path || "";
                            document.getElementById("film_synopsis").value =
                                movie.overview || "";

                            resultsBox.style.display = "none"; // 🔥 fermeture OK maintenant
                        });

                        resultsBox.appendChild(item);
                    });

                    resultsBox.style.display = "block";
                });
        }, 300);
    });
});
