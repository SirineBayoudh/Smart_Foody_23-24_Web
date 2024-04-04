function loadNextPage(url) {
    fetch(url)
        .then(response => response.text())
        .then(data => {
            // Mettez à jour la section de la page avec les données de la page suivante
            document.getElementById('product-list').innerHTML = data;
        })
        .catch(error => console.error('Une erreur s\'est produite :', error));
}
document.addEventListener('click', function(event) {
    // Vérifiez si l'élément cliqué est un lien de pagination
    if (event.target.matches('.page-link')) {
        // Empêchez le comportement par défaut du lien
        event.preventDefault();
        // Récupérez l'URL de la page suivante à partir de l'attribut href du lien
        var nextPageUrl = event.target.getAttribute('href');
        // Chargez les données de la page suivante via AJAX
        loadNextPage(nextPageUrl);
    }
});

