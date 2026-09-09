<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\Book\BookRequestCreate;
use App\Http\Requests\Book\BookRequestUpdate;
use App\Repositories\BookRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookRequestController extends Controller
{
    protected BookRepo $book;

    protected UserRepo $user;

    public function __construct(BookRepo $book, UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.tenant.book_requests');
        parent::__construct();
        $this->book = $book;
        $this->user = $user;
    }

    /**
     * Display a listing of book requests.
     */
    public function index(): View
    {
        $data['requests'] = $this->book->getAllBookRequestsWithRelations();
        $data['books'] = $this->book->getAllBooks();
        $data['users'] = $this->user->getAllUsers();

        return view('pages.books.requests.index', $data);
    }

    /**
     * Store a newly created book request.
     */
    public function store(BookRequestCreate $request): JsonResponse
    {
        $data = $request->except(['_token', '_method']);
        $book = $this->book->find($data['book_id']);

        if ($book->issued_copies >= $book->total_copies) {
            return Qs::json(__('msg.all_book_copies_issued'), false);
        }

        $data['user_id'] = auth()->id();
        $this->book->createBookRequest($data);
        $this->book->incrementColumn(['id' => $book->id], 'issued_copies');

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified book request.
     */
    public function edit($book_request): View
    {
        $data['book_request'] = $this->book->findBookRequest($book_request);
        $data['books'] = $this->book->getAllBooks();

        return view('pages.books.requests.edit', $data);
    }

    /**
     * Update the specified book request.
     */
    public function update(BookRequestUpdate $request, $book_request): JsonResponse
    {
        $data = $request->all();
        $book_id = $data['book_id'];
        $this->book->updateBookRequest($book_request, $data);

        $book = $this->book->find($book_id);

        if ($request->boolean('is_returned') === true) {
            $this->book->decrementColumn(['id' => $book_id], 'issued_copies');
        } elseif ($request->was_returned == 1 && $request->boolean('is_returned') === false) {
            if ($book->issued_copies >= $book->total_copies) {
                return Qs::json(__('msg.all_book_copies_issued'), false);
            }
            $this->book->incrementColumn(['id' => $book_id], 'issued_copies');
        }

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified book request.
     */
    public function destroy($book_request): RedirectResponse
    {
        $book_request = $this->book->findBookRequest($book_request);
        $book_id = $book_request->book_id;
        $book_request->delete();

        $this->book->decrementColumn(['id' => $book_id], 'issued_copies');

        return Qs::redirectDeleteSuccess('book_requests.index');
    }
}
