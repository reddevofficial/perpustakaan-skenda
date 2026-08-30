<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ReservationServiceTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBaseTables();
        $this->seedSettings();
    }

    public function test_create_a_reservation_with_queue_position(): void
    {
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);

        $service = app(ReservationService::class);
        $reservation = $service->create($student, $book);

        $this->assertNotNull($reservation);
        $this->assertNotNull($reservation->reservation_number);
        $this->assertStringStartsWith('RSV-', $reservation->reservation_number);
        $this->assertEquals($student->id, $reservation->murid_id);
        $this->assertEquals($book->id, $reservation->book_id);
        $this->assertEquals(ReservationStatus::WAITING, $reservation->status);
        $this->assertEquals(1, $reservation->queue_position);
    }

    public function test_second_reservation_gets_incremented_queue_position(): void
    {
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);

        $service = app(ReservationService::class);
        $reservation1 = $service->create($student1, $book);
        $reservation2 = $service->create($student2, $book);

        $this->assertEquals(1, $reservation1->queue_position);
        $this->assertEquals(2, $reservation2->queue_position);
    }

    public function test_duplicate_reservation_throws_exception(): void
    {
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);

        $service = app(ReservationService::class);
        $service->create($student, $book);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Anda sudah memiliki reservasi aktif untuk buku ini.');

        $service->create($student, $book);
    }

    public function test_cancel_a_reservation(): void
    {
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);

        $service = app(ReservationService::class);
        $reservation = $service->create($student, $book);

        $service->cancel($reservation);

        $reservation->refresh();
        $this->assertEquals(ReservationStatus::CANCELLED, $reservation->status);
        $this->assertNotNull($reservation->cancelled_at);
    }

    public function test_expire_reservations_promotes_next_in_queue(): void
    {
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);

        $service = app(ReservationService::class);
        $reservation1 = $service->create($student1, $book);
        $reservation2 = $service->create($student2, $book);

        $reservation1->update([
            'status' => ReservationStatus::AVAILABLE,
            'expires_at' => now()->subDay(),
        ]);

        $service->expireReservations();

        $reservation1->refresh();
        $reservation2->refresh();

        $this->assertEquals(ReservationStatus::EXPIRED, $reservation1->status);
        $this->assertEquals(ReservationStatus::AVAILABLE, $reservation2->status);
        $this->assertNotNull($reservation2->expires_at);
        $this->assertTrue($reservation2->expires_at->isFuture());
    }
}
