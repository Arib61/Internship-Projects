<!DOCTYPE html>
<html>
<head>
    <title>Notification Professionnelle</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0; padding: 20px;
        }
        header {
            text-align: left;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        header h2 {
            margin: 0;
            font-weight: 600;
            color: #222;
        }
        main {
            font-size: 16px;
            line-height: 1.4;
        }
        footer {
            margin-top: 40px;
            border-top: 2px solid #ccc;
            padding-top: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        footer img.logo {
            max-height: 50px;
            max-width: 100px;
        }
        footer .infos {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>

<header>
    <h2>{{ $clientName }}</h2>
    <p>Message professionnel</p>
</header>

<main>
    <p>{!! nl2br(e($messagePro)) !!}</p>
</main>

<footer>
    @if($societe->logo)
        <img src="{{ $societe->logo }}" alt="Logo Société" class="logo">
    @endif
    <div class="infos">
        <p><strong>{{ $societe->nom_francais }}</strong></p>
        <p>Email : {{ $societe->email }}</p>
        <p>Téléphone : {{ $societe->telephone }}</p>
        <p>Site web : <a href="{{ $societe->website }}">{{ $societe->website }}</a></p>
    </div>
</footer>

</body>
</html>
