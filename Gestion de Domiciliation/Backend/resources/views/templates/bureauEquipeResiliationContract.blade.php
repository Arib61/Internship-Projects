<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Résiliation de Bureau équipé</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: "Times New Roman", serif;
      direction: ltr;
    }

    #pdf-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 20mm 10mm;
      position: relative;
      box-sizing: border-box;
    }

    .logo {
      position: absolute;
      top: 0;
      left: 20px;
      width: 120px;
      height: auto;
    }

    .pdf-content {
      text-align: center;
      max-width: 700px;
      width: 100%;
    }

    .date {
      text-align: right;
      margin-bottom: 30px;
    }

    .subject {
      font-weight: bold;
      text-align: left;
      text-decoration: underline;
      margin-bottom: 25px;
    }

    .content {
      text-align: justify;
      margin-bottom: 40px;
    }

    .signature {
      margin-top: 50px;
      text-align: center;
    }

    .sign-name {
      margin-top: 50px;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <div id="pdf-container">

    {{-- Logo --}}
    @if(isset($societe) && $societe->logo)
      <img
        src="{{ asset('api/storage/' . $societe->logo) }}"
        alt="Logo"
        class="logo" />
    @endif

    <div class="pdf-content">

      {{-- Date --}}
      <div class="date">
        Rabat le {{ \Carbon\Carbon::now('Africa/Casablanca')->format('d/m/Y') }}
      </div>

      {{-- Subject --}}
      <div class="subject">
        Objet : Résiliation de bureau équipé.
      </div>

      {{-- Contract Body --}}
      <div class="content">
        En date du {{ $bureau->created_at->format('d/m/Y') }}, nous avons mis à la disposition de la société
        <strong>{{ $societe->societe_nom }}</strong>, gratuitement une attestation de domiciliation sur notre adresse suivante :
        <strong>{{ $societe->adresse_siege_social_francais }}</strong>.<br><br>

        Suite à la décision de liquidation sociale de la société <strong>{{ $client->nom_francais }}</strong>, nous décidons de mettre fin à cette domiciliation.
        Donc, cette résiliation prendra effet à compter du <strong>{{ $bureau->date_fin }}</strong>.<br><br>

        Veuillez agréer, Madame, Monsieur, l’expression de mes salutations distinguées.
      </div>

      {{-- Signature --}}
      <div class="signature">
        Signature<br>
        <div class="sign-name">{{ $societe->nom_complet_francais ?? 'MOURAD EL MANSOURI' }}</div>
      </div>

    </div>
  </div>
</body>
</html>
