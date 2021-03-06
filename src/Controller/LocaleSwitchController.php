<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LocaleSwitchController extends AbstractController
{
    /**
     * @Route("/switchenglish", name="switch_language_english")
     */
    public function switchLanguageEnglishAction() {
        $this->get('session')->set('language', 'en');
        return $this->switchLanguage('en');
    }

    /**
     * @Route("/switchfrench", name="switch_language_french")
     */
    public function switchLanguageFrenchAction() {
        $this->get('session')->set('language', 'fr');
        return $this->switchLanguage('fr');
    }

    private function switchLanguage($locale) {
        $this->get('session')->set('_locale', $locale);
        return $this->redirect($this->generateUrl('home'));
    }
}
