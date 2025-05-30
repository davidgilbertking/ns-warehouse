<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Product;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexPageIsAccessible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Product::factory()->count(3)->create();

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');
    }

    public function testCreatePageIsAccessible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('products.create');
    }

    public function testStoreCreatesProductAndRedirects(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $data = [
            'name' => 'Test Product',
            'items' => [
                ['id' => $item->id, 'quantity' => 5],
            ],
        ];

        $response = $this->post(route('products.store'), $data);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Тэг создан!');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
        ]);
    }

    public function testUpdateUpdatesProductAndRedirects(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();
        $items = Item::factory()->count(2)->create();

        $payload = [
            'name' => 'Updated Product',
            'items' => [
                ['id' => $items[0]->id, 'quantity' => 2],
                ['id' => $items[1]->id, 'quantity' => 4],
            ],
        ];

        $response = $this->patch(route('products.update', $product), $payload);

        $response->assertRedirect(route('products.show', $product));
        $response->assertSessionHas('success', 'Тэг обновлён!');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
        ]);
    }

    public function testDestroyDeletesProductAndRedirects(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Тэг удалён!');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function testItemsEndpointReturnsCorrectJson(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();
        $item = Item::factory()->create();

        $product->items()->attach($item->id, ['quantity' => 5]);

        $response = $this->get(route('products.items', $product));

        $response->assertStatus(200);
        $response->assertJsonStructure(['items' => [['id', 'name', 'quantity']]]);
        $response->assertJsonFragment([
                                          'id' => $item->id,
                                          'name' => $item->name,
                                          'quantity' => 5,
                                      ]);
    }
}
