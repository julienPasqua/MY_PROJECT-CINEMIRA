// assets/app.js
import "./bootstrap.js";  // ← Décommenté
import "./styles/app.css";

console.log("🎬 CineMira JS chargé !");

// Fonction d'initialisation
function initSearch() {
    console.log("🔍 Tentative d'initialisation...");

    const form = document.getElementById("searchForm");
    const input = document.getElementById("searchInput");
    const resultsDiv = document.getElementById("searchResults");

    console.log("Form:", form);
    console.log("Input:", input);
    console.log("Results div:", resultsDiv);

    if (!form || !input || !resultsDiv) {
        console.log(
            "⚠️ Éléments absents (normal si pas sur la page de recherche)"
        );
        return;
    }

    console.log("✅ Éléments trouvés !");

    // Retirer les anciens listeners si présents (évite les doublons)
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);

    const newInput = newForm.querySelector("#searchInput");
    const finalForm = newForm;

    let timeout = null;

    // Submit du formulaire
    finalForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("🔎 Submit:", newInput.value);
        searchMovies(newInput.value);
    });

    // Recherche en temps réel
    newInput.addEventListener("input", function () {
        const query = newInput.value.trim();
        console.log("⌨️ Frappe clavier:", query);

        if (query.length < 2) {
            resultsDiv.innerHTML = "";
            return;
        }

        clearTimeout(timeout);
        timeout = setTimeout(() => {
            console.log("⏱️ Debounce terminé, lancement recherche");
            searchMovies(query);
        }, 300);
    });

    async function searchMovies(query) {
        if (!query || query.length < 2) {
            resultsDiv.innerHTML = "";
            return;
        }

        console.log("🌐 Appel API pour:", query);

        // Indicateur de chargement
        resultsDiv.innerHTML =
            '<div class="text-center mt-3"><div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div></div>';

        try {
            const url = `/api/tmdb/search?query=${encodeURIComponent(query)}`;
            console.log("📡 URL complète:", url);

            const response = await fetch(url);
            console.log("📥 Réponse reçue:", response.status);

            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

            const movies = await response.json();
            console.log("📽️ Nombre de films:", movies.length);

            displayResults(movies);
        } catch (error) {
            console.error("❌ Erreur complète:", error);
            resultsDiv.innerHTML =
                '<div class="alert alert-danger mt-3">Erreur lors de la recherche</div>';
        }
    }

    function displayResults(movies) {
        if (!movies || movies.length === 0) {
            resultsDiv.innerHTML =
                '<div class="alert alert-info mt-3">Aucun film trouvé</div>';
            return;
        }

        console.log("🎨 Affichage des résultats");

        let html = '<div class="list-group mt-3">';

        movies.forEach((movie) => {
            const title = movie.title || movie.name || "Sans titre";
            const overview = movie.overview || "Pas de description disponible";
            const posterPath = movie.poster_path
                ? `https://image.tmdb.org/t/p/w200${movie.poster_path}`
                : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="300"%3E%3Crect fill="%23333" width="200" height="300"/%3E%3Ctext fill="%23999" font-family="Arial" font-size="18" x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle"%3EPas d\'image%3C/text%3E%3C/svg%3E';

            const rating = movie.vote_average
                ? movie.vote_average.toFixed(1)
                : "N/A";

            html += `
                <div class="list-group-item list-group-item-action">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="${posterPath}" alt="${escapeHtml(title)}" class="img-fluid rounded">
                        </div>
                        <div class="col-md-10">
                            <h5 class="mb-1">${escapeHtml(title)}</h5>
                            <p class="mb-1 text-muted">${escapeHtml(overview.substring(0, 200))}${overview.length > 200 ? "..." : ""}</p>
                            <small class="text-warning">⭐ ${rating}/10</small>
                        </div>
                    </div>
                </div>
            `;
        });

        html += "</div>";
        resultsDiv.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return "";
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
}

// 🎯 Initialisation
if (document.readyState === "loading") {
    console.log("⏳ DOM en cours de chargement...");
    document.addEventListener("DOMContentLoaded", initSearch);
} else {
    console.log("⚡ DOM déjà prêt, init immédiate");
    initSearch();
}

// Gestion Turbo
document.addEventListener("turbo:load", () => {
    console.log("🔄 Turbo:load");
    initSearch();
});