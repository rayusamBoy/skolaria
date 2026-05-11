<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Book\BookCreate;
use App\Http\Requests\Book\BookUpdate;
use App\Repositories\BookRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookController extends Controller
{
    protected BookRepo $book;
    protected MyClassRepo $my_class;

    public function __construct(BookRepo $book, MyClassRepo $my_class)
    {
        $this->permissionMap = config('permissions.names.tenant.books');
        parent::__construct();
        $this->book = $book;
        $this->my_class = $my_class;
    }

    /**
     * Display a listing of books.
     */
    public function index(): View
    {
        $data['books'] = $this->book->getAllBooks();

        return view('pages.books.index', $data);
    }

    /**
     * Store a newly created book.
     */
    public function store(BookCreate $request): JsonResponse
    {
        $data = $request->except(['_token', '_method']);
        $this->book->create($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit($book): View
    {
        $data['book'] = $this->book->find($book);

        return view('pages.books.edit', $data);
    }

    /**
     * Update the specified book.
     */
    public function update(BookUpdate $request, $book): JsonResponse
    {
        $data = $request->except(['_method', '_token']);
        $this->book->update($book, $data);

        return Qs::jsonUpdateOk();
    }

    public function show($book): View
    {
        $data['book'] = $this->book->find($book);

        return view('pages.books.show', $data);
    }

    /**
     * Remove the specified book.
     */
    public function destroy($book): RedirectResponse
    {
        $this->book->find($book)->delete();

        return Qs::redirectDeleteSuccess('books.index');
    }
}
