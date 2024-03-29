<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Kategori;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardBeritaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('dashboard.berita.index', [
            // 'semuaBerita' => Berita::all(),
            'jumlahData' => Berita::get('id')->count(),
            "semuaBerita"=> Berita::latest()->filter(request(['search','kategori', 'author']))->paginate(20)->withQueryString()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.berita.create', [
            'categories' => Kategori::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);
        $validateData = $request->validate([
            'title' => 'required',
            'slug' => 'required|unique:beritas',
            'kategori_id' => 'required',
            'image' => 'image|file',
            'konten' => 'required'
        ]);

        if ($request->file('image')) {
            $validateData['image'] = $request->file('image')->store('berita-images');
        }

        $validateData['user_id'] = auth()->user()->id;
        $validateData['excerpt'] = Str::limit(strip_tags($request->konten), 40, '...');
        Berita::create($validateData);
        return redirect('/dashboard/berita')->with('success', 'New berita has been added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Berita  $berita
     * @return \Illuminate\Http\Response
     */
    public function show(Berita $berita)
    {
        // dd($berita);
        return view('dashboard.berita.show', [
            // Detail Berita
            "title" => "Detail Berita",
            "detailBerita" => $berita,
            "semuaKategori" => Kategori::all(),

        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Berita  $berita
     * @return \Illuminate\Http\Response
     */
    public function edit(Berita $berita)
    {
        return view('dashboard.berita.edit', [
            'berita' => $berita,
            'categories' => Kategori::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Berita  $berita
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Berita $berita)
    {
        // dd($request);
        $rules = [
            'title' => 'required',
            'kategori_id' => 'required',
            'image' => 'image|file',
            'konten' => 'required',
        ];

        if ($request->slug != $berita->slug) {
            $rules['slug'] = 'required|unique:beritas';
        }

        $validateData = $request->validate($rules);

        //  Jika ada gambar baru
        if ($request->file('image')) {
            // jika gambar lamanya ada, maka hapus gambar
            if ($request->oldImage) {
                Storage::delete($request->oldImage);
            }
            // jika tidak ada gambar lama, lakukan penyimpanan kestorage folder "berita-image"
            $validateData['image'] = $request->file('image')->store('berita-images');
        }

        $validateData['user_id'] = auth()->user()->id;
        $validateData['excerpt'] = Str::limit(strip_tags($request->konten), 100, '...');

        Berita::where('id', $berita->id)
            ->update($validateData);
        return redirect('/dashboard/berita')->with('success', 'Berita berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Berita  $berita
     * @return \Illuminate\Http\Response
     */
    public function destroy(Berita $berita)
    {
        // hapus gambar dengan nama yang sesuai di tabel berita field image
        if ($berita->image) {
            Storage::delete($berita->image);
        }
        Berita::destroy($berita->id);
        return redirect('/dashboard/berita')->with('success', 'Berita berhasil dihapus!');
    }

    // public function checkSlug(Request $request)
    // {
    //     $slug = SlugService::createSlug(Berita::class, 'slug', $request->title);
    //     return response()->json(['slug' => $slug]);
    // }
}
