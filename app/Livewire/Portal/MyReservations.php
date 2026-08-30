<?php

namespace App\Livewire\Portal;

use Livewire\Component;

class MyReservations extends Component
{
    public function render()
    {
        $student = auth()->user()->student;
        $reservations = $student?->reservations()->with('book')->latest()->get() ?? collect();

        return view('livewire.portal.my-reservations', ['reservations' => $reservations])->layout('components.portal.layout', ['title' => 'Reservasi Saya']);
    }
}
