<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Scoring</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
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
    <h1>Scoring de votre emplacement</h1>
    <section>
        <h2>1 - Le bassin de population et sa situation financière</h2>
        <p>La population située à 15 minutes en voiture de votre emplacement s'élève à @php $population = array_reduce($datas, function($carry, $item){return $carry + $item->population;},0); echo($population); @endphp personnes.</p>
        <div class="population">
        @foreach ($datas as $city)
            <div class="card-city" style="@if($city->code_insee == $code_insee) border:3px solid var(--accent) @endif">
                <h3>{{ $city->name }}</h3>
                <div> 
                  @if(count($city->nearby_organic_vegetable_farms))<span class="icon"><img src="./../img/farmer_icon.png" alt="farmer-icon" title="Maraicher Bio présent sur la commune">x{{ count($city->nearby_organic_vegetable_farms[0]) }}</span>@endif 
                  @if(count($city->restaurants))<span class="icon"><img src="./../img/restaurant_icon.png" alt="restaurant-icon" title="Restaurant présent sur la commune">x{{ count($city->restaurants[0]) }}</span>@endif 
                  @if(count($city->marketplaces))<span class="icon"><img src="./../img/market_icon.png" alt="market-icon" title="Marché présent sur la commune">x{{ count($city->marketplaces[0]) }}</span>@endif</div>
                <ul>
                    <li>Durée : {{ $city->limit_duration/60 }} min</li>
                    <li>Population : {{ $city->population }}</li>
                    <li>Nb de foyers : {{ $city->incoming_tax[0][0]->number_of_taxable_households }}</li>
                    <li>Code insee : {{ $city->code_insee }}</li>
                    <li>Score foyers imposables : <span class="confidence-level" style="background-color: {{ $city->scoring_incoming_tax['scoring_percent_taxable_households_color'] }}"> {{ $city->scoring_incoming_tax['scoring_percent_taxable_households'] }}</span></li>
                    <li>Score salaires : <span class="confidence-level" style="background-color: {{ $city->scoring_incoming_tax['scoring_average_salary_tax_color'] }}"> {{ $city->scoring_incoming_tax['scoring_average_salary_tax'] }} </span></li>
                    <li>Score retraites/pensions : <span class="confidence-level" style="background-color: {{ $city->scoring_incoming_tax['scoring_average_pension_tax_color'] }}">{{ $city->scoring_incoming_tax['scoring_average_pension_tax'] }}</span></li>
                </ul>
            </div>
        @endforeach
        </div>
    </section>
  </div>
</body>
</html>