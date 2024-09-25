<?php

namespace App\Http\Controllers;

use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function all()
    {
        return BannerResource::collection(Banner::all());
    }

    public function read(Banner $banner)
    {
        return new BannerResource($banner);
    }

    public function create(Request $request)
    {
        $request->validate([
            'photo' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:255'],
            'description_color' => ['nullable', 'string'],
        ]);

        $photo_path = null;
        if($request->hasFile('photo')) {
            $photo_path = $request->file('photo')->store('slider-cards', 'public');
        }
        $banner = Banner::create([
            'photo' => $photo_path,
            'description' => $request->description,
            'description_color' => $request->description_color ?? '#000000',
            'link' => $request->link
        ]);

        return response(new BannerResource($banner), 201);
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'photo' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:255'],
            'description_color' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($banner->photo);
            $banner->photo = $request->file('photo')->store('slider-cards', 'public');
        }
        $banner->fill([
            'description' => $request->description,
            'link' => $request->link,
            'description_color' => $request->description_color ?? '#000000',
        ])->save();

        return new BannerResource($banner);
    }

    public function delete(Banner $banner)
    {
        $banner->delete();
        return response()->noContent();
    }
}
