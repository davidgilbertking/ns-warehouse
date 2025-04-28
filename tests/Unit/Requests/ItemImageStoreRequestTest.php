<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\ItemImageStoreRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ItemImageStoreRequestTest extends TestCase
{
    public function testRulesPassWithValidImage(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('photo.jpg', 600, 600)->size(1000);

        $data = ['image' => $file];

        $request = new ItemImageStoreRequest();

        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testRulesFailWithMissingImage(): void
    {
        // Arrange
        $data = [];

        $request = new ItemImageStoreRequest();

        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('image', $validator->errors()->messages());
    }

    public function testRulesFailWithInvalidFileType(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $data = ['image' => $file];

        $request = new ItemImageStoreRequest();

        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('image', $validator->errors()->messages());
    }

    public function testRulesFailWithTooLargeImage(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('large-photo.jpg')->size(5000); // 5000 KB > 4096 KB

        $data = ['image' => $file];

        $request = new ItemImageStoreRequest();

        $validator = Validator::make($data, $request->rules());

        // Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('image', $validator->errors()->messages());
    }
}
