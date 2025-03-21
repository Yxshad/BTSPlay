document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleAdvancedSearch');
    const advancedMenu = document.getElementById('advancedSearchMenu');
    const applyButton = document.getElementById('applyAdvancedSearch');
    const descKeywordInput = document.getElementById('descriptionKeyword');
    const titreKeywordInput = document.getElementById('TitreKeyword');
    const professeurSelect = document.getElementById('professeurReferent');
    const resultsContainer = document.getElementById('resultsContainer');
    const img_toggle = document.getElementById('img_toggle');

    const urlParams = new URLSearchParams(window.location.search);
    const initialKeyword = urlParams.get('motCle') || '';

    chargerProfesseursReferents();

    if (initialKeyword) {
        performSearch();
    }

    toggleButton.addEventListener('click', function() {
        advancedMenu.classList.toggle('visible');
        if (advancedMenu.classList.contains('visible')) {
            const menuHeight = advancedMenu.scrollHeight;
            resultsContainer.style.transform = `translateY(${menuHeight / 4}px)`;
            img_toggle.classList.add("reverse");
        } else {
            resultsContainer.style.transform = 'translateY(0)';
            img_toggle.classList.remove("reverse");
        }
    });

    applyButton.addEventListener('click', function() {
        performSearch();
    });

    descKeywordInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    });

    titreKeywordInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    });

    function chargerProfesseursReferents() {
        fetch('?action=getProfesseursReferents')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de la récupération des professeurs');
                }
                return response.json();
            })
            .then(data => {
                if (Array.isArray(data)) {
                    data.forEach(professeur => {
                        const option = document.createElement('option');
                        option.value = professeur;
                        option.textContent = professeur;
                        professeurSelect.appendChild(option);
                    });

                    const urlProfesseur = urlParams.get('professeurReferent');
                    if (urlProfesseur) {
                        professeurSelect.value = urlProfesseur;
                    }
                } else if (data.error) {
                    console.error('Erreur serveur:', data.error);
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des professeurs:', error);
            });
    }

    function performSearch() {
        const descKeyword = descKeywordInput.value.trim();
        const titreKeyword = titreKeywordInput.value.trim();
        const professeurId = professeurSelect.value;
        const searchParams = new URLSearchParams();

        searchParams.append('ajax', '1');

        if (!descKeyword && !titreKeyword && !professeurId) {
            searchParams.append('motCle', initialKeyword);
        }

        if (descKeyword) {
            searchParams.append('descriptionKeyword', descKeyword);
        }
        if (titreKeyword) {
            searchParams.append('TitreKeyword', titreKeyword);
        }
        if (professeurId) {
            searchParams.append('professeurReferent', professeurId);
        }

        resultsContainer.innerHTML = '<div class="loading">Chargement des résultats...</div>';

        fetch(`?${searchParams.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Réponse du serveur non valide');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                displayResults(data);
                const newUrl = window.location.pathname + `?${searchParams.toString()}`;
                window.history.pushState({}, '', newUrl);
            })
            .catch(error => {
                resultsContainer.innerHTML = `<p class="error">Erreur lors de la recherche: ${error.message}</p>`;
                console.error('Erreur de recherche:', error);
            });
    }

    function displayResults(data) {
        if (!data || data.count === 0) {
            resultsContainer.innerHTML = '<p>Aucun résultat trouvé.</p>';
            return;
        }

        let html = `<h2>Résultats de recherche</h2>`;

        if (data.results.length > 0) {
            html += '<div class="results-list">';

            data.results.forEach(media => {
                html += `
                <div class="result">
                    <h3>${escapeHtml(media.titre || '')}</h3>
                    ${media.nom ? `<p><strong>Nom :</strong> ${escapeHtml(media.nom)}</p>` : ''}
                    ${media.description ? `<p><strong>Description :</strong> ${escapeHtml(media.description)}</p>` : ''}
                    ${media.professeurReferent ? `<p><strong>Professeur référent :</strong> ${escapeHtml(media.professeurReferent)}</p>` : ''}
                </div>`;
            });

            html += '</div>';
            html += `<p>Nombre de résultats : ${data.count}</p>`;
        } else {
            html += '<p>Aucun résultat trouvé.</p>';
        }

        resultsContainer.innerHTML = html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});