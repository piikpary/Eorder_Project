<?php

namespace Modules\Hotel\Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Modules\Hotel\Entities\Reservation;
use Modules\Hotel\Entities\ReservationRoom;
use Modules\Hotel\Entities\Guest;
use Modules\Hotel\Entities\RatePlan;
use Modules\Hotel\Entities\Room;
use Modules\Hotel\Entities\RoomType;
use Modules\Hotel\Entities\Rate;
use Modules\Hotel\Entities\Stay;
use Modules\Hotel\Entities\StayGuest;
use Modules\Hotel\Entities\Folio;
use Modules\Hotel\Entities\FolioLine;
use Modules\Hotel\Enums\FolioLineType;
use Modules\Hotel\Enums\FolioStatus;
use Modules\Hotel\Enums\ReservationStatus;
use Modules\Hotel\Enums\RoomStatus;
use Modules\Hotel\Enums\StayStatus;
use Carbon\Carbon;

class HotelReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = Restaurant::with('branches')->get();

        foreach ($restaurants as $restaurant) {
            foreach ($restaurant->branches as $branch) {
                $roomTypes = RoomType::where('restaurant_id', $restaurant->id)
                    ->where('branch_id', $branch->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();

                // Get guests for this branch
                $guests = Guest::where('restaurant_id', $restaurant->id)
                    ->where('branch_id', $branch->id)
                    ->limit(3)
                    ->get();

                // Get rate plans for this branch (optional)
                $ratePlans = RatePlan::where('restaurant_id', $restaurant->id)
                    ->where('branch_id', $branch->id)
                    ->where('is_active', true)
                    ->get();

                if ($guests->isEmpty()) {
                    continue;
                }

                // Create 3 reservations; the first one will be checked in
                $reservationCounter = 1;
                $firstReservationInBranch = true;

                foreach ($guests->take(3) as $index => $guest) {
                    $reservationNumber = $this->generateUniqueReservationNumber($restaurant->id, $branch->id, $reservationCounter);

                    // Check if reservation already exists
                    $existingReservation = Reservation::where('restaurant_id', $restaurant->id)
                        ->where('branch_id', $branch->id)
                        ->where('primary_guest_id', $guest->id)
                        ->first();

                    $isCheckIn = $firstReservationInBranch;
                    $firstReservationInBranch = false;

                    if (!$existingReservation) {
                        $checkInDate = Carbon::today();
                        $checkOutDate = $isCheckIn ? Carbon::today() : Carbon::tomorrow();

                        $ratePlanId = $ratePlans->isNotEmpty() ? $ratePlans->random()->id : null;

                        $reservation = Reservation::create([
                            'restaurant_id' => $restaurant->id,
                            'branch_id' => $branch->id,
                            'reservation_number' => $reservationNumber,
                            'primary_guest_id' => $guest->id,
                            'check_in_date' => $checkInDate->format('Y-m-d'),
                            'check_out_date' => $checkOutDate->format('Y-m-d'),
                            'check_in_time' => '14:00:00',
                            'check_out_time' => $isCheckIn ? '23:59:00' : '11:00:00',
                            'rooms_count' => 1,
                            'adults' => 1 + $index,
                            'children' => $index > 1 ? 1 : 0,
                            'rate_plan_id' => $ratePlanId,
                            'status' => $isCheckIn
                                ? ReservationStatus::CHECKED_IN->value
                                : ReservationStatus::TENTATIVE->value,
                        ]);

                        $roomType = $roomTypes->get($index) ?? $roomTypes->random();
                        if ($roomType) {
                            $nights = max(1, (int) $checkInDate->diffInDays($checkOutDate));

                            $rate = (float) $roomType->base_rate;
                            if ($ratePlanId) {
                                $rateRow = Rate::where('restaurant_id', $restaurant->id)
                                    ->where('branch_id', $branch->id)
                                    ->where('room_type_id', $roomType->id)
                                    ->where('rate_plan_id', $ratePlanId)
                                    ->where('is_active', true)
                                    ->whereDate('date_from', '<=', $checkInDate->format('Y-m-d'))
                                    ->whereDate('date_to', '>=', $checkInDate->format('Y-m-d'))
                                    ->first();

                                if ($rateRow) {
                                    $rate = (float) $rateRow->double_occupancy_rate;
                                }
                            }

                            $totalAmount = $rate * 1 * $nights;

                            // Find an available room to assign (only for check-in)
                            $assignedRoomId = null;
                            if ($isCheckIn) {
                                $availableRoom = Room::where('restaurant_id', $restaurant->id)
                                    ->where('branch_id', $branch->id)
                                    ->where('room_type_id', $roomType->id)
                                    ->where('status', RoomStatus::VACANT_CLEAN->value)
                                    ->where('is_active', true)
                                    ->first();
                                $assignedRoomId = $availableRoom?->id;
                            }

                            ReservationRoom::create([
                                'reservation_id' => $reservation->id,
                                'room_type_id' => $roomType->id,
                                'room_id' => $assignedRoomId,
                                'quantity' => 1,
                                'rate' => $rate,
                                'total_amount' => $totalAmount,
                            ]);

                            $reservation->update([
                                'rooms_count' => 1,
                                'total_amount' => $totalAmount,
                            ]);

                            // Perform full check-in for the first reservation
                            if ($isCheckIn && $assignedRoomId) {
                                $this->checkInReservation(
                                    $reservation,
                                    $guest,
                                    $assignedRoomId,
                                    $roomType,
                                    $rate,
                                    $nights,
                                    $totalAmount,
                                    $checkInDate,
                                    $checkOutDate
                                );
                            }
                        }
                    } elseif ($isCheckIn && $existingReservation->status === ReservationStatus::TENTATIVE->value) {
                        // If the reservation already exists but isn't checked in yet, skip silently
                    }

                    $reservationCounter++;
                }
            }
        }
    }

    /**
     * Perform a full check-in: create stay, update room status, create folio with room charge.
     */
    private function checkInReservation(
        Reservation $reservation,
        Guest $guest,
        int $roomId,
        RoomType $roomType,
        float $rate,
        int $nights,
        float $totalAmount,
        Carbon $checkInDate,
        Carbon $checkOutDate
    ): void {
        // Skip if a stay already exists for this reservation
        if (Stay::where('reservation_id', $reservation->id)->exists()) {
            return;
        }

        // Update room to occupied
        Room::where('id', $roomId)->update(['status' => RoomStatus::OCCUPIED->value]);

        $stayNumber = Stay::generateStayNumber($reservation->branch_id);

        $stay = Stay::create([
            'restaurant_id' => $reservation->restaurant_id,
            'branch_id' => $reservation->branch_id,
            'reservation_id' => $reservation->id,
            'room_id' => $roomId,
            'stay_number' => $stayNumber,
            'check_in_at' => $checkInDate->copy()->setTime(14, 0),
            'expected_checkout_at' => $checkInDate->equalTo($checkOutDate)
                ? $checkOutDate->copy()->setTime(23, 59)
                : $checkOutDate->copy()->setTime(11, 0),
            'status' => StayStatus::CHECKED_IN->value,
            'adults' => $reservation->adults,
            'children' => $reservation->children,
        ]);

        // Link primary guest to stay
        StayGuest::create([
            'stay_id' => $stay->id,
            'guest_id' => $guest->id,
            'is_primary' => true,
        ]);

        // Generate folio number
        $folioNumber = 'FOL-' . $reservation->branch_id . '-' . str_pad($stay->id, 6, '0', STR_PAD_LEFT);

        $folio = Folio::create([
            'restaurant_id' => $reservation->restaurant_id,
            'branch_id' => $reservation->branch_id,
            'stay_id' => $stay->id,
            'folio_number' => $folioNumber,
            'status' => FolioStatus::OPEN->value,
            'total_charges' => $totalAmount,
            'total_payments' => 0,
            'balance' => $totalAmount,
            'opened_at' => $checkInDate->copy()->setTime(14, 0),
        ]);

        // Post room charge line for each night
        for ($night = 0; $night < $nights; $night++) {
            $postingDate = $checkInDate->copy()->addDays($night);

            FolioLine::create([
                'folio_id' => $folio->id,
                'type' => FolioLineType::ROOM_CHARGE->value,
                'description' => $roomType->name . ' — Night ' . ($night + 1),
                'amount' => $rate,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'net_amount' => $rate,
                'reference_type' => 'reservation',
                'reference_id' => $reservation->id,
                'posting_date' => $postingDate->format('Y-m-d'),
            ]);
        }
    }

    /**
     * Generate a unique reservation number
     */
    private function generateUniqueReservationNumber(int $restaurantId, int $branchId, int $counter): string
    {
        $baseNumber = 'RES-' . $branchId . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
        $reservationNumber = $baseNumber;
        $maxAttempts = 1000;
        $attempt = 0;

        while (
            Reservation::where('reservation_number', $reservationNumber)->exists() && $attempt < $maxAttempts
        ) {
            $attempt++;
            $reservationNumber = $baseNumber . '-' . $attempt;
        }

        return $reservationNumber;
    }
}

