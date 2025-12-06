<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Merci – Votre étude arrive</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <!-- Styles de la landing page (simplifiés mais identiques visuellement) -->
    <style>
        /* SECTION MERCI */
        .merci-container {
            max-width: 700px;
            margin: 90px auto;
            background: white;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            animation: fadeIn 0.5s ease-out;
        }

        /* .merci-container h1 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            color: #2b7d4f;
        } */

        .merci-container p {
            font-size: 1.15rem;
            line-height: 1.6;
        }

        .btn-return {
            display: inline-block;
            margin-top: 35px;
            padding: 14px 28px;
            background: #2b7d4f;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            transition: 0.2s;
        }

        .btn-return:hover {
            background: ;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .merci-container {
                margin: 50px 20px;
                padding: 30px;
            }
        }
    </style>
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

        <!-- MESSAGE DE REMERCIEMENT -->
        <section>
            <div class="merci-container">
                <h1>Merci pour votre demande 🎉</h1>
                <p>
                    Votre étude personnalisée est en cours de génération.<br>
                    Vous allez recevoir un email avec <b>votre rapport complet</b> dans quelques instants.
                </p>

                <a href="/" class="btn-primary">↩ Retour à la page d’accueil</a>
            </div>
        </section>
    </div>

</body>
</html>
