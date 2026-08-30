<?php

namespace App\Livewire\Portal;

use Livewire\Component;

class Notifications extends Component
{
    public function render()
    {
        $notifications = auth()->user()->notifications()->latest()->get();

        return view('livewire.portal.notifications', ['notifications' => $notifications])->layout('components.portal.layout', ['title' => 'Notifikasi']);
    }
}
