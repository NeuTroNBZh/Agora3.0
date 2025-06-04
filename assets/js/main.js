// Navigation responsive
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu mobile
    const navLinks = document.querySelector('.nav-links');
    const logo = document.querySelector('.logo');

    // Ajouter un bouton hamburger pour mobile
    const hamburger = document.createElement('button');
    hamburger.classList.add('hamburger');
    hamburger.innerHTML = '☰';
    logo.appendChild(hamburger);

    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    // Fermer le menu mobile lors du clic sur un lien
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
        });
    });

    // Animation smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Menu déroulant mobile : clic sur parent ouvre/ferme le sous-menu
    document.querySelectorAll('.nav-links .has-dropdown > a').forEach(parentLink => {
        parentLink.addEventListener('click', function(e) {
            if (window.innerWidth <= 900) {
                e.preventDefault();
                const dropdown = this.nextElementSibling;
                const parentLi = this.parentElement;
                if (dropdown) {
                    const isOpen = dropdown.style.display === 'block';
                    dropdown.style.display = isOpen ? 'none' : 'block';
                    if (isOpen) {
                        parentLi.classList.remove('open');
                    } else {
                        parentLi.classList.add('open');
                    }
                }
            }
        });
    });

    loadLatestYouTubeVideo();
    loadContent();
});

// Gestion du header lors du scroll
window.addEventListener('scroll', function() {
    const header = document.querySelector('header');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// === YouTube API: Affichage automatique de la dernière vidéo ===
// Remplacez par votre propre clé API YouTube Data v3
const YOUTUBE_API_KEY = 'AIzaSyCLBRJgzPj5bZwj7D1L98wMj8f3ZTEaIN8';
const CHANNEL_ID = 'UCsHvSmaxUOGttP39HAIVy9w'; // Remplacez par l'ID de la chaîne AHNO
let latestVideoId = null;

function loadLatestYouTubeVideo() {
    console.log('Chargement de la dernière vidéo YouTube...');
    const videoDiv = document.getElementById('youtube-latest');
    if (!videoDiv) {
        console.error('Élément youtube-latest non trouvé dans le DOM');
        return;
    }

    // Afficher un état de chargement
    videoDiv.innerHTML = `
        <div class="video-loading">
            <div class="loading-spinner"></div>
            <p>Chargement de la vidéo...</p>
        </div>
    `;

    fetch(`https://www.googleapis.com/youtube/v3/search?key=${YOUTUBE_API_KEY}&channelId=${CHANNEL_ID}&part=snippet,id&order=date&maxResults=5`)
        .then(response => {
            console.log('Réponse reçue:', response.status);
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Données reçues:', data);
            if (data.error) {
                throw new Error(`Erreur API YouTube: ${data.error.message}`);
            }
            if (data.items && data.items.length > 0) {
                const videoItem = data.items.find(item => item.id && item.id.kind === 'youtube#video' && item.id.videoId);
                if (videoItem) {
                    console.log('Vidéo trouvée:', videoItem);
                    const videoId = videoItem.id.videoId;
                    const snippet = videoItem.snippet;
                    const thumbnail = snippet.thumbnails.high.url;
                    const title = snippet.title;
                    
                    videoDiv.innerHTML = `
                        <div class="video-embed" id="latest-video-thumbnail" style="cursor:pointer;position:relative;">
                            <img src="${thumbnail}" alt="${title}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                            <span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.7);color:#ffe082;padding:16px 22px;border-radius:50%;font-size:2.2rem;box-shadow:0 2px 12px #000a;pointer-events:none;">▶</span>
                        </div>
                    `;
                    document.getElementById('latest-video-thumbnail').addEventListener('click', function() {
                        openVideoModal(videoId, snippet);
                    });
                } else {
                    throw new Error('Aucune vidéo trouvée dans les résultats');
                }
            } else {
                throw new Error('Aucun résultat reçu de l\'API YouTube');
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement de la vidéo:', error);
            videoDiv.innerHTML = `
                <div class="video-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Impossible de charger la vidéo</p>
                    <a href="https://www.youtube.com/@AHNO-fr" target="_blank" class="btn btn-youtube">
                        Voir la chaîne YouTube
                    </a>
                </div>
            `;
        });
}

// === Gestion des actualités ===
let allNews = [];

function filterNews(searchTerm = '', category = '') {
    return allNews.filter(news => {
        const matchesSearch = searchTerm === '' || 
            news.titre.toLowerCase().includes(searchTerm.toLowerCase()) ||
            news.excerpt.toLowerCase().includes(searchTerm.toLowerCase());
        
        const matchesCategory = category === '' || news.categorie === category;
        
        return matchesSearch && matchesCategory;
    });
}

function displayNews(news) {
    const newsGrid = document.querySelector('.news-grid');
    if (!newsGrid) return;

    newsGrid.innerHTML = news.map(item => {
        // Limite l'extrait à 300 caractères (sans couper le HTML)
        const fullExcerpt = item.excerpt;
        let shortExcerpt = fullExcerpt;
        let needsReadMore = false;
        // On retire les balises HTML pour le calcul de la longueur
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = fullExcerpt;
        const plainText = tempDiv.textContent || tempDiv.innerText || '';
        if (plainText.length > 300) {
            needsReadMore = true;
            // On coupe le texte brut, puis on réinjecte dans une div pour garder le HTML de début
            let cutText = plainText.slice(0, 300);
            // On évite de couper au milieu d'un mot
            cutText = cutText.slice(0, cutText.lastIndexOf(' '));
            shortExcerpt = cutText + '...';
        }
        return `
        <article class="news-card" id="news-${item.id}">
            ${item.image ? `<img src="${item.image}" alt="${item.titre}" class="news-image">` : ''}
            <div class="news-content">
                <div class="news-meta">
                    <span class="news-date">${item.date}</span>
                    ${item.important ? '<span class="news-badge">Important</span>' : ''}
                </div>
                <h2>${item.titre}</h2>
                <p class="news-excerpt" data-full="${encodeURIComponent(fullExcerpt)}" data-short="${encodeURIComponent(shortExcerpt)}" data-state="short">
                    ${needsReadMore ? shortExcerpt : fullExcerpt}
                </p>
                ${item.tags ? `
                    <div class="news-tags">
                        ${item.tags.map(tag => `<span class="news-tag">#${tag}</span>`).join('')}
                    </div>
                ` : ''}
                <div class="news-actions">
                    ${needsReadMore ? `<button class="btn btn-readmore" data-newsid="${item.id}">Lire plus</button>` : `<a href="${item.lien}" class="btn">Lire la suite</a>`}
                </div>
            </div>
        </article>
        `;
    }).join('');

    // Ajout de la gestion du bouton Lire plus/Réduire
    document.querySelectorAll('.btn-readmore').forEach(btn => {
        btn.addEventListener('click', function() {
            const newsId = this.getAttribute('data-newsid');
            const excerptP = document.querySelector(`#news-${newsId} .news-excerpt`);
            if (!excerptP) return;
            const state = excerptP.getAttribute('data-state');
            if (state === 'short') {
                excerptP.innerHTML = decodeURIComponent(excerptP.getAttribute('data-full'));
                excerptP.setAttribute('data-state', 'full');
                this.textContent = 'Réduire';
            } else {
                excerptP.innerHTML = decodeURIComponent(excerptP.getAttribute('data-short'));
                excerptP.setAttribute('data-state', 'short');
                this.textContent = 'Lire plus';
            }
        });
    });
}

function setupNewsFilters() {
    const searchInput = document.getElementById('search-news');
    const categorySelect = document.getElementById('category-filter');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const filteredNews = filterNews(e.target.value, categorySelect.value);
            displayNews(filteredNews);
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', (e) => {
            const filteredNews = filterNews(searchInput.value, e.target.value);
            displayNews(filteredNews);
        });
    }
}

async function loadContent() {
    try {
        const response = await fetch('/assets/data/content.json');
        const data = await response.json();
        
        // Stocker toutes les actualités
        allNews = data.actualites;
        
        // Mise à jour des actualités sur la page d'accueil
        const newsHighlight = document.querySelector('.news-highlight');
        if (newsHighlight && data.actualites.length > 0) {
            const latestNews = data.actualites[0];
            newsHighlight.querySelector('h3').textContent = latestNews.titre;
            newsHighlight.querySelector('.news-excerpt').innerHTML = 
                `${latestNews.excerpt}<br><a href="${latestNews.lien}" class="btn btn-secondary">Lire l'actualité</a>`;
        }

        // Mise à jour de la page actualités si on est sur cette page
        const newsGrid = document.querySelector('.news-grid');
        if (newsGrid) {
            displayNews(data.actualites);
            setupNewsFilters();
        }

        // Mise à jour des événements sur la page d'accueil
        const eventHighlight = document.querySelector('.event-highlight');
        if (eventHighlight && data.evenements.length > 0) {
            const nextEvent = data.evenements[0];
            eventHighlight.querySelector('.event-title').textContent = nextEvent.titre;
            eventHighlight.querySelector('.event-date').textContent = nextEvent.date;
            eventHighlight.querySelector('.event-desc').textContent = nextEvent.description;
        }

        // Mise à jour de la page événements si on est sur cette page
        const eventsList = document.querySelector('.events-list');
        if (eventsList) {
            eventsList.innerHTML = data.evenements.map(event => `
                <div class="event-card">
                    <div class="event-date">
                        <span class="event-day">${event.date}</span>
                    </div>
                    <div class="event-content">
                        <h2>${event.titre}</h2>
                        <p class="event-description">${event.description}</p>
                        <a href="${event.lien}" class="btn btn-secondary">En savoir plus</a>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Erreur lors du chargement du contenu:', error);
    }
}

// === Gestion de la page Vidéos ===
let nextPageToken = '';
let isLoading = false;
let playlistNextPageToken = '';
let isLoadingPlaylists = false;

// Ajout d'une variable pour le tri
let currentSort = 'date';

// Chargement initial des vidéos
async function loadVideos(reset = false, searchQuery = '', sort = 'date') {
    if (isLoading) return;
    isLoading = true;
    if (reset) {
        nextPageToken = '';
        document.querySelector('.videos-grid').innerHTML = '';
    }
    try {
        let url = `https://www.googleapis.com/youtube/v3/search?key=${YOUTUBE_API_KEY}&channelId=${CHANNEL_ID}&part=snippet,id&maxResults=12`;
        if (sort === 'date') url += '&order=date';
        else if (sort === 'views') url += '&order=viewCount';
        else if (sort === 'rating') url += '&order=rating';
        if (nextPageToken) url += `&pageToken=${nextPageToken}`;
        if (searchQuery) url += `&q=${encodeURIComponent(searchQuery)}`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.nextPageToken) {
            nextPageToken = data.nextPageToken;
            document.querySelector('.load-more').style.display = 'block';
        } else {
            document.querySelector('.load-more').style.display = 'none';
        }
        const videoIds = data.items.map(item => item.id.videoId).join(',');
        if (!videoIds) return;
        const videoDetails = await fetch(`https://www.googleapis.com/youtube/v3/videos?key=${YOUTUBE_API_KEY}&id=${videoIds}&part=contentDetails,statistics`);
        const detailsData = await videoDetails.json();
        displayVideos(data.items, detailsData.items);
    } catch (error) {
        console.error('Erreur lors du chargement des vidéos:', error);
    } finally {
        isLoading = false;
    }
}

// Affichage des vidéos
function displayVideos(videos, details) {
    const grid = document.querySelector('.videos-grid');
    
    videos.forEach((video, index) => {
        const videoDetail = details[index];
        const duration = formatDuration(videoDetail.contentDetails.duration);
        const views = formatViews(videoDetail.statistics.viewCount);
        
        const videoCard = document.createElement('div');
        videoCard.className = 'video-card';
        videoCard.innerHTML = `
            <div class="video-thumbnail">
                <img src="${video.snippet.thumbnails.high.url}" alt="${video.snippet.title}">
                <span class="video-duration">${duration}</span>
            </div>
            <div class="video-info">
                <h3>${video.snippet.title}</h3>
                <div class="video-meta">
                    <span>${views} vues</span>
                    <span>${formatDate(video.snippet.publishedAt)}</span>
                </div>
            </div>
        `;
        
        videoCard.addEventListener('click', () => openVideoModal(video.id.videoId, video.snippet));
        grid.appendChild(videoCard);
    });
}

// Chargement des playlists avec pagination
async function loadPlaylists(reset = false) {
    if (isLoadingPlaylists) return;
    isLoadingPlaylists = true;
    if (reset) {
        playlistNextPageToken = '';
        document.querySelector('.playlists-grid').innerHTML = '';
    }
    try {
        let url = `https://www.googleapis.com/youtube/v3/playlists?key=${YOUTUBE_API_KEY}&channelId=${CHANNEL_ID}&part=snippet&maxResults=6`;
        if (playlistNextPageToken) url += `&pageToken=${playlistNextPageToken}`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.nextPageToken) {
            playlistNextPageToken = data.nextPageToken;
            document.querySelector('.load-more-playlists').style.display = 'block';
        } else {
            document.querySelector('.load-more-playlists').style.display = 'none';
        }
        const grid = document.querySelector('.playlists-grid');
        data.items.forEach(playlist => {
            const playlistCard = document.createElement('div');
            playlistCard.className = 'playlist-card';
            playlistCard.innerHTML = `
                <div class="playlist-thumbnail">
                    <img src="${playlist.snippet.thumbnails.high.url}" alt="${playlist.snippet.title}">
                </div>
                <div class="playlist-info">
                    <h3>${playlist.snippet.title}</h3>
                    <p>${playlist.snippet.description.substring(0, 100)}...</p>
                </div>
            `;
            playlistCard.addEventListener('click', () => loadPlaylistVideos(playlist.id));
            grid.appendChild(playlistCard);
        });
    } catch (error) {
        console.error('Erreur lors du chargement des playlists:', error);
    } finally {
        isLoadingPlaylists = false;
    }
}

// Chargement des vidéos d'une playlist
async function loadPlaylistVideos(playlistId) {
    try {
        const response = await fetch(`https://www.googleapis.com/youtube/v3/playlistItems?key=${YOUTUBE_API_KEY}&playlistId=${playlistId}&part=snippet&maxResults=50`);
        const data = await response.json();
        
        const videoIds = data.items.map(item => item.snippet.resourceId.videoId).join(',');
        const videoDetails = await fetch(`https://www.googleapis.com/youtube/v3/videos?key=${YOUTUBE_API_KEY}&id=${videoIds}&part=contentDetails,statistics`);
        const detailsData = await videoDetails.json();
        
        const grid = document.querySelector('.videos-grid');
        grid.innerHTML = '';
        
        displayVideos(data.items.map(item => ({
            id: { videoId: item.snippet.resourceId.videoId },
            snippet: item.snippet
        })), detailsData.items);
    } catch (error) {
        console.error('Erreur lors du chargement des vidéos de la playlist:', error);
    }
}

// Recherche et tri de vidéos (corrigée)
function setupVideoSearch() {
    const searchInput = document.getElementById('search-videos');
    const sortSelect = document.getElementById('sort-filter');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value;
            loadVideos(true, query, currentSort);
        });
    }
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            currentSort = e.target.value;
            const query = searchInput ? searchInput.value : '';
            loadVideos(true, query, currentSort);
        });
    }
}

// Modal de lecture vidéo
function openVideoModal(videoId, snippet) {
    const modal = document.getElementById('video-modal');
    const player = document.getElementById('video-player');
    const titleEl = document.getElementById('modal-video-title');
    const descEl = document.getElementById('modal-video-description');
    const ytLink = document.getElementById('youtube-link');
    // Affiche le lecteur YouTube
    player.innerHTML = `<iframe width="100%" height="100%" src="https://www.youtube.com/embed/${videoId}?autoplay=1" frameborder="0" allowfullscreen></iframe>`;
    if (snippet) {
        titleEl.textContent = snippet.title || '';
        descEl.textContent = snippet.description || '';
        ytLink.href = `https://www.youtube.com/watch?v=${videoId}`;
    }
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Fermeture du modal
function closeVideoModal() {
    const modal = document.getElementById('video-modal');
    const player = document.getElementById('video-player');
    if (modal && player) {
        player.innerHTML = '';
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Ajout des gestionnaires d'événements pour la fermeture de la modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('video-modal');
    const closeBtn = document.querySelector('.close-modal');
    
    if (modal && closeBtn) {
        // Fermeture avec le bouton X
        closeBtn.addEventListener('click', closeVideoModal);
        
        // Fermeture en cliquant en dehors de la modal
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeVideoModal();
            }
        });
        
        // Fermeture avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'block') {
                closeVideoModal();
            }
        });
    }
});

// Partage de vidéo
function shareVideo(videoId, title) {
    const url = `https://www.youtube.com/watch?v=${videoId}`;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).catch(console.error);
    } else {
        navigator.clipboard.writeText(url)
            .then(() => alert('Lien copié dans le presse-papiers !'))
            .catch(console.error);
    }
}

// Utilitaires
function formatDuration(duration) {
    const match = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/);
    const hours = (match[1] || '').replace('H', '');
    const minutes = (match[2] || '').replace('M', '');
    const seconds = (match[3] || '').replace('S', '');
    
    let result = '';
    if (hours) result += hours + ':';
    result += (minutes || '0').padStart(2, '0') + ':';
    result += (seconds || '0').padStart(2, '0');
    
    return result;
}

function formatViews(views) {
    return new Intl.NumberFormat('fr-FR').format(views);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(date);
}

// Initialisation de la page vidéos
if (document.querySelector('.videos-container')) {
    loadVideos(true);
    loadPlaylists(true);
    setupVideoSearch();
    document.getElementById('load-more-btn').addEventListener('click', () => loadVideos());
    document.getElementById('load-more-playlists-btn').addEventListener('click', () => loadPlaylists());
    document.querySelector('.close-modal').addEventListener('click', closeVideoModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeVideoModal();
    });
}
// Ajout pour la page d'accueil : fermeture du modal
if (document.getElementById('video-modal')) {
    document.querySelector('.close-modal').addEventListener('click', closeVideoModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeVideoModal();
    });
}

// === INFOS SERVEUR DISCORD EN DIRECT ===
function loadDiscordServerInfo() {
    const serverId = '1254007638161752177';
    fetch(`https://discord.com/api/guilds/${serverId}/widget.json`)
        .then(res => res.json())
        .then(data => {
            const infoBox = document.querySelector('.community-info ul');
            if (!infoBox) return;
            // Nombre de membres connectés (en ligne)
            const onlineCount = data.presence_count;
            // Dernier membre (le dernier de la liste des membres connectés)
            const lastMember = data.members && data.members.length > 0 ? data.members[data.members.length - 1].username : 'N/A';
            // Nom du serveur
            const serverName = data.name;
            infoBox.innerHTML = `
                <li><strong>Serveur :</strong> ${serverName}</li>
                <li><strong>Membres connectés :</strong> ${onlineCount}</li>
                <li><strong>Dernier membre connecté :</strong> <span style="color:#ffe082;">${lastMember}</span></li>
                <li><strong>Salons actifs :</strong> #général, #jeux, #annonces, #tournois</li>
            `;
        })
        .catch(() => {
            const infoBox = document.querySelector('.community-info ul');
            if (infoBox) infoBox.innerHTML = '<li>Impossible de charger les infos serveur en direct.</li>';
        });
}

if (document.querySelector('.community-info')) {
    loadDiscordServerInfo();
}

// === Pastille dynamique sur Communauté si membres en ligne ===
function updateCommunityBadge() {
    const navLink = document.querySelector('.nav-links a[href="communaute"]');
    if (!navLink) return;
    fetch('https://discord.com/api/guilds/1254007638161752177/widget.json')
      .then(res => res.json())
      .then(data => {
        if (data.presence_count && data.presence_count > 0) {
          let badge = navLink.querySelector('.community-badge');
          if (!badge) {
            badge = document.createElement('span');
            badge.className = 'community-badge';
            navLink.appendChild(badge);
          }
          badge.textContent = data.presence_count;
          badge.style.display = 'inline-block';
        } else {
          const badge = navLink.querySelector('.community-badge');
          if (badge) badge.style.display = 'none';
        }
      })
      .catch(() => {
        const badge = navLink.querySelector('.community-badge');
        if (badge) badge.style.display = 'none';
      });
}
updateCommunityBadge(); 