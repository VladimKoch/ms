<?php

declare(strict_types=1);

namespace App\Presentation\Admin;

use Nette;
use Nette\Database\Explorer;

final class AdminPresenter extends Nette\Application\UI\Presenter
{   
    private Explorer $database;
    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }   

    public function actionDefault(): void
    {
        // Zde byste měl ověřit, že je uživatel přihlášený jako admin!
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
        // Pokud jde o AJAX požadavek, překresli snippety
        if ($this->isAjax()) {
            $this->redrawControl('contentSnippet');
            $this->redrawControl('sidebarSnippet');
        }
    }

    public function renderDefault(): void
    {
        // Prázdná stránka - jen sidebar
    }

    public function renderUsers(): void
    {
        $data = $this->database->table('users')->fetchAll();
        $this->template->peoples = $data;
    }

    public function renderResponses(): void
    {
        $data = $this->database->table('responses')->fetchAll();
        $this->template->responses = $data;
    }
}