<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Installation Scoring — Analyse de marché pour fermes maraîchères bio</title>
  <meta name="description" content="Installation Scoring centralise les données locales, automatise les recherches et propose des analyses pour guider l'installation d'une ferme maraîchère bio." />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  @vite(['resources/css/style.css', 'resources/js/app.js'])
</head>
<body>
  <div class="container">
    <header>
      <div class="logo">
        <a class="mark" href="/">IS</a>
        <div>
          <div style="font-weight:700">Installation Scoring</div>
          <div style="font-size:13px;color:var(--muted)">Analyse marché — fermes maraîchères bio</div>
        </div>
      </div>

      <nav>
        <a class="btn-ghost">Fonctionnalités</a>
        {{-- <a class="btn-ghost">Tarifs</a>
        <a class="btn-ghost">Témoignages</a> --}}
        <a class="btn-primary" href="#Essayer">Essayer gratuitement</a>
      </nav>
    </header>

    <!-- Hero -->
    <section class="hero">
      <div class="hero-card">
        <span class="eyebrow">Pour porteurs de projet</span>
        <h1>Choisissez l’emplacement idéal pour votre ferme maraîchère bio</h1>
        <p class="lead">Installation Scoring centralise les données locales, automatise la recherche et génère des analyses claires pour minimiser les risques et maximiser les opportunités d'implantation.</p>

        <div class="feature-list">
          <div class="feature">
            <!-- svg leaf -->
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 20c4-6 9-10 16-12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <div><strong>Données locales</strong><div style="font-size:13px;color:var(--muted)">Concurrence, demande, profils clients</div></div>
          </div>

          <div class="feature">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            <div><strong>Analyses automatiques</strong><div style="font-size:13px;color:var(--muted)">Rapports prêts à l'emploi</div></div>
          </div>

          <div class="feature">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            <div><strong>Visualisation cartographique</strong><div style="font-size:13px;color:var(--muted)">Risques & opportunités</div></div>
          </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:12px;align-items:center">
          <button class="btn-primary">Commencer l'analyse gratuite</button>
          <a href="#how" style="color:var(--muted);text-decoration:underline">Voir comment ça marche</a>
        </div>

        <div style="display:flex;gap:18px;margin-top:20px;align-items:center">
          <div class="kpi">
            <div>
              <strong>+120</strong>
              <small>régions analysées</small>
            </div>
          </div>

          <div class="kpi">
            <div>
              <strong>85%</strong>
              <small>projets facilités</small>
            </div>
          </div>
        </div>
      </div>

      <aside class="card-aside">
        <h3>Tableau de bord — aperçu</h3>
        <p style="color:var(--muted)">Un tableau de bord clair présente : score d'implantation, concurrence, carte de chaleur, demande locale et recommandations.</p>

        <div style="margin-top:18px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div style="font-size:13px;color:var(--muted)">Score d'implantation</div>
            <div style="font-weight:700;color:var(--accent)">72 / 100</div>
          </div>

          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div style="font-size:13px;color:var(--muted)">Demande locale</div>
            <div style="font-weight:700;color:var(--soil)">Forte</div>
          </div>

          <div style="display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:13px;color:var(--muted)">Concurrence</div>
            <div style="font-weight:700;color:var(--muted)">Modérée</div>
          </div>
        </div>

        <div style="margin-top:18px">
          <small style="color:var(--muted)">Export PDF • Rapport complet • Cartes imprimables</small>
        </div>
      </aside>
    </section>

    <!-- How it works -->
    <section id="how" class="how">
      <h2 style="font-size:26px;color:var(--soil);margin-bottom:18px">Comment ça marche</h2>
      <div class="steps">
        <div class="step">
          <strong>1. Saisie du périmètre</strong>
          <p>Définissez l'emplacement de votre future ferme et l'outil agrège automatiquement les sources publiques et privées pertinentes.</p>
        </div>

        <div class="step">
          <strong>2. Analyse automatisée</strong>
          <p>Scoring d'implantation, comparaison de la concurrence, estimation de la demande et cartographie des risques.</p>
        </div>

        <div class="step">
          <strong>3. Recommandations</strong>
          <p>Priorisation des parcelles, scénarios d'implantation et checklists opérationnelles pour démarrer rapidement.</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section id="Essayer" class="cta">
      <div class="cta-left" style="flex:1">
        <h2>Prêt à valider votre projet ?</h2>
        <p style="margin:0;color:var(--muted)">Choisissez un emplacement sur la carte, renseignez votre email puis recevez sur celui ci votre analyse démo dans quelques instants.</p>
        <div id="map" style="height:300px;margin-top:14px;border-radius:12px;overflow:hidden"></div>
      </div>
      <div style="flex:1;display:flex;flex-direction:column;gap:12px">
        <form id="demoForm" method="POST" action="./start-scoring">
          @csrf
          <input class="input" id="email" name="email" type="email" placeholder="Votre email" required />
          <input type="hidden" id="lat" name="lat" required/>
          <input type="hidden" id="lon" name="lon" required/>
          <button class="btn-primary" type="submit">Recevoir ma démo</button>
        </form>
        <p id="status" style="color:var(--muted);font-size:14px"></p>
      </div>
    </section>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
      const map = L.map('map').setView([46.6, 1.88], 6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
      }).addTo(map);

      let marker;
      map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        document.getElementById('lat').value = lat;
        document.getElementById('lon').value = lng;
        if (marker) marker.setLatLng([lat, lng]);
        else marker = L.marker([lat, lng]).addTo(map);
      });

      const form = document.querySelector('#demoForm');
      const submitBtn = form.querySelector("button");
      form.addEventListener('submit', async (e) => {
        submitBtn.disabled = true;
        e.preventDefault();
        const email = document.getElementById('email').value;
        const lat = document.getElementById('lat').value;
        const lon = document.getElementById('lon').value;
        const status = document.getElementById('status');
        console.log("LAT : ",lat);
        if(!email){
          status.textContent = "Veuillez renseigner un email valide.";
          submitBtn.disabled = false;
          return;
        }
        if (!lat || !lon) {
          status.textContent = "Veuillez cliquer sur la carte pour choisir un emplacement.";
          submitBtn.disabled = false;
          return;
        }

        status.textContent = "Envoi en cours...";
        form.submit();
        
      });
    </script>

    <!-- Extras : testimonials + features -->
    <div class="grid-2">
      <div>
        <h3 style="color:var(--soil)">Ce que vous obtenez</h3>
        <ul style="color:var(--muted);line-height:1.8;margin-top:10px">
          <li>Score d'implantation et rapport PDF</li>
          <li>Cartes interactives et exportables</li>
          <li>Analyse de la concurrence et segmentation clients</li>
          <li>Checklists opérationnelles et estimations de coûts</li>
        </ul>
      </div>

      <div>
        <h3 style="color:var(--soil)">Témoignages</h3>
        <div class="testimonial" style="margin-top:12px">
          <strong>Anaïs, porteuse de projet</strong>
          <p style="margin:6px 0;color:var(--muted)">"Grâce à Installation Scoring j'ai choisi un emplacement qui correspond réellement à ma clientèle — économie de temps et d'argent."</p>
        </div>

        <div class="testimonial" style="margin-top:12px">
          <strong>Julien, maraîcher</strong>
          <p style="margin:6px 0;color:var(--muted)">"Les cartes de chaleur et le scoring m'ont aidé à prioriser des parcelles et optimiser la logistique."</p>
        </div>
      </div>
    </div>

    <footer>
      <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
        <div>© <strong>Installation Scoring</strong> — Tous droits réservés</div>
        <div style="color:var(--muted)">Contact • Confidentialité • Mentions légales</div>
      </div>
    </footer>

  </div>
</body>
</html>
