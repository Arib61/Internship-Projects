<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Gestionnaire - Domiciliation expire dans 5 jours</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .alert-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }

        .warning-box {
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
            border-radius: 8px;
            overflow: hidden;
        }

        .info-table th,
        .info-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .info-table th {
            background: #e9ecef;
            font-weight: bold;
            color: #495057;
        }

        .info-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .action-buttons {
            text-align: center;
            margin: 25px 0;
        }

        .cta-button {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        .cta-button.urgent {
            background: #dc3545;
        }

        .cta-button.info {
            background: #17a2b8;
        }

        .days-remaining {
            font-size: 28px;
            font-weight: bold;
            color: #f39c12;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #fff3cd;
            border-radius: 8px;
        }

        .manager-actions {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .priority-high {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="days-remaining">
            {{ $daysRemaining }} JOURS RESTANTS
        </div>
        <div class="content">
            <h2>Bonjour {{ $gestionnaire->nom ?? 'Cher Gestionnaire' }},</h2>
            <h4>La domiciliation du client <strong>{{ $client->nom_francais }}</strong> se termine dans 5 jours, le
                <strong>{{ $domiciliation->date_fin->format("d/m/Y") }}</strong>.
            </h4>

            <h3>Détails de la domiciliation :</h3>
            <table class="info-table">
                <tr>
                    <th>Référence Domiciliation</th>
                    <td><strong>{{ $domiciliation->reference_numero ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Client</th>
                    <td>{{ $client->nom_francais ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Email Client</th>
                    <td>{{ $client->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Téléphone Client</th>
                    <td>{{ $client->telephone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Date d'expiration</th>
                    <td><strong style="color:rgb(199, 45, 60);">{{ \Carbon\Carbon::parse($domiciliation->date_fin)->format('d/m/Y') }}</strong></td>
                </tr>
                <tr>
                    <th>Date de la première domiciliation</th>
                    <td><strong style="color:rgb(0, 0, 0);">{{ \Carbon\Carbon::parse($domiciliation->created_at)->format('d/m/Y') }}</strong></td>
                </tr>
            </table>

            <div class="action-buttons">
                <a href="tel:{{ $client->telephone ?? '' }}" class="cta-button urgent">
                    Appeler le client
                </a>
                <a href="mailto:{{ $client->email ?? '' }}" class="cta-button">
                    Envoyer un email
                </a>
            </div>

            <p><strong>Merci de traiter cette demande dans les plus brefs délais pour assurer la continuité du service client.</strong></p>

            <p>Cordialement,<br>
                <strong>L'équipe de gestion</strong>
            </p>
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
