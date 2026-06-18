<?php

namespace Modules\Hotel\Livewire;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\Hotel\Entities\Quotation;
use Modules\Hotel\Enums\QuotationStatus;
use Modules\Hotel\Notifications\HotelQuotationConfirmation;

class Quotations extends Component
{
    use LivewireAlert, WithPagination;

    public $search = '';
    public $filterDate = '';
    public $filterStatus = '';

    public function mount(): void
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
    }

    public function showComingSoon(): void
    {
        $this->alert('info', __('hotel::modules.comingSoon') ?? 'Coming soon', [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
        ]);
    }

    public function emailQuotationConfirmation(int $quotationId): void
    {
        abort_if(!user_can('Update Hotel Quotation'), 403);

        $quotation = Quotation::with(['primaryGuest'])->findOrFail($quotationId);
        $email = $quotation->primaryGuest?->email;

        if (blank($email)) {
            $this->alert('warning', __('hotel::modules.quotation.noEmailAddress'), [
                'toast' => true,
                'position' => 'top-end',
            ]);
            return;
        }

        try {
            Notification::route('mail', $email)->notify(new HotelQuotationConfirmation($quotation));

            $this->alert('success', __('hotel::modules.quotation.confirmationSent'), [
                'toast' => true,
                'position' => 'top-end',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error sending hotel quotation confirmation email: ' . $e->getMessage(), [
                'quotation_id' => $quotation->id,
                'guest_id' => $quotation->primaryGuest?->id,
                'email' => $email,
            ]);

            $this->alert('error', __('hotel::modules.quotation.somethingWentWrong'), [
                'toast' => true,
                'position' => 'top-end',
            ]);
        }
    }

    public function render()
    {
        abort_if(!user_can('Show Hotel Quotations'), 403);

        $query = Quotation::with(['primaryGuest'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('quotation_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('primaryGuest', function ($q) {
                            $q->where('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDate, fn ($q) => $q->whereDate('check_in_date', $this->filterDate))
            ->orderBy('check_in_date', 'desc');

        return view('hotel::livewire.quotations', [
            'quotations' => $query->paginate(20),
            'statuses' => QuotationStatus::cases(),
        ]);
    }
}

