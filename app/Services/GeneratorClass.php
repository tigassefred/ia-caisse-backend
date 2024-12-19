<?php

namespace App\Services;

class GeneratorClass
{
    public function generateInvoice()
    {
        return [
            'name' => 'john doe',
            'client_id' => NULL,
            'valeur_reduction' => 200000,
            'reliquat' => 0,
            'comments' => 'testte',
            'is10Yaars' => true,
            'valeur_facture' => 1020000,
            'valeur_apres_reduction' => 820000,
            'somme_verser' => 820000,
            'commercial' => '88b34fc4-6a41-4779-ba07-6ae96baedf22',
            'Paniers' => [
                [
                    'uuid' => 7006,
                    'qte' => '1',
                    'name' => 'GRP2022341',
                    'designation' => 'HAND BALLE No.74',
                    'type' => 'BALLE',
                    'cbm' => '1',
                ],
                [
                    'uuid' => 7007,
                    'qte' => '1',
                    'name' => 'GRP2022341',
                    'designation' => 'GRD BALLE No.72(1200PCS)',
                    'type' => 'BALLE',
                    'cbm' => '1',
                ],
                [
                    'uuid' => 7011,
                    'qte' => '1',
                    'name' => 'GRP2022341',
                    'designation' => 'HAND BALLE No.120',
                    'type' => 'BALLE',
                    'cbm' => '1',
                ],
            ],
        ];
    }
}
