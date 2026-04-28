<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public $currentLocale;

    public function mount()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->currentLocale = $_SESSION['locale'] ?? 'vi';
    }

    public function updatedCurrentLocale($value)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['locale'] = $value;
        \LangHelper::setLocale($value);
        $this->currentLocale = $value;
        
        $this->js('window.location.href = window.location.href');
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
