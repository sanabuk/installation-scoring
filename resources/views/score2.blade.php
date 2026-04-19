<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Installation Scoring</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  @vite(['resources/css/style.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
</head>

<body>
<div class="container">

  <!-- HEADER -->
  <header>
    <div class="logo">
      <a class="mark" href="/">IS</a>
      <div>
        <div style="font-weight:700">Installation Scoring</div>
        <div style="font-size:13px;color:var(--muted)">
          Analyse marché — maraîchage bio
        </div>
      </div>
    </div>

    <nav>
      <a class="btn-primary" href="#Essayer">Nouvelle analyse</a>
    </nav>
  </header>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-card">
      <h1>Votre potentiel d’installation 🌱</h1>

        <p class="lead">
        Analyse intelligente du marché local pour maximiser vos chances de réussite.
        </p>

      <div class="feature-list">

        <div class="feature">

        <div>
            <strong>👥 Population cible</strong>
            <div class="badge badge-blue">{{ number_format($datas->scoring->population_totale, 0, ',', ' ') }}</div>
        </div>

        </div>

        <div class="feature">
        <div>
            <strong>💰 Potentiel économique local</strong>
            <div class="badge badge-orange">
            {{ $datas->scoring->demande_locale }}
            </div>
        </div>
        </div>

        <div class="feature">
        <div>
            <strong>🌱 Intensité concurrentielle</strong>
            <div class="badge badge-green">
            {{ $datas->scoring->concurrence }}
            </div>
        </div>
        </div>

      </div>
    </div>

    <!-- SCORE -->
    <aside class="card-aside">
      <h3>Score global</h3>

      <div style="font-size:48px;font-weight:900;color:var(--accent)">
        {{ $datas->weather->globalScore ?? '--' }}
        </div>

        <div style="font-size:14px;color:var(--muted)">Score d’opportunité</div>

      <p style="color:var(--muted)">
        {{ $datas->weather->globalCommentary }}
      </p>
    </aside>
  </section>

  <!-- ANALYSE DETAILLEE -->
  <section>
    <h2>Analyse climatique</h2>

    <div class="card">
      <pre style="white-space:pre-line">
        {{ $datas->weather->commentaries }}
      </pre>

      <p><strong>{{ $datas->weather->globalConfidence }}</strong></p>
      <div class="chart-container">
          <h2>Données climatiques</h2>
          <button onclick="chart.resetZoom()">Reset zoom</button>
          <canvas id="climateChart"></canvas>
        </div>
    </div>
  </section>

  <!-- VILLES -->
<section>
  <h2>Analyse par commune</h2>

  <div class="grid-3">

    @foreach($datas->cities as $city)

      @php
        $farms = $city->nearby_organic_vegetable_farms[0] ?? [];
        $amaps = $city->amap[0] ?? [];
        $markets = $city->marketplaces[0] ?? [];
        $restaurants = $city->restaurants[0] ?? [];

        $nbFarms = count($farms);
      @endphp

      <div class="card city-card">

        <!-- HEADER -->
        <div style="display:flex;justify-content:space-between;align-items:center">
          <strong>{{ $city->name }}</strong>
          <span class="badge badge-green">
            {{ $nbFarms }} fermes
          </span>
        </div>

        <!-- INFOS RAPIDES -->
        <div style="margin-top:10px;font-size:14px">
          👥 {{ number_format($city->population, 0, ',', ' ') }} habitants
        </div>

        <!-- BOUTON ACCORDÉON -->
        <button class="accordion-btn" onclick="toggleAccordion(this)">
          Voir le détail ↓
        </button>

        <!-- ACCORDÉON -->
        <div class="accordion-content">

          <!-- 🌱 FERMES -->
          <div class="accordion-block">
            <h4>🌱 Maraîchers bio installés</h4>

            @forelse($farms as $farm)
              <div class="mini-card">
                <strong>{{ $farm->name_annuaire }}</strong><br>
                @if($farm->responsable)
                   🧑‍🌾 {{ $farm->responsable }}<br>
                @endif

                📍 {{ $farm->city2 ?? $city->name }}<br>

                @if($farm->phone1 || $farm->phone2)
                  📞 {{ $farm->phone1 ?? $farm->phone2 }}<br>
                @endif

                {{-- @if(!$farm->email)
                  ✉️ {{ $farm->email }}<br>
                @endif --}}

                @if($farm->url)
                  🌐 <a href="{{ $farm->url }}" target="_blank">Site</a><br>
                @endif

                {{-- @if($farm->opening_hours)
                  🕒 {{ $farm->opening_hours }}
                @endif --}}
              </div>
            @empty
              <p class="muted">Aucune ferme détectée</p>
            @endforelse
          </div>

          <!-- 🥕 AMAPS -->
          <div class="accordion-block">
            <h4>🥕 AMAP</h4>

            @forelse($amaps as $amap)
              <div class="mini-card">
                <strong>{{ $amap->name }}</strong><br>
                📍 {{ $amap->address ?? 'Non renseigné' }}<br>
                ℹ️ {{ $amap->infos ?? 'Non renseigné' }}<br>
              </div>
            @empty
              <p class="muted">Aucune AMAP</p>
            @endforelse
          </div>

          <!-- 🛒 MARCHÉS -->
          <div class="accordion-block">
            <h4>🛒 Marchés</h4>

            @forelse($markets as $market)
              <div class="mini-card">
                <strong>{{ $market->name }}</strong><br>
                📍 {{ $market->address ?? '' }}<br>
                @if($market->website)
                🕒 <a href="{{ $market->website}}" target="_blank">Site web</a>
                @endif
              </div>
            @empty
              <p class="muted">Aucun marché</p>
            @endforelse
          </div>

          <!-- 🍽️ RESTAURANTS -->
          <div class="accordion-block">
            <h4>🍽️ Restaurants</h4>

            @forelse($restaurants as $resto)
              <div class="mini-card">
                <strong>{{ $resto->name??'' }}</strong><br>
                📍 {{ $resto->address ?? '' }}<br>
                ☎️ {{ $resto->phone ?? '' }}<br>
              </div>
            @empty
              <p class="muted">Aucun restaurant</p>
            @endforelse
          </div>

        </div>

      </div>

    @endforeach

  </div>
</section>

  {{-- <!-- CONCURRENCE -->
  <section>
    <h2>Concurrence locale</h2>

    <div class="card">
      @php $hasFarms = false; @endphp

      @foreach($datas->cities as $city)
        @foreach($city->nearby_organic_vegetable_farms[0] ?? [] as $farm)
          @php $hasFarms = true; @endphp

          <div style="margin-bottom:10px">
            🌱 <strong>{{ $farm->name_annuaire }}</strong><br>
            {{ $city->name }}<br>
            @if($farm->vente_particuliers) Vente particuliers<br>@endif
            @if($farm->vente_pros_details) Vente pros<br>@endif
          </div>
        @endforeach
      @endforeach

      @if(!$hasFarms)
        <p style="color:var(--muted)">Aucune concurrence significative détectée</p>
      @endif
    </div>
  </section>

  <!-- ANNUAIRE COMPLET -->
  <section>
    <h2>Annuaire des contacts locaux</h2>

    <div class="card">

      <input 
        type="text" 
        id="searchContact" 
        placeholder="Rechercher un contact..."
        class="input"
        style="margin-bottom:15px"
      />

      <div id="contactsList">

        @foreach($datas->cities as $city)
          @foreach($city->nearby_organic_vegetable_farms[0] ?? [] as $farm)

            <div class="contact-card" style="margin-bottom:12px">

              <strong>{{ $farm->name_annuaire }}</strong><br>

              👤 {{ $farm->responsable ?? 'Non renseigné' }}<br>

              📍 {{ $farm->city2 ?? $city->name }}<br>

              @if($farm->phone1)
                📞 {{ $farm->phone1 }}<br>
              @endif

              @if($farm->url)
                🌐 <a href="{{ $farm->url }}" target="_blank">Site web</a><br>
              @endif

              <div style="font-size:12px;color:var(--muted)">
                Distance : {{ round($farm->distance) }} m
              </div>

            </div>

          @endforeach
        @endforeach

      </div>
    </div>
  </section> --}}


<script>
    function toggleAccordion(button) {
        const content = button.nextElementSibling;

        content.classList.toggle('open');

        button.textContent = content.classList.contains('open')
            ? 'Réduire ↑'
            : 'Voir le détail ↓';
    }
    async function loadWeather() {

        const data = @json($weather_datas)

        const labels = data.daily.time;

        const tempMax = data.daily.temperature_2m_max;
        const tempMin = data.daily.temperature_2m_min;
        const tempMean = data.daily.temperature_2m_mean;
        const sunshineDuration = data.daily.sunshine_duration.map(v => v / 3600);

        const rain = data.daily.precipitation_sum;

        // Graphique climatiques

        window.chart = new Chart(document.getElementById('climateChart'), {

          data: {
              labels: labels,
              datasets: [

                  {
                      type: 'line',
                      label: 'Température moyenne',
                      data: tempMean,
                      borderColor: 'orange',
                      borderWidth: 3,
                      pointRadius: 0,
                      yAxisID: 'yTemp'
                  },

                  {
                      type: 'line',
                      label: 'Température max',
                      data: tempMax,
                      borderColor: 'red',
                      borderWidth: 1.5,
                      pointRadius: 0,
                      yAxisID: 'yTemp'
                  },

                  {
                      type: 'line',
                      label: 'Température min',
                      data: tempMin,
                      borderColor: 'blue',
                      borderWidth: 1.5,
                      pointRadius: 0,
                      yAxisID: 'yTemp'
                  },

                  {
                      type: 'line',
                      label: 'Ensoleillement (heures)',
                      data: sunshineDuration,
                      borderColor: 'gold',
                      borderWidth: 1.5,
                      pointRadius: 0,
                      yAxisID: 'yTemp'
                  },

                  {
                      type: 'bar',
                      label: 'Précipitations (mm)',
                      data: rain,
                      backgroundColor: 'rgba(0,120,255,0.4)',
                      borderColor: 'rgba(0,120,255,0.8)',
                      yAxisID: 'yRain'
                  }

              ]
          },

          options: {

              responsive: true,
              maintainAspectRatio: false,

              interaction: {
                  mode: 'index',
                  intersect: false
              },

              scales: {

                  yTemp: {
                      type: 'linear',
                      position: 'left',
                      title: {
                          display: true,
                          text: 'Température (°C)'
                      }
                  },

                  yRain: {
                      type: 'linear',
                      position: 'right',
                      title: {
                          display: true,
                          text: 'Précipitations (mm)'
                      },

                      grid: {
                          drawOnChartArea: false
                      }
                  }
              },

              plugins: {
                  zoom: {

                      pan: {
                          enabled: true,
                          mode: 'x'
                      },

                      zoom: {
                          wheel: {
                              enabled: true
                          },

                          pinch: {
                              enabled: true
                          },

                          drag: {
                              enabled: true,
                              backgroundColor: 'rgba(0,0,0,0.2)'
                          },

                          mode: 'x'
                      }
                  }
              }
          }

      });

    }
    loadWeather();
  </script>


</body>
</html>