<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Event;
use App\Models\Item;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_successful_response(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->actingAs($user)
            ->get(route('items.index'))
            ->assertOk()
            ->assertViewIs('items.index');
    }

    public function test_items_and_tasks_index_do_not_render_date_filters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('items.index', ['depth' => 0]))
            ->assertOk()
            ->assertDontSee('id="available_from"', false)
            ->assertDontSee('id="available_to"', false)
            ->assertDontSee('dateErrorModal');

        $this->actingAs($user)
            ->get(route('items.index', ['depth' => 1]))
            ->assertOk()
            ->assertDontSee('id="available_from"', false)
            ->assertDontSee('id="available_to"', false)
            ->assertDontSee('dateErrorModal');
    }

    public function test_create_returns_successful_response_for_authorized_user(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role' => 'admin', // или любая роль, у которой есть права
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('items.create'))
            ->assertOk()
            ->assertViewIs('items.create');
    }

    public function test_store_creates_new_item(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role' => 'admin', // или любой не viewer
        ]);

        $data = [
            'depth' => 1,
            'name' => 'Test Item',
            'description' => 'Test Description',
            'quantity' => 5,
            'size' => 'Medium',
            'material' => 'Steel',
            'supplier' => 'Supplier A',
            'storage_location' => 'Shelf A1',
            'mechanics' => 'Standard procedure',
            'scalability' => 'Подходит для мероприятий от 10 до 1000 человек',
            'client_price' => '15000',
            'op_media' => ['https://example.com/op1.jpg', 'https://example.com/op2.jpg'],
            'branding_options' => 'Можно нанести логотип клиента',
            'adaptation_options' => 'Используется с разными размерами сцены',
            'op_price' => '7000',
            'real_media' => ['https://example.com/real1.jpg'],
            'construction_description' => 'Каркас из металла, обтянут тканью',
            'contractor' => 'ИП Иванов',
            'production_cost' => '4500',
            'change_history' => 'Перекрашен в 2023',
            'consumables' => 'Болты, гайки',
            'implementation_comments' => 'Требует двоих для монтажа',
            'mounting' => 'Сборка 30 мин, разборка 15 мин',
            'storage_features' => 'Хранить в сухом помещении',
            'storage_place' => 'Склад 1, стеллаж 3B',
            'design_links' => 'https://example.com/designs',
            'event_history' => "12.03.2024 — Мероприятие А\n01.02.2024 — Презентация Б",
            'event_media' => ['https://example.com/event1.jpg'],
        ];

        // Act & Assert
        $this->actingAs($user)
            ->post(route('items.store'), $data)
            ->assertRedirect(route('items.index', ['depth' => 1]))
            ->assertSessionHas('success', 'Создано: Предмет');

        // Проверяем, что предмет действительно создан в базе
        $this->assertDatabaseHas('items', [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'quantity' => 5,
        ]);
    }

    public function test_show_returns_successful_response(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $item = Item::create([
            'name' => 'Test Show Item',
            'description' => 'Description',
            'quantity' => 10,
            'size' => 'Medium',
            'material' => 'Steel',
            'supplier' => 'Supplier X',
            'storage_location' => 'Shelf B1',
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertViewIs('items.show')
            ->assertViewHas('item', function ($viewItem) use ($item) {
                return $viewItem->id === $item->id;
            });
    }

    public function test_edit_returns_successful_response_for_authorized_user(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $item = Item::create([
            'name' => 'Test Edit Item',
            'description' => 'Description',
            'quantity' => 7,
            'size' => 'Small',
            'material' => 'Plastic',
            'supplier' => 'Supplier Y',
            'storage_location' => 'Shelf C1',
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('items.edit', $item))
            ->assertOk()
            ->assertViewIs('items.edit')
            ->assertViewHas('item', function ($viewItem) use ($item) {
                return $viewItem->id === $item->id;
            });
    }

    public function test_update_updates_item(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $item = Item::create([
            'depth' => 1,
            'name' => 'Old Name',
            'description' => 'Old Description',
            'quantity' => 3,
            'size' => 'Small',
            'material' => 'Wood',
            'supplier' => 'Supplier Old',
            'storage_location' => 'Shelf D1',
        ]);

        $updateData = [
            'name' => 'New Name',
            'description' => 'New Description',
            'quantity' => 8,
            'size' => 'Large',
            'material' => 'Metal',
            'supplier' => 'Supplier New',
            'storage_location' => 'Shelf D2',
        ];

        // Act & Assert
        $this->actingAs($user)
            ->put(route('items.update', $item), $updateData)
            ->assertRedirect(route('items.show', $item))
            ->assertSessionHas('success', 'Обновлено: Предмет');

        // Проверка что в базе данные реально обновились
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'New Name',
            'description' => 'New Description',
            'quantity' => 8,
        ]);
    }

    public function test_destroy_deletes_item(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $item = Item::create([
            'depth' => 1,
            'name' => 'Item To Delete',
            'description' => 'Description',
            'quantity' => 5,
            'size' => 'Medium',
            'material' => 'Steel',
            'supplier' => 'Supplier Delete',
            'storage_location' => 'Shelf E1',
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->delete(route('items.destroy', $item))
            ->assertRedirect(route('items.index', ['depth' => 1]))
            ->assertSessionHas('success', 'Удалено: Предмет');

        $this->assertSoftDeleted('items', [
            'id' => $item->id,
        ]);
    }

    public function test_destroy_redirect_preserves_current_filters(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $item = Item::factory()->create([
            'depth' => 1,
            'name' => 'Filtered Item To Delete',
        ]);
        $filters = [
            'depth' => 1,
            'search' => 'Filtered',
            'storage_place' => 'Shelf E1',
            'without_parent_items' => 1,
            'page' => 2,
        ];

        $this->actingAs($user)
            ->delete(route('items.destroy', ['item' => $item] + $filters))
            ->assertRedirect(route('items.index', $filters))
            ->assertSessionHas('success', 'Удалено: Предмет');

        $this->assertSoftDeleted('items', [
            'id' => $item->id,
        ]);
    }

    public function test_index_delete_action_preserves_current_filters(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);
        $item = Item::factory()->create([
            'depth' => 1,
            'name' => 'Filtered Delete Button Item',
            'storage_place' => 'Shelf E1',
        ]);
        $filters = [
            'depth' => 1,
            'search' => 'Filtered Delete',
            'storage_place' => 'Shelf E1',
            'without_parent_items' => 1,
        ];

        $expectedAction = route('items.destroy', ['item' => $item] + $filters);

        $this->actingAs($user)
            ->get(route('items.index', $filters))
            ->assertOk()
            ->assertSee('data-action="'.e($expectedAction).'"', false);
    }

    public function test_destroy_fails_if_item_has_reservations_or_products(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $item = Item::create([
            'depth' => 1,
            'name' => 'Protected Item',
            'description' => 'Cannot delete',
            'quantity' => 5,
            'size' => 'Small',
            'material' => 'Plastic',
            'supplier' => 'Supplier Protected',
            'storage_location' => 'Shelf F1',
        ]);

        $event = Event::factory()->create([
            'user_id' => $user->id,
        ]);

        Reservation::create([
            'item_id' => $item->id,
            'event_id' => $event->id,
            'quantity' => 1,
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->delete(route('items.destroy', $item))
            ->assertRedirect()
            ->assertSessionHas('error', 'Нельзя удалить Предмет, которое зарезервировано в мероприятии.');

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
        ]);
    }

    public function test_export_returns_csv_download(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        Item::create([
            'name' => 'Exported Item',
            'description' => 'For Export',
            'quantity' => 15,
            'size' => 'Large',
            'material' => 'Aluminum',
            'supplier' => 'Supplier Export',
            'storage_location' => 'Shelf X1',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('items.export'));

        // Assert
        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $response->assertHeader('Content-Disposition');

        $this->assertStringContainsString('Exported Item', $response->getContent());
    }

    public function test_store_depth_one_item_attaches_parent_items(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $parentItem = Item::factory()->create([
            'depth' => 0,
            'name' => 'Задание с кубиком',
        ]);

        $this->actingAs($user)
            ->post(route('items.store'), [
                'depth' => 1,
                'name' => 'Поролоновый кубик',
                'quantity' => 1,
                'parent_items' => [
                    $parentItem->id => [
                        'selected' => 1,
                        'quantity' => 3,
                    ],
                ],
            ])
            ->assertRedirect(route('items.index', ['depth' => 1]))
            ->assertSessionHas('success', 'Создано: Предмет');

        $item = Item::where('name', 'Поролоновый кубик')->firstOrFail();

        $this->assertDatabaseHas('item_subitem', [
            'item_id' => $parentItem->id,
            'subitem_id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function test_update_depth_one_item_syncs_parent_items(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $oldParentItem = Item::factory()->create(['depth' => 0]);
        $newParentItem = Item::factory()->create(['depth' => 0]);
        $item = Item::factory()->create([
            'depth' => 1,
            'name' => 'Крестик-нолик',
            'quantity' => 1,
        ]);

        $item->parentItems()->attach($oldParentItem->id, ['quantity' => 1]);

        $this->actingAs($user)
            ->put(route('items.update', $item), [
                'name' => 'Крестик-нолик обновленный',
                'quantity' => 2,
                'parent_items' => [
                    $newParentItem->id => [
                        'selected' => 1,
                        'quantity' => 4,
                    ],
                ],
            ])
            ->assertRedirect(route('items.show', $item))
            ->assertSessionHas('success', 'Обновлено: Предмет');

        $this->assertDatabaseMissing('item_subitem', [
            'item_id' => $oldParentItem->id,
            'subitem_id' => $item->id,
        ]);

        $this->assertDatabaseHas('item_subitem', [
            'item_id' => $newParentItem->id,
            'subitem_id' => $item->id,
            'quantity' => 4,
        ]);
    }

    public function test_show_depth_one_item_displays_parent_item_links_with_quantity(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $parentItem = Item::factory()->create([
            'depth' => 0,
            'name' => 'Задание для просмотра',
        ]);
        $item = Item::factory()->create([
            'depth' => 1,
            'name' => 'Просматриваемый предмет',
        ]);

        $item->parentItems()->attach($parentItem->id, ['quantity' => 2]);

        $this->actingAs($user)
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('Задания')
            ->assertSee($parentItem->name)
            ->assertSee('href="'.route('items.show', $parentItem).'"', false)
            ->assertSee('× 2', false);
    }

    public function test_subitem_search_is_case_insensitive_for_cyrillic(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create([
            'depth' => 1,
            'name' => 'Поролоновый Кубик',
        ]);

        $this->actingAs($user)
            ->getJson(route('api.items.search-subitems', ['q' => 'кубик']))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $item->id,
                'name' => 'Поролоновый Кубик',
            ]);
    }

    public function test_parent_item_search_is_case_insensitive_for_cyrillic(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $parentItem = Item::factory()->create([
            'depth' => 0,
            'name' => 'Задание Крестики-Нолики',
        ]);

        $this->actingAs($user)
            ->getJson(route('api.items.search-parent-items', ['q' => 'крестики']))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $parentItem->id,
                'name' => 'Задание Крестики-Нолики',
            ]);
    }

    public function test_depth_one_index_displays_parent_item_links(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $parentItem = Item::factory()->create([
            'depth' => 0,
            'name' => 'Задание со ссылкой',
        ]);
        $item = Item::factory()->create([
            'depth' => 1,
            'name' => 'Предмет в задании',
        ]);

        $item->parentItems()->attach($parentItem->id, ['quantity' => 1]);

        $this->actingAs($user)
            ->get(route('items.index', ['depth' => 1]))
            ->assertOk()
            ->assertSee('Задания')
            ->assertSee('Кол-во')
            ->assertSee($parentItem->name)
            ->assertSee('href="'.route('items.show', $parentItem).'"', false);
    }

    public function test_depth_zero_index_does_not_display_parent_items_column(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Item::factory()->create([
            'depth' => 0,
            'name' => 'Задание без колонки',
        ]);

        $this->actingAs($user)
            ->get(route('items.index'))
            ->assertOk()
            ->assertDontSee('<th>Задания</th>', false)
            ->assertSee('Кол-во');
    }

    public function test_depth_one_index_can_filter_items_without_parent_items(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $parentItem = Item::factory()->create(['depth' => 0]);
        $linkedItem = Item::factory()->create([
            'depth' => 1,
            'name' => 'Привязанный предмет',
        ]);
        $unlinkedItem = Item::factory()->create([
            'depth' => 1,
            'name' => 'Непривязанный предмет',
        ]);

        $linkedItem->parentItems()->attach($parentItem->id, ['quantity' => 1]);

        $this->actingAs($user)
            ->get(route('items.index', [
                'depth' => 1,
                'without_parent_items' => 1,
            ]))
            ->assertOk()
            ->assertSee('Без заданий')
            ->assertSee('id="without_parent_items"', false)
            ->assertSee('checked', false)
            ->assertSee($unlinkedItem->name)
            ->assertDontSee($linkedItem->name);
    }
}
