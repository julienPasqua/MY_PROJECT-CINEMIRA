import "./stimulus_bootstrap.js";
import "./bootstrap.js";
import "./styles/app.css";

console.log("🎬 CineMira JS chargé !");

/* ============================================================================
   🔵 PARTIE 1 — Recherche TMDB dans la NAVBAR (PUBLIC)
   ============================================================================ */

function initPublicSearch() {
    const form = document.getElementById("searchForm");
    const input = document.getElementById("searchInput");
    const results = document.getElementById("searchResults");

    if (!form || !input || !results) {
        console.log("⚠️ Composants PUBLIC non trouvés → page sans navbar recherche");
        return;
    }

    console.log("🔵 Recherche PUBLIC TMDB ACTIVÉE");

    let timer = null;

    form.addEventListener("submit", (e) => e.preventDefault());

    input.addEventListener("input", () => {
        const query = input.value.trim();
        clearTimeout(timer);

        if (query.length < 2) {
            results.innerHTML = "";
            return;
        }

        timer = setTimeout(() => {
            fetch(`/api/tmdb/search?query=${encodeURIComponent(query)}`)
                .then((response) => response.json())
                .then((movies) => showPublicResults(movies, results))
                .catch((err) => {
                    console.error("Erreur recherche PUBLIC TMDB :", err);
                });
        }, 300);
    });
}

function showPublicResults(movies, results) {
    if (!movies || movies.length === 0) {
        results.innerHTML =
            "<div class='alert alert-info mt-2'>Aucun résultat</div>";
        return;
    }

    let html = `<div class="list-group mt-2">`;

    movies.forEach((movie) => {
        const poster = movie.poster_path
            ? `https://image.tmdb.org/t/p/w92${movie.poster_path}`
            : "https://via.placeholder.com/92x138?text=No+Image";

        html += `
            <a class="list-group-item list-group-item-action d-flex align-items-start gap-3"
               href="/film/tmdb/${movie.id}">
                
                <img src="${poster}" 
                     class="rounded shadow-sm" 
                     style="width:55px; height:auto">

                <div>
                    <strong>${movie.title}</strong><br>
                    <small>
                        ${(movie.overview || "").substring(0, 90)}...
                    </small>
                </div>
            </a>
        `;
    });

    html += `</div>`;

    results.innerHTML = html;
}


/* ============================================================================
   🟠 PARTIE 2 — Recherche TMDB ADMIN (déjà fonctionnel)
   ============================================================================ */

function initAdminSearch() {
    const input = document.querySelector("#tmdb_search");
    const resultsBox = document.querySelector("#tmdb_results");
    const hiddenId = document.querySelector("#seance_tmdb_id");

    if (!input || !resultsBox || !hiddenId) {
        return; // on n'est pas sur admin/seance/new
    }

    console.log("🟠 Recherche TMDB ADMIN ACTIVÉE");

    let timer = null;

    input.addEventListener("input", () => {
        const query = input.value.trim();
        clearTimeout(timer);

        if (query.length < 2) {
            resultsBox.style.display = "none";
            resultsBox.innerHTML = "";
            return;
        }

        timer = setTimeout(() => {
            fetch(`/api/tmdb/search?query=${encodeURIComponent(query)}`)
                .then((response) => response.json())
                .then((movies) =>
                    showAdminResults(movies, input, hiddenId, resultsBox)
                )
                .catch((err) => console.error("Erreur ADMIN TMDB :", err));
        }, 300);
    });
}

function showAdminResults(movies, input, hiddenId, box) {
    box.innerHTML = "";

    if (!movies || movies.length === 0) {
        box.style.display = "none";
        return;
    }

    movies.forEach((movie) => {
        const div = document.createElement("div");
        div.classList.add("tmdb-item");

        div.innerHTML = `
            <img src="https://image.tmdb.org/t/p/w92${movie.poster_path}">
            <div>
                <strong>${movie.title}</strong><br>
                <small>${movie.release_date?.substring(0, 4) || ""}</small>
            </div>
        `;

        div.addEventListener("click", () => {
            input.value = movie.title;
            hiddenId.value = movie.id;
            box.style.display = "none";
        });

        box.appendChild(div);
    });

    box.style.display = "block";
}

/* ============================================================================
   🚀 INITIALISATION GLOBALE
   ============================================================================ */

function initAll() {
    initPublicSearch();
    initAdminSearch();
}

document.addEventListener("DOMContentLoaded", initAll);
document.addEventListener("turbo:load", initAll);
