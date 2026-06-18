<?php

namespace Modules\Hotel\Livewire\Forms;

use Modules\Hotel\Entities\Agreement;
use Modules\Hotel\Entities\AgreementTemplate;
use Modules\Hotel\Entities\Reservation;
use Modules\Hotel\Enums\AgreementType;
use Modules\Hotel\Livewire\Agreements;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AddAgreement extends Component
{
    use LivewireAlert;

    public $reservationId;
    public $type          = 'rent';
    public $content       = '';
    public $agreement_date;
    public $notes         = '';

    public function mount(int $reservationId)
    {
        $this->reservationId   = $reservationId;
        $this->agreement_date  = now()->format('Y-m-d');
        $this->loadTemplate();
    }

    public function updatedType(): void
    {
        $this->loadTemplate();
    }

    protected function loadTemplate(): void
    {
        $agreementType = AgreementType::tryFrom($this->type);
        if (!$agreementType) {
            return;
        }

        $template = AgreementTemplate::getDefault($agreementType);
        if ($template) {
            $reservation = Reservation::with([
                'primaryGuest',
                'restaurant',
                'branch',
                'reservationRooms.roomType',
            ])->find($this->reservationId);

            if ($reservation) {
                $this->content = Agreement::renderContent($template->content, $reservation);
            } else {
                $this->content = $template->content;
            }
        }
    }

    public function submitForm(): void
    {
        $this->validate([
            'reservationId'  => 'required|exists:hotel_reservations,id',
            'type'           => 'required|in:' . implode(',', array_column(AgreementType::cases(), 'value')),
            'content'        => 'required|string',
            'agreement_date' => 'required|date',
        ]);

        $agreement = Agreement::create([
            'restaurant_id'    => restaurant()->id,
            'branch_id'        => branch()?->id,
            'reservation_id'   => $this->reservationId,
            'template_id'      => AgreementTemplate::getDefault(AgreementType::from($this->type))?->id,
            'agreement_number' => Agreement::generateAgreementNumber(branch()?->id),
            'type'             => $this->type,
            'content'          => str_replace('{{agreement_id}}', Agreement::generateAgreementNumber(branch()?->id), $this->content),
            'agreement_date'   => $this->agreement_date,
            'notes'            => $this->notes,
            'created_by'       => Auth::id(),
        ]);

        // Replace the {{agreement_id}} placeholder now that we have the real number
        $agreement->update([
            'content' => str_replace('{{agreement_id}}', $agreement->agreement_number, $agreement->content),
        ]);

        $this->dispatch('agreementAdded', agreementId: $agreement->id);

        $this->alert('success', __('hotel::modules.agreement.created'), [
            'toast'            => true,
            'position'         => 'top-end',
            'showCancelButton' => false,
        ]);
    }

    public function cancel(): void
    {
        $this->dispatch('hotelAgreementFormCancelled')->to(Agreements::class);
    }

    public function render()
    {
        $reservation = Reservation::with('primaryGuest')->find($this->reservationId);

        return view('hotel::livewire.forms.add-agreement', [
            'reservation'    => $reservation,
            'agreementTypes' => AgreementType::cases(),
        ]);
    }
}
