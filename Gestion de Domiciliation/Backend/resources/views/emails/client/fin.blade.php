<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fin de votre contrat de domiciliation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e74c3c;
        }
        .header h1 {
            color: #e74c3c;
            margin: 0;
            font-size: 24px;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #f39c12;
        }
        .alert-icon {
            display: inline-block;
            font-size: 20px;
            margin-right: 10px;
        }
        .details-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .details-box h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px dotted #dee2e6;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .detail-value {
            color: #6c757d;
        }
        .action-section {
            background-color: #e8f4fd;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .action-section h3 {
            color: #0056b3;
            margin-top: 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 5px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 14px;
            color: #6c757d;
            text-align: start;
        }
        .contact-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .contact-info h4 {
            margin-top: 0;
            color: #495057;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                margin-bottom: 5px;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Fin de Contrat de Domiciliation</h1>
        </div>

        <div class="alert-box">
            <span class="alert-icon"></span>
            <strong>Attention :</strong> Votre contrat de domiciliation a expiré aujourd'hui.
        </div>

        <p>Nous vous informons que votre contrat de domiciliation a pris fin aujourd'hui, <strong>{{ $dateFinFormatted }}</strong>.</p>

        <div class="details-box">
            <h3>Détails du Contrat</h3>
            <div class="detail-row">
                <span class="detail-label">Référence Domiciliation : </span>
                <span class="detail-value"> #{{ $domiciliation->reference_numero ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date de fin : </span>
                <span class="detail-value"> {{ $dateFinFormatted }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Client : </span>
                <span class="detail-value"> {{ $client->nom_francais ?? 'Non défini' }}</span>
            </div>
        </div>

        <p><strong>Important :</strong> Sans renouvellement rapide, vos services de domiciliation seront suspendus.</p>

        <p>Nous restons à votre disposition pour tout renseignement complémentaire.</p>

        <p>Cordialement,<br>
        <strong>L'équipe de gestion</strong></p>

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