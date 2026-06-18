<?php

namespace Modules\Hotel\Livewire;

use Modules\Hotel\Entities\Agreement;
use Modules\Hotel\Entities\Reservation;
use Modules\Hotel\Enums\AgreementType;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class Agreements extends Component
{
    use LivewireAlert, WithPagination;

    public $search       = '';
    public $filterType   = '';
    public $showAddModal = false;
    public $selectedReservationId;
    public $confirmDeleteAgreementModal = false;
    public $activeAgreementId = null;

    // For inline generate from reservation id passed via query
    public function mount()
    {
        $reservationId = request()->query('reservation_id');
        if ($reservationId) {
            $this->selectedReservationId = $reservationId;
            $this->showAddModal          = true;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function showGenerateAgreement(int $reservationId): void
    {
        $this->selectedReservationId = $reservationId;
        $this->showAddModal          = true;
    }

    public function showDeleteAgreement(int $id): void
    {
        abort_if(!user_can('Delete Hotel Reservation'), 403);

        $this->activeAgreementId = $id;
        $this->confirmDeleteAgreementModal = true;
    }

    public function deleteAgreement(): void
    {
        abort_if(!user_can('Delete Hotel Reservation'), 403);

        if (!$this->activeAgreementId) {
            return;
        }

        Agreement::findOrFail((int) $this->activeAgreementId)->delete();

        $this->confirmDeleteAgreementModal = false;
        $this->activeAgreementId = null;

        $this->alert('success', __('hotel::modules.agreement.deleted'), [
            'toast'            => true,
            'position'         => 'top-end',
            'showCancelButton' => false,
        ]);
    }

    #[\Livewire\Attributes\On('agreementAdded')]
    public function onAgreementAdded(int $agreementId): void
    {
        $this->showAddModal          = false;
        $this->selectedReservationId = null;
        $this->redirectRoute('hotel.agreements.print', ['agreement' => $agreementId]);
    }

    #[\Livewire\Attributes\On('hotelAgreementFormCancelled')]
    public function onHotelAgreementFormCancelled(): void
    {
        $this->showAddModal = false;
        $this->selectedReservationId = null;
    }

    public function getAgreementsProperty()
    {
        return Agreement::with(['reservation.primaryGuest'])
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->search, function ($q) {
                $q->where('agreement_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('reservation', function ($rq) {
                      $rq->where('reservation_number', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('reservation.primaryGuest', function ($gq) {
                      $gq->where('first_name', 'like', '%' . $this->search . '%')
                         ->orWhere('last_name', 'like', '%' . $this->search . '%');
                  });
            })
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('hotel::livewire.agreements', [
            'agreements'   => $this->agreements,
            'agreementTypes' => AgreementType::cases(),
        ]);
    }
}
