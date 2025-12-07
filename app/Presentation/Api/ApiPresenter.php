<?php

declare(strict_types=1);

namespace App\Presentation\Api;

use Nette;
use Nette\Application\Responses\JsonResponse;

final class ApiPresenter extends Nette\Application\UI\Presenter
{
    // Připojení k databázi
    private $database;
    private array $validApiKeys;

    public function __construct(Nette\Database\Explorer $database, array $apiKeys)
    {   
        parent::__construct();
        $this->validApiKeys = $apiKeys;
        $this->database = $database;
    }

    protected function startup(): void
    {
        parent::startup();
        
        // --- 1. CORS HLAVIČKY ---
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Origin', '*');
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Api-Key'); // Přidal jsem X-Api-Key do povolených hlaviček
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');

        // --- 2. VÝJIMKA PRO OPTIONS (Preflight) ---
        // Prohlížeč posílá OPTIONS request bez API klíče, aby zjistil, zda může komunikovat.
        // Musíme ho pustit dál bez kontroly hesla.
        if ($this->getRequest()->getMethod() === 'OPTIONS') {
            return;
        }

        // --- 3. KONTROLA API KLÍČE (Globalní pro celý Presenter) ---
        $apiKey = $this->getHttpRequest()->getHeader('X-Api-Key');

        // Pokud klíč chybí NEBO není v poli povolených klíčů ($this->validApiKeys)
        if (!$apiKey || !in_array($apiKey, $this->validApiKeys, true)) {
            
            // Nastavíme HTTP kód 401 Unauthorized
            $this->getHttpResponse()->setCode(Nette\Http\IResponse::S401_Unauthorized);
            
            // Odešleme chybu a UKONČÍME běh skriptu (dál se nic neprovede)
            $this->sendJson([
                'status' => 'error', 
                'message' => 'Přístup odepřen: Neplatný API klíč'
            ]);
        }
    }

    // Ošetření tzv. "preflight" requestu
    public function actionOptions(): void
    {
        $this->sendJson(['status' => 'ok']);
    }

    /**
     * 1. ČTENÍ DAT (GET)
     */
    public function actionRead(string $table, ?int $id = null): void
    {
        // Zde už nemusíš řešit API klíč, vyřešil to startup()
        
        $allowedTables = ['responses', 'users'];
        if (!in_array($table, $allowedTables)) {
            $this->sendJson(['error' => 'Tabulka nepovolena']);
        }

        $selection = $this->database->table($table);

        if ($id) {
            $row = $selection->get($id);
            if (!$row) {
                $this->error('Záznam nenalezen', 404);
            }
            $this->sendJson($row->toArray());
        }

        // Filtrování
        $httpRequest = $this->getHttpRequest();
        $sloupec = $httpRequest->getQuery('sloupec'); 
        $hledat = $httpRequest->getQuery('hledat');   

        if ($sloupec && $hledat) {
            try {
                $selection->where($sloupec . ' LIKE ?', '%' . $hledat . '%');
            } catch (\Exception $e) {
                $this->sendJson(['error' => 'Chyba filtrování: ' . $e->getMessage()]);
            }
        }

        $rows = $selection->limit(50)->fetchAll();

        $jsonOutput = [];
        foreach ($rows as $row) {
            $jsonOutput[] = $row->toArray();
        }

        $this->sendJson($jsonOutput);
    }

    /**
     * 2. ZMĚNA DAT (POST)
     */
    public function actionUpdate(): void
    {
        // A. BEZPEČNOSTNÍ KONTROLA
        // Zde jsem odstranil tvou ruční kontrolu, protože už proběhla v startup().
        // Pokud se kód dostal sem, uživatel MÁ platný klíč.

        // B. Získání dat z požadavku
        $input = $this->getHttpRequest()->getRawBody();
        $data = json_decode($input, true);

        if (!$data || !isset($data['id'], $data['table'], $data['values'])) {
             $this->sendJson(['status' => 'error', 'message' => 'Chybí data (id, table, values)']);
        }

        // C. Uložení do databáze
        try {
            $this->database->table($data['table'])
                ->where('id', $data['id'])
                ->update($data['values']);

            $this->sendJson(['status' => 'ok', 'message' => 'Uloženo']);
        } catch (\Exception $e) {
            $this->sendJson(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}