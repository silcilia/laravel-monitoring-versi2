<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /**
     * 🔥 KONSTANTA MAKSIMAL KONTAK
     */
    private const MAX_CONTACTS = 10;

    /**
     * Display a listing of the contacts.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        
        $totalContacts = Contact::count();
        $totalActive = Contact::where('is_active', true)->count();
        $totalInactive = Contact::where('is_active', false)->count();
        
        $contacts = Contact::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
        
        return view('contacts', compact(
            'contacts',
            'totalContacts',
            'totalActive',
            'totalInactive',
            'perPage'
        ));
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create()
    {
        return redirect()->route('contacts');
    }

    /**
     * Store a newly created contact in storage.
     * 🔥 PERUBAHAN: Dibatasi maksimal 10 kontak
     */
    public function store(Request $request)
    {
        try {
            // 🔥 CEK JUMLAH KONTAK SEBELUM MENAMBAH
            $currentCount = Contact::count();
            
            if ($currentCount >= self::MAX_CONTACTS) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', '⚠️ Maksimal kontak adalah ' . self::MAX_CONTACTS . ' kontak. 
                        Saat ini sudah ada ' . $currentCount . ' kontak. 
                        Hapus kontak yang tidak digunakan terlebih dahulu.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15|unique:contacts,phone',
                'is_active' => 'boolean'
            ]);

            $contact = Contact::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'is_active' => $request->is_active ?? 1
            ]);

            return redirect()
                ->route('contacts')
                ->with('success', '✅ Kontak "' . $contact->name . '" berhasil ditambahkan. 
                    (' . ($currentCount + 1) . '/' . self::MAX_CONTACTS . ')');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit($id)
    {
        $contact = Contact::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $contact
            ]);
        }
        
        return redirect()->route('contacts');
    }

    /**
     * Update the specified contact in storage.
     * 🔥 PERUBAHAN: Tidak ada batasan untuk update (boleh berapa saja)
     */
    public function update(Request $request, $id)
    {
        try {
            $contact = Contact::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15|unique:contacts,phone,' . $id,
                'is_active' => 'boolean'
            ]);

            $contact->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'is_active' => $request->is_active ?? 1
            ]);

            return redirect()
                ->route('contacts')
                ->with('success', '✅ Kontak "' . $contact->name . '" berhasil diupdate');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            $contactName = $contact->name;
            
            // 🔥 CEK SEBELUM DELETE
            $currentCount = Contact::count();
            $contact->delete();

            return redirect()
                ->route('contacts')
                ->with('success', '✅ Kontak "' . $contactName . '" berhasil dihapus. 
                    (' . ($currentCount - 1) . '/' . self::MAX_CONTACTS . ' kontak tersisa)');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Search contacts (AJAX)
     * GET /contacts/search
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $perPage = $request->input('per_page', 10);
            
            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'pagination' => [
                        'total' => 0,
                        'from' => 0,
                        'to' => 0,
                        'current_page' => 1,
                        'last_page' => 1,
                        'prev_page_url' => null,
                        'next_page_url' => null
                    ]
                ]);
            }
            
            $contacts = Contact::where('name', 'LIKE', "%{$query}%")
                ->orWhere('phone', 'LIKE', "%{$query}%")
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $contacts->items(),
                'pagination' => [
                    'total' => $contacts->total(),
                    'from' => $contacts->firstItem(),
                    'to' => $contacts->lastItem(),
                    'current_page' => $contacts->currentPage(),
                    'last_page' => $contacts->lastPage(),
                    'prev_page_url' => $contacts->previousPageUrl(),
                    'next_page_url' => $contacts->nextPageUrl()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================================================================
    // 🔥 METHOD TAMBAHAN UNTUK KEBUTUHAN FUTURE
    // ================================================================

    /**
     * 🔥 CEK APAKAH MASIH BISA TAMBAH KONTAK
     * 
     * @return array [
     *   'can_add' => bool,
     *   'current' => int,
     *   'max' => int,
     *   'remaining' => int
     * ]
     */
    public function checkAvailability()
    {
        $current = Contact::count();
        $max = self::MAX_CONTACTS;
        
        return response()->json([
            'success' => true,
            'data' => [
                'can_add' => $current < $max,
                'current' => $current,
                'max' => $max,
                'remaining' => $max - $current,
                'message' => $current < $max 
                    ? 'Masih bisa menambah ' . ($max - $current) . ' kontak lagi'
                    : 'Kontak sudah mencapai batas maksimal (' . $max . ' kontak)'
            ]
        ]);
    }

    /**
     * 🔥 BULK DELETE KONTAK (untuk future)
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada kontak yang dipilih'
                ], 400);
            }

            $deletedCount = Contact::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} kontak",
                'deleted_count' => $deletedCount,
                'remaining' => Contact::count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kontak: ' . $e->getMessage()
            ], 500);
        }
    }
}