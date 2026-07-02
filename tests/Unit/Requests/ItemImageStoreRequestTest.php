<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\ItemImageStoreRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ItemImageStoreRequestTest extends TestCase
{
    public function test_rules_pass_with_valid_image(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('photo.jpg', 600, 600)->size(1000);

        $data = ['image' => $file];

        $request = new ItemImageStoreRequest;

        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function test_rules_fail_with_missing_image(): void
    {
        // Arrange
        $data = [];

        $request = new ItemImageStoreRequest;

        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('image', $validator->errors()->messages());
    }

    public function test_rules_fail_with_invalid_file_type(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $data = ['image' => $file];

        $request = new ItemImageStoreRequest;

        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('image', $validator->errors()->messages());
    }

    public function test_rules_fail_with_too_large_image(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('large-photo.jpg')->size(51201);

        $data = ['image' => $file];

        $request = new ItemImageStoreRequest;

        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('image', $validator->errors()->messages());
    }
}
