<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\SimpleIdentity;

class Authenticator implements Nette\Security\Authenticator
{
    public function __construct(
        private Nette\Database\Explorer $database,
        private Nette\Security\Passwords $passwords
    ) {
    }

    public function authenticate(string $email, string $password): SimpleIdentity
    {
        // 1. Najdeme uživatele podle jména
        $row = $this->database->table('users')
            ->where('email', $email)
            ->fetch();

        // 2. Pokud neexistuje -> chyba
        if (!$row) {
            throw new Nette\Security\AuthenticationException('Uživatel nenalezen.');
        }

        // 3. Ověříme heslo (porovnáme zadané heslo s hashem v DB)
        if (!$this->passwords->verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Neplatné heslo.');
        }

        // 4. Pokud vše sedí, vrátíme Identitu (id a role)
        return new SimpleIdentity(
            $row->id,
            $row->role, // např. 'admin'
            ['username' => $row->username] // další data
        );
    }
}