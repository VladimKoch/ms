<?php

declare(strict_types=1);

namespace App\Presentation\Sign;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Database\Explorer;

final class SignPresenter extends Nette\Application\UI\Presenter
{
    private Explorer $db;
    

    public function __construct(Explorer $db)
    {
        $this->db = $db;
        
    }




    public function createComponentSignInForm():Form
    {
        $form = new Form;

        $form->addText('email', 'Zadejte email')
            ->setRequired('Zadejte email.')
            ->setHtmlAttribute('class', 'form-control');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo.')
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('send', 'Přihlásit se')
            ->setHtmlAttribute('class', 'btn btn-primary w-100 py-2 mt-3');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $data): void
    {
        // Tady zpracujete přihlášení
        // $data->username, $data->password ...

        try {
        // Tady se volá náš Authenticator
        $this-> getUser()->login($data->email, $data->password);
        
        // Pokud to projde, přesměrujeme do adminu
        // $this->flashMessage('Jste přihlášen!');
        $this->redirect('Admin:default');

        } catch (Nette\Security\AuthenticationException $e) {
            // Pokud Authenticator vyhodil chybu, zobrazíme ji ve formuláři
            $form->addError('Špatné jméno nebo heslo.');
        }

    }
}