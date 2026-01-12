<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vos résultats sont disponibles</title>
</head>

<body style="margin:0;padding:0;background-color:#F6FBF6;font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F6FBF6;padding:30px 10px;">
    <tr>
        <td align="center">

            <!-- Container -->
            <table width="600" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,0.05);">

                <!-- Header -->
                <tr>
                    <td style="background:linear-gradient(90deg,#9CCB6B,#2F7A2F);padding:24px;text-align:center;">
                        <h1 style="margin:0;color:#ffffff;font-size:26px;">Installation Scoring</h1>
                        <p style="margin:6px 0 0;color:#eaf6ea;font-size:14px;">
                            Analyse de marché pour fermes maraîchères bio
                        </p>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:32px;color:#0f172a;">
                        <h2 style="color:#7C5A3A;margin-top:0;">
                            Vos résultats sont prêts 🌱
                        </h2>

                        <p style="font-size:16px;line-height:1.6;color:#64748B;">
                            Bonjour,
                        </p>

                        <p style="font-size:16px;line-height:1.6;color:#64748B;">
                            Votre analyse <strong>Installation Scoring</strong> est maintenant disponible.
                        </p>

                        <!-- Button -->
                        <div style="text-align:center;margin:32px 0;">
                            <a href="{{ $url }}"
                               style="background:#2F7A2F;color:white;text-decoration:none;padding:14px 28px;border-radius:12px;font-weight:bold;display:inline-block;">
                                Accéder à mes résultats
                            </a>
                        </div>

                        <p style="font-size:14px;color:#64748B;line-height:1.6;">
                            Si le bouton ne fonctionne pas, vous pouvez copier ce lien dans votre navigateur :
                        </p>

                        <p style="font-size:14px;color:#2F7A2F;word-break:break-all;">
                            {{ $url }}
                        </p>

                        <p style="font-size:15px;color:#64748B;margin-top:30px;">
                            L’équipe <strong>Installation Scoring</strong>
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#F6FBF6;padding:20px;text-align:center;font-size:13px;color:#94a3b8;">
                        © Installation Scoring — Tous droits réservés
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
