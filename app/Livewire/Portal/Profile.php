<?php

namespace App\Livewire\Portal;

use Livewire\Component;

class Profile extends Component
{
    public function render()
    {
        $student = auth()->user()->student;

        return view('livewire.portal.profile', ['student' => $student])->layout('components.portal.layout', ['title' => 'Profil Saya']);
    }
}
