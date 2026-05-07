<?php

namespace Tests\Feature;

use App\Events\ReservationCancelled;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ReservationCancellationTest extends TestCase
{
    use DatabaseTransactions;

    // Always returns a future weekday within the reservation window
    private function nextWeekday(): string
    {
        return today()->next(Carbon::MONDAY)->toDateString();
    }

    public function test_user_can_cancel_own_reservation(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'date' => $this->nextWeekday(),
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/reservations/{$reservation->id}")
            ->assertNoContent();

        $reservation->refresh();
        $this->assertEquals('cancelled', $reservation->status);
        $this->assertNotNull($reservation->cancelled_at);
        $this->assertEquals($user->id, $reservation->cancelled_by);
        $this->assertEquals('User cancelled', $reservation->cancellation_reason);

        Event::assertDispatched(
            ReservationCancelled::class,
            fn ($e) => $e->reservation->id === $reservation->id
        );
    }

    public function test_cancellation_reason_is_stored_when_provided(): void
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'date' => $this->nextWeekday(),
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/reservations/{$reservation->id}", ['reason' => 'Not coming to office'])
            ->assertNoContent();

        $this->assertEquals('Not coming to office', $reservation->fresh()->cancellation_reason);
    }

    public function test_user_cannot_cancel_another_users_reservation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $other->id,
            'date' => $this->nextWeekday(),
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/reservations/{$reservation->id}")
            ->assertForbidden();

        $this->assertEquals('confirmed', $reservation->fresh()->status);
    }

    public function test_cannot_cancel_after_cutoff(): void
    {
        $user = User::factory()->create();
        $date = $this->nextWeekday();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'date' => $date,
        ]);

        // Travel to the reservation day itself — past the cutoff (day before at 23:59)
        Carbon::setTestNow(Carbon::parse($date)->startOfDay());

        $response = $this->actingAs($user)
            ->deleteJson("/api/reservations/{$reservation->id}");

        Carbon::setTestNow(null);

        $response->assertForbidden();
        $this->assertEquals('confirmed', $reservation->fresh()->status);
    }

    public function test_cancelled_spot_can_be_reserved_by_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $date = $this->nextWeekday();
        $reservation = Reservation::factory()->create([
            'user_id' => $owner->id,
            'date' => $date,
        ]);

        $spaceId = $reservation->parking_space_id;

        $this->actingAs($owner)
            ->deleteJson("/api/reservations/{$reservation->id}")
            ->assertNoContent();

        $this->actingAs($other)
            ->postJson('/api/reservations', [
                'type' => 'single',
                'date' => $date,
                'parking_space_id' => $spaceId,
            ])
            ->assertOk();

        $this->assertDatabaseHas('reservations', [
            'user_id' => $other->id,
            'parking_space_id' => $spaceId,
            'date' => $date,
            'status' => 'confirmed',
        ]);
    }

    public function test_unauthenticated_user_cannot_cancel(): void
    {
        $reservation = Reservation::factory()->create([
            'date' => $this->nextWeekday(),
        ]);

        $this->deleteJson("/api/reservations/{$reservation->id}")
            ->assertUnauthorized();
    }

    public function test_admin_can_cancel_any_reservation(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'date' => $this->nextWeekday(),
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/reservations/{$reservation->id}")
            ->assertNoContent();

        $this->assertEquals('cancelled', $reservation->fresh()->status);
        $this->assertEquals($admin->id, $reservation->fresh()->cancelled_by);
    }
}
