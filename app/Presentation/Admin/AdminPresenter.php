<?php

declare(strict_types=1);

namespace App\Presentation\Admin;

use Nette;
use Nette\Database\Explorer;

final class AdminPresenter extends Nette\Application\UI\Presenter
{
    private Explorer $database;

    // 1. Zavedeme dvě různé proměnné pro hledání, aby se nepletly
    /** @var string|null @persistent */
    public $userSearch = null;

    /** @var string|null @persistent */
    public $responseSearch = null;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function actionDefault(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function actionUsers(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function actionResponses(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function beforeRender(): void
    {
        if ($this->isAjax()) {
        // Získáme parametry přímo z URL požadavku
        $httpRequest = $this->getHttpRequest();
        
        // Zjistíme, jestli se právě vyhledává (parametr existuje v URL)
        $isSearching = $httpRequest->getQuery('userSearch') !== null 
                    || $httpRequest->getQuery('responseSearch') !== null;

        // Zjistíme, jestli jde o signál (např. kliknutí na Reset nebo stránkování)
        $isSignal = $this->getSignal() !== null;

        // Pokud to NENÍ vyhledávání ani signál => znamená to, že uživatel klikl v MENU.
        // V tom případě musíme překreslit hlavní obálku (contentSnippet).
        if (!$isSearching && !$isSignal) {
            $this->redrawControl('contentSnippet');
            $this->redrawControl('sidebarSnippet');
        }
    }
    }

    // --- LOGIKA PRO UŽIVATELE ---

    public function renderUsers(): void
    {
        $query = $this->database->table('users');

        // Používáme specifickou proměnnou $userSearch
        if ($this->userSearch) {
            $query->where(
                'username LIKE ? OR email LIKE ? OR id = ?',
                "%" . $this->userSearch . "%",
                "%" . $this->userSearch . "%",
                $this->userSearch // ID by mělo být integer, ale v SQL to projde i jako string
            );
        }

        // Opraveno: Musíme specifikovat sloupec pro řazení (např. id)
        $query->order('id DESC');

        // Data posíláme do šablony až PO aplikaci filtrů
        $this->template->peoples = $query;
        
        // Pošleme aktuální hledaný výraz zpět do inputu
        $this->template->userSearch = $this->userSearch;

        if ($this->isAjax()) {
            $this->redrawControl('tabulkaZaznamu');
        }
    }

    // Handler pro reset hledání u uživatelů
    public function handleRefreshUsers(): void
    {
        $this->userSearch = null;
        if ($this->isAjax()) {
            $this->redrawControl('tabulkaZaznamu');
            $this->redirect('this');
        } else {
            $this->redirect('this');
        }
    }

    // --- LOGIKA PRO ODPOVĚDI (RESPONSES) ---

    public function renderResponses(): void
    {
        $query = $this->database->table('responses');

        // Používáme specifickou proměnnou $responseSearch
        if ($this->responseSearch) {
            $query->where(
                'name LIKE ? OR email LIKE ? OR id = ?',
                "%" . $this->responseSearch . "%",
                "%" . $this->responseSearch . "%",
                $this->responseSearch
            );
        }

        $query->order('time_date DESC');

        $this->template->responses = $query;
        
        // Pošleme aktuální hledaný výraz zpět do inputu
        $this->template->responseSearch = $this->responseSearch;

        if ($this->isAjax()) {
            $this->redrawControl('tabulkaZaznamu');
        }
    }

    // Handler pro reset hledání u odpovědí
    public function handleRefreshResponses(): void
    {
        $this->responseSearch = null;
        if ($this->isAjax()) {
            $this->redrawControl('tabulkaZaznamu');
            $this->redirect('this');
        } else {
            $this->redirect('this');
        }
    }
}