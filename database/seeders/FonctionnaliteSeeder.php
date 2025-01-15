<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class FonctionnaliteSeeder extends Seeder
{
    public function run()
    {
        DB::table('fonctionnalites')->insert([
            [
                'id' => 1,
                'nom_fonctionnalite' => 'Register',
                'description' => 'Permet de créer un compte',
            ],
            [
                'id' => 2,
                'nom_fonctionnalite' => 'Login',
                'description' => 'Permet de se connecter',
            ],
            [
                'id' => 4,
                'nom_fonctionnalite' => 'Email verification',
                'description' => 'Email verification',
            ],
            [
                'id' => 5,
                'nom_fonctionnalite' => 'Email spam',
                'description' => "Spammer d'email",
            ],
            [
                'id' => 6,
                'nom_fonctionnalite' => 'Password generator',
                'description' => 'Génération de mot de passe',
            ],
            [
                'id' => 7,
                'nom_fonctionnalite' => 'Get subdomains',
                'description' => 'Récupérer tous domaines & sous-domaines associés à...',
            ],
            [
                'id' => 8,
                'nom_fonctionnalite' => 'Password check',
                'description' => 'Vérifie le mot de passe',
            ],
            [
                'id' => 9,
                'nom_fonctionnalite' => 'Fake identity',
                'description' => 'Génération de fausse identité',
            ],
            [
                'id' => 10,
                'nom_fonctionnalite' => 'Ddos',
                'description' => 'Fonctionnalité de ddos',
            ],
            [
                'id' => 11,
                'nom_fonctionnalite' => 'Random image',
                'description' => "Génération d'image random",
            ],
            [
                'id' => 12,
                'nom_fonctionnalite' => 'Crawler information',
                'description' => "Crawler d'informations",
            ],
            [
                'id' => 13,
                'nom_fonctionnalite' => 'Phishing',
                'description' => 'Phishing',
            ],
            [
                'id' => 14,
                'nom_fonctionnalite' => 'Get users profile',
                'description' => "Permet d'avoir le profil de l'utilisateur",
            ],
        ]);
    }
}
