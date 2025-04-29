<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Item;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function testItemHasReservationsRelationship(): void
    {
        $item = Item::factory()->create();
        $reservation = Reservation::factory()->create(['item_id' => $item->id]);

        $this->assertTrue($item->reservations->contains($reservation));
    }

    public function testItemHasImagesRelationship(): void
    {
        $item = Item::factory()->create();
        $image = \App\Models\ItemImage::factory()->create(['item_id' => $item->id]);

        $this->assertTrue($item->images->contains($image));
    }

    public function testItemHasProductsRelationship(): void
    {
        $item = Item::factory()->create();
        $product = Product::factory()->create();

        $item->products()->attach($product->id, ['quantity' => 5]);

        $this->assertTrue($item->products->contains($product));
        $this->assertEquals(5, $item->products->first()->pivot->quantity);
    }

    public function testAvailableQuantityReturnsCorrectValue(): void
    {
        $item = Item::factory()->create(['quantity' => 10]);
        $event = Event::factory()->create([
                                              'start_date' => now()->addDay(),
                                              'end_date' => now()->addDays(2),
                                          ]);
        Reservation::factory()->create([
                                           'item_id' => $item->id,
                                           'event_id' => $event->id,
                                           'quantity' => 4,
                                       ]);

        $available = $item->availableQuantity(
            now()->toDateString(), now()->addDays(3)->toDateString()
        );

        $this->assertEquals(6, $available);
    }

    public function testAvailableQuantityDoesNotGoNegative(): void
    {
        $item = Item::factory()->create(['quantity' => 3]);
        $event = Event::factory()->create([
                                              'start_date' => now()->addDay(),
                                              'end_date' => now()->addDays(2),
                                          ]);
        Reservation::factory()->create([
                                           'item_id' => $item->id,
                                           'event_id' => $event->id,
                                           'quantity' => 5,
                                       ]);

        $available = $item->availableQuantity(
            now()->toDateString(), now()->addDays(3)->toDateString()
        );

        $this->assertEquals(0, $available);
    }
}
