<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Database\Explorer;


final class HomePresenter extends Nette\Application\UI\Presenter
{   
      private Explorer $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }


    // Definice formuláře
    protected function createComponentContactForm(): Form
    {
        $form = new Form;

        $form->addText('name', 'Celé jméno:')
            ->setRequired('Zadejte prosím své jméno.')
            ->setHtmlAttribute('class', 'form-control');

        $form->addEmail('email', 'E-mail:')
            ->setRequired()
            ->addRule(Form::EMAIL, 'E-mail nemá správný formát.') // Další pravidlo
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('phone', 'Telefon:')
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('annual_turnover', 'Roční obrat (Kč):')
            ->setRequired()    
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('property_cards', 'Majetek (inv. karty):')
        ->setRequired()    
        ->setHtmlAttribute('class', 'form-control');

        $form->addText('employe', 'Počet zaměstnanců:')
        ->setRequired()    
        ->setHtmlAttribute('class', 'form-control');

        $form->addText('documents', 'Počet dokladů:')
        ->setRequired()    
        ->setHtmlAttribute('class', 'form-control');

        $form->addText('legal_form', 'Právní forma')
        ->setRequired()    
        ->setHtmlAttribute('class', 'form-control');

        $form->addText('business', 'Předmět podnikání')
        ->setRequired()    
        ->setHtmlAttribute('class', 'form-control');

        $form->addSelect('interest', 'Mám zájem o', [
            '' => 'Vyberte',
            'Podvojné účetnictví' => 'Podvojné účetnictví',
            'Daňová Evidence' => 'Daňová evidence',
            'Mzdová a personální agenda' => 'Mzdová a personální agenda',
        ])->setRequired()->setHtmlAttribute('class', 'form-select');

        $form->addSelect('taxpay', 'Jste plátce DPH?', [
            '' => 'Vyberte',
            'Ne' => 'Ne, nejsem',
            'Měsíční' => 'Ano, měsíční',
            'Čtvrtletní' => 'Ano, čtvrtletní',
        ])->setRequired()->setHtmlAttribute('class', 'form-select');

        $form->addTextArea('info', 'Doplňující informace')
            // ->setRequired('Prosím napište nám zprávu.')
            ->setHtmlAttribute('class', 'form-control bg-light')
            ->setHtmlAttribute('rows', 4)
            ->setHtmlAttribute('placeholder', 'Potřebuji zpracovat daňové přiznání...');

        $form->addSubmit('send', 'Odeslat zprávu')
            ->setHtmlAttribute('class', 'btn-custom w-100'); // Použijeme naše CSS tlačítko

        $form->onSuccess[] = [$this, 'contactFormSucceeded'];

        return $form;
    }

    // Zpracování po odeslání
    public function contactFormSucceeded(Form $form, \stdClass $values): void
    {   

        // Uložení do DB
        $this->db->table('responses')->insert([
            'time_date'=> new \DateTime(),
            'name' => $values->name,
            'email' => $values->email,
            'phone' => $values->phone,
            'annual_turnover' => $values->annual_turnover,
            'property_cards' => $values->property_cards,
            'employe' => $values->employe,
            'documents' => $values->documents,
            'legal_form' => $values->legal_form,
            'business' => $values->business,
            'interest' => $values->interest,
            'taxpay' => $values->taxpay,
            'info' => $values->info,
        ]);



        // try {
        //     // Odeslání emailu
        //     $mail = new Message;
        //     $mail->setFrom('Webový formulář <noreply@vasedomena.cz>')
        //         ->addTo('marcelasalajkova@ucto.cz')
        //         ->setReplyTo($data->email)
        //         ->setSubject('Nová zpráva z webu: ' . $data->name)
        //         ->setBody("Jméno: {$data->name}\nEmail: {$data->email}\n\nZpráva:\n{$data->message}");

        //     $mailer = new SendmailMailer;
        //     $mailer->send($mail);

        //     $this->flashMessage('Děkujeme, zpráva byla úspěšně odeslána.', 'success');
        // } catch (\Exception $e) {
        //     $this->flashMessage('Omlouváme se, došlo k chybě při odesílání.', 'danger');
        // }

        $this->flashMessage('Děkuji, vaše zpráva byla odeslána.', 'success');

        // Pokud je to AJAX, překreslíme snippet a vyčistíme formulář
    if ($this->isAjax()) {
        $form->setValues([], true); // Vymazání políček
        $this->redrawControl('contactFormSnippet'); // Překreslení HTML
    } else {
        // Pokud uživatel nemá JS, funguje klasický redirect
        $this->redirect('this#contactForm'); // Přesměrování na tu samou stránku (proti F5 odeslání)
    }

    
    }

    // public function actionDefault(): void
    // {
    //     // Dočasný kód pro získání hashe
    //         $passwords = new \Nette\Security\Passwords;
    //         dump($passwords->hash('123456')); 
    //         die();
    // }

}
