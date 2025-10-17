<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel - Expiration de votre domiciliation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #f39c12;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #f8f9fa;
            border-radius: 5px;
            overflow: hidden;
        }
        .info-table th, .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .info-table th {
            background: #e9ecef;
            font-weight: bold;
        }
        .cta-button {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .footer {
            background: #343a40;
            color: #f8f9fa;
            padding: 30px 20px 20px;
            font-size: 14px;
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: center;
            gap: 20px;
            max-width: 700px;
            margin: 0 auto 20px;
            border-bottom: 1px solid #495057;
            padding-bottom: 20px;
        }

        .footer-logo img {
            max-height: 50px;
            max-width: 160px;
            display: block;
            margin: 0 auto;
        }

        .footer-details {
            text-align: left;
            flex: 1;
            min-width: 200px;
        }

        .footer-details a {
            color: #f8f9fa;
            text-decoration: underline;
        }

        .footer-bottom {
            text-align: center;
            font-size: 12px;
            color: #adb5bd;
        }
        .days-remaining {
            font-size: 24px;
            font-weight: bold;
            color: #f39c12;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rappel d'expiration</h1>
            <p>Votre service de domiciliation</p>
        </div>
        
        <div class="content">
            <h2> {{ $client->nom_francais ? 'Cher' : '' }} {{ $client->nom_francais ?? 'Cher client' }},</h2>
            
            <div class="alert-box">
                <strong>Attention :</strong> Votre service de domiciliation expire bientôt !
            </div>

            <div class="days-remaining">
                Il vous reste {{ $daysRemaining }} jours
            </div>

            <p>Nous vous informons que votre contrat de domiciliation arrivera à échéance prochainement. 
            Il est important de prendre les mesures nécessaires pour éviter toute interruption de service.</p>

            <table class="info-table">
                <tr>
                    <th>Référence</th>
                    <td>{{ $domiciliation->reference_numero ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Date d'expiration</th>
                    <td>{{ \Carbon\Carbon::parse($domiciliation->date_fin)->format('d/m/Y') }}</td>
                </tr>
            </table>

            <div style="text-align: center; margin: 30px 0;">
                <a href="mailto:{{$societe->email ?? ''}}" class="cta-button">
                    Nous contacter
                </a>
                <a href="tel:{{ $client->telephone ?? '' }}" class="cta-button">
                    Nous Appeler
                </a>
            </div>

            <div class="alert-box">
                <strong>Important :</strong> 
                Pour éviter toute interruption de service, nous vous recommandons de nous contacter 
                au plus tôt pour discuter du renouvellement de votre contrat.
            </div>

            <p>Pour toute question ou assistance, n'hésitez pas à nous contacter.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe de gestion</strong></p>
        </div>
        
        <div class="footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="./storage/app/public/tmp_icons/icon_eryx.png">
                </div>
                <div class="footer-details">
                    <p><strong>{{ $societe->nom_francais }}</strong></p>
                    <p>Email : <a href="mailto:{{ $societe->email }}">{{ $societe->email }}</a></p>
                    <p>Téléphone : <a href="tel:{{ $societe->telephone }}">{{ $societe->telephone }}</a></p>
                    <p>Adresse : {{ $societe->adresse_siege_social_francais }}</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $societe->nom_francais }}. Tous droits réservés.</p>
            </div>
        </div>
    </div>
</body>
</html>