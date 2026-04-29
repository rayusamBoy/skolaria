<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Book\BookCreate;
use App\Http\Requests\Book\BookUpdate;
use App\Repositories\BookRepo;
use App\Repositories\MyClassRepo;

class BookController extends Controller
{
    protected $book, $my_class;

    public function __construct(BookRepo $book, MyClassRepo $my_class)
    {
        $this->permissionMap = config('permissions.names.tenant.books');
        parent::__construct();
        $this->book = $book;
        $this->my_class = $my_class;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $d["books"] = $this->book->getAll();

        return view('pages.books.index', $d);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookCreate $request)
    {
        $data = $request->except(["_token", "_method"]);
        $this->book->create($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($book)
    {
        $d["book"] = $this->book->find($book);

        return view("pages.books.edit", $d);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookUpdate $request, $book)
    {
        $data = $request->all();
        $this->book->update($book, $data);

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($book)
    {
        $this->book->find($book)->delete();

        return Qs::deleteOk('books.index');
    }
}
