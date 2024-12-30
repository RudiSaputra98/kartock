<?php

namespace App\Http\Controllers\Admin;

use App\Models\Note;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class NoteController extends Controller
{
    // public function store(Request $request)
    // {
    //     // Validasi input
    //     $request->validate([
    //         'note' => 'required|string|max:255',
    //     ]);

    //     // Menyimpan catatan ke database
    //     Note::create([
    //         'user_id' => Auth::id(),
    //         'content' => $request->note,
    //     ]);

    //     // Kembalikan respons JSON sukses
    //     return response()->json(['success' => true]);
    // }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'note' => 'required|string|max:255',
        ]);

        // Simpan catatan baru
        Note::create([
            'user_id' => Auth::id(),
            'content' => $request->note,
        ]);

        // Batasi jumlah catatan menjadi maksimal 10
        $notesCount = Note::count();
        if ($notesCount > 10) {
            $excessNotes = Note::orderBy('created_at', 'asc')->take($notesCount - 10)->get();
            foreach ($excessNotes as $note) {
                $note->delete();
            }
        }

        return response()->json(['success' => true]);
    }
}
