<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel urgent - Domiciliation expirée</title>
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
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .urgent-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
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
            background: #dc3545;
            color: white;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            background: #6c757d;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .days-expired {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            text-align: center;
            margin: 20px 0;
        }
        .consequences-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #f39c12;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RAPPEL URGENT</h1>
            <p>Votre domiciliation a expiré</p>
        </div>
        
        <div class="content">
            <h2>Bonjour {{ $client->nom ?? 'Cher client' }},</h2>
            
            <div class="urgent-box">
                <strong>🚨 URGENT :</strong> Votre service de domiciliation a expiré depuis {{ $daysExpired }} jours !
            </div>

            <div class="days-expired">
                Expiré depuis {{ $daysExpired }} jours
            </div>

            <p>Nous vous informons que votre contrat de domiciliation a expiré et aucune action n'a été entreprise 
            de votre part. Cette situation nécessite votre attention immédiate.</p>

            <table class="info-table">
                <tr>
                    <th>Référence</th>
                    <td>{{ $domiciliation->reference_numero ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Date d'expiration</th>
                    <td>{{ \Carbon\Carbon::parse($domiciliation->date_fin)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Expirée depuis</th>
                    <td>{{ $daysExpired }} jours</td>
                </tr>
                <tr>
                    <th>Adresse de domiciliation</th>
                    <td>{{ $domiciliation->adresse_siege_social_francais ?? 'N/A' }}</td>
                </tr>
            </table>

            <div style="text-align: center; margin: 30px 0;">
                <a href="tel:{{ config('company.phone', '{{ $societe->tele }}" class="cta-button">
                    Appelez-nous MAINTENANT
                </a>
                <br>
                <a href="mailto:{{ config('mail.from.address') }}" class="cta-button" style="background: #28a745;">
                    Email urgent
                </a>
            </div>

            <p><strong>Il est impératif d'agir rapidement pour régulariser votre situation.</strong></p>
            
            <p>Cordialement,<br>
            <strong>L'équipe de gestion - Service Urgent</strong></p>
        </div>
        
        <div class="footer">
            <p><strong>RAPPEL AUTOMATIQUE URGENT</strong> - Action immédiate requise</p>
            <p>{{ config('app.name') }} - Service de domiciliation</p>
        </div>
    </div>
</body>
</html>