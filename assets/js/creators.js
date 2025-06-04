document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Charger les données des créateurs
        const response = await fetch('/assets/data/content.json');
        const data = await response.json();
        
        const creatorsList = document.getElementById('creators-list');
        
        if (data.creators && data.creators.length > 0) {
            data.creators.forEach(creator => {
                const creatorCard = document.createElement('div');
                creatorCard.className = 'creator-card';
                creatorCard.innerHTML = `
                    <div class="creator-image">
                        <img src="${creator.image}" alt="${creator.nom}" loading="lazy">
                    </div>
                    <div class="creator-info">
                        <h3>${creator.nom}</h3>
                        <p>${creator.description}</p>
                        <div class="creator-social">
                            ${creator.reseaux.youtube ? `<a href="${creator.reseaux.youtube}" target="_blank" rel="noopener noreferrer" class="social-link youtube">YouTube</a>` : ''}
                            ${creator.reseaux.twitch ? `<a href="${creator.reseaux.twitch}" target="_blank" rel="noopener noreferrer" class="social-link twitch">Twitch</a>` : ''}
                        </div>
                    </div>
                `;
                creatorsList.appendChild(creatorCard);
            });
        } else {
            creatorsList.innerHTML = '<p class="no-creators">Aucun créateur disponible pour le moment.</p>';
        }
    } catch (error) {
        console.error('Erreur lors du chargement des créateurs:', error);
        document.getElementById('creators-list').innerHTML = '<p class="error">Erreur lors du chargement des créateurs.</p>';
    }
}); 