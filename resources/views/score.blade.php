<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Scoring</title>
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
                    <li>Population : {{ $city->population }}</li>
                    <li>Nb de foyers : {{ $city->incoming_tax[0][0]->number_of_taxable_households }}</li>
                    <li>Code insee : {{ $city->code_insee }}</li>
                </ul>
            </div>
        @endforeach
        </div>
    </section>
  </div>
</body>
</html>