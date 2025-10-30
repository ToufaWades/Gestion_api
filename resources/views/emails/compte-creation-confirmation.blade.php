<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de création de compte</title>
</head>
<body>
    <h1>Confirmation de création de compte</h1>

    <p>Bonjour {{ $client->nom }} {{ $client->prenom }},</p>

    <p>Votre compte bancaire a été créé avec succès !</p>

    <div style="background-color: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <h3>Détails du compte :</h3>
        <ul>
            <li><strong>Numéro de compte :</strong> {{ $compte->numero_compte }}</li>
            <li><strong>Type :</strong> {{ $compte->type_compte }}</li>
            <li><strong>Solde initial :</strong> {{ number_format($compte->solde, 2) }} {{ $compte->devise }}</li>
            <li><strong>Date de création :</strong> {{ $compte->date_creation->format('d/m/Y') }}</li>
            <li><strong>Statut :</strong> {{ $compte->statut_compte }}</li>
        </ul>
    </div>

    <p>Vous pouvez maintenant utiliser votre compte pour effectuer des transactions.</p>

    <p>Cordialement,<br>
    L'équipe Gestion des Comptes</p>
</body>
</html>