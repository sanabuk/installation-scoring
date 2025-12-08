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
        <h2>1 - Le bassin de population</h2>
        <p>La population située à 15 minutes en voiture de votre emplacement s'élève à xxxx personnes.</p>
        <div class="population">
        @foreach ($datas as $city)
            <div class="card-city" style="@if($city->code_insee == $code_insee) border:3px solid var(--accent) @endif">
                <h3 style="color:var(--soil)">{{ $city->name }}</h3>
                <ul>
                    <li>Durée : {{ $city->limit_duration/60 }} min</li>
                    <li>Population : {{ $city->population }}</li>
                    <li>Nb de foyers : {{ $city->incoming_tax[0][0]->number_of_taxable_households }}</li>
                    <li>Code insee : {{ $city->code_insee }}</li>
                    <li>Score foyers imposables : {{ $city->scoring_incoming_tax['scoring_percent_taxable_households'] }}</li>
                    <li>Score salaires : {{ $city->scoring_incoming_tax['scoring_average_salary_tax'] }}</li>
                    <li>Score retraites/pensions : {{ $city->scoring_incoming_tax['scoring_average_pension_tax'] }}</li>
                </ul>
            </div>
        @endforeach
        </div>
    </section>
  </div>
</body>
</html>