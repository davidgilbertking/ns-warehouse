<?php

namespace App\Http\Controllers;

use App\Models\ItemImage;
use Illuminate\Support\Facades\Storage;

class ItemImageController extends Controller
{
    public function destroy(ItemImage $image)
    {
        $item = $image->item;

        // Удаляем файл
        \Storage::disk('public')->delete($image->path);

        // Удаляем запись в базе
        $image->delete();

        // Возвращаемся на страницу редактирования предмета
        return redirect()->route('items.edit', $item)->with('success', 'Фото удалено.');
    }

    public function store(\Illuminate\Http\Request $request, \App\Models\Item $item)
    {
        $request->validate([
                               'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                           ]);

        $path = $request->file('image')->store('items', 'public');

        $item->images()->create([
                                    'path' => $path,
                                ]);

        return redirect()->route('items.edit', $item)->with('success', 'Фото загружено.');
    }

}
