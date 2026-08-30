<?php

namespace App\Livewire\Portal;

use App\Models\User;
use Livewire\Component;

class Login extends Component
{
    public string $identity = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'identity' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $this->identity)->first();

        if (! $user) {
            $studentId = \DB::table('murid')->where('nipd', $this->identity)->value('id');
            if ($studentId) {
                $user = User::where('student_id', $studentId)->first();
            }
        }

        if (! $user || ! \Hash::check($this->password, $user->password)) {
            session()->flash('error', 'Email/NIPD atau password salah.');

            return;
        }

        if (! $user->is_active) {
            session()->flash('error', 'Akun Anda tidak aktif.');

            return;
        }

        $user->update(['last_login_at' => now()]);
        auth()->login($user, $this->remember);

        $this->redirect(route('portal.catalog'), navigate: true);
    }

    public function render()
    {
        return view('livewire.portal.login')->layout('components.portal.layout', ['title' => 'Masuk']);
    }
}
