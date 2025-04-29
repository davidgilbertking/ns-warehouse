<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function testActivityLogBelongsToUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $log = ActivityLog::create([
                                       'user_id' => $user->id,
                                       'action' => 'created_item',
                                       'entity_type' => 'Item',
                                       'entity_id' => 1,
                                       'description' => 'Created a new item.',
                                   ]);

        // Act
        $relatedUser = $log->user;

        // Assert
        $this->assertInstanceOf(User::class, $relatedUser);
        $this->assertEquals($user->id, $relatedUser->id);
    }
}
