<?php

namespace App\Http\Controllers;

use App\Models\TourReservation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ReservationFileController extends Controller
{
    public function upload(Request $request, TourReservation $reservation)
    {
        try {
            Gate::authorize('isTourOwner', $reservation->tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $files = $reservation->files;
        foreach ($request->allFiles() as $name => $file) {
            if ($files->files->has($name)) {
                return response(['message' => "فایل $name موجود می باشد."]);
            }
            $files->files->put($name, $file->store('reservation-files', 'public'));
        }
        $files->save();

        return response()->noContent();
    }

    public function remove(Request $request, TourReservation $reservation)
    {
        $files = $reservation->files;
        $name = $request->query('name');
        if ($name && $files->files->has($name)) {
            $path = $files->files->get($name);
            Storage::disk('public')->delete($path);
            $files->files->forget($name);
            $files->save();
        }
        return response()->noContent();
    }
}
