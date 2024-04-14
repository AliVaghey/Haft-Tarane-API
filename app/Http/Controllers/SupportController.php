<?php

namespace App\Http\Controllers;

use App\Models\Support;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SupportController extends Controller
{
    /**
     * Create new support.
     */
    public function new(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
        ]);
        $agency = $request->user();

        $support = Support::make([
            'agency_id' => $agency->id,
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        try {
            Gate::authorize('isRepeated', $support);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $support->save();
        return response()->noContent();
    }

    /**
     * Get all the supports.
     */
    public function getAll(Request $request)
    {
        return $request->query('name') ?
            $request->user()->supports()->where('name', $request->query('name'))->get() :
            $request->user()->supports;
    }

    /**
     * Edit an existing support.
     */
    public function edit(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
        ]);
        if (!$support = Support::find($id)) {
            return response(['message' => __('exceptions.support-not-found')], 404);
        }

        $support->fill([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        try {
            Gate::authorize('isRepeated', $support);
            Gate::authorize('isOwner', $support);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $support->save();

        return response()->noContent();
    }

    /**
     * Delete a support.
     */
    public function delete(Request $request, $id)
    {
        if (!$support = Support::find($id)) {
            return response(['message' => __('exceptions.support-not-found')], 404);
        }
        try {
            Gate::authorize('isOwner', $support);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()]);
        }
        $support->delete();

        return response()->noContent();
    }
}
