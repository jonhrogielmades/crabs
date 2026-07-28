<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;

class SpeciesController extends Controller
{
    public function show(CrabSpecies $crabSpecies)
    {
        abort_unless($crabSpecies->is_active, 404);

        return view('species.show', [
            'species' => $crabSpecies->loadCount('recognitionRecords'),
            'recentRecords' => auth()->check()
                ? $crabSpecies->recognitionRecords()->whereBelongsTo(auth()->user())->latest()->limit(6)->get()
                : collect(),
            'relatedSpecies' => CrabSpecies::where('is_active', true)
                ->where('id', '!=', $crabSpecies->id)
                ->when($crabSpecies->family, fn ($query) => $query->where('family', $crabSpecies->family))
                ->orderBy('common_name')
                ->limit(4)
                ->get(),
        ]);
    }
}
