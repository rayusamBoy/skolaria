<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Book\BookRequestCreate;
use App\Http\Requests\Book\BookRequestUpdate;
use App\Repositories\BookRepo;
use App\Repositories\UserRepo;

class BookRequestController extends Controller
{
    protected $book, $user;

    public function __construct(BookRepo $book, UserRepo $user)
    {
        $this->book = $book;
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $d["requests"] = $this->book->getRequests();
        $d["books"] = $this->book->getAll();
        $d["users"] = $this->user->getUsersByRoleNames(['Student', 'Teacher', 'Companion', 'Admin', 'Super Admin']);

        return view('pages.books.requests.index', $d);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookRequestCreate $request)
    {
        $data = $request->except(["_token", "_method"]);
        $book = $this->book->find($data["book_id"]);

        if ($book->issued_copies >= $book->total_copies) {
            return Qs::json(__('msg.all_book_copies_issued'), false);
        }

        $data["user_id"] = auth()->id();
        $this->book->createRequest($data);
        $this->book->incrementColumn(["id" => $book->id], "issued_copies");

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($book_req_id)
    {
        $book_req_id = Qs::decodeHash($book_req_id);
        $d["book_request"] = $this->book->findRequest($book_req_id);
        $d["books"] = $this->book->getAll();

        return view("pages.books.requests.edit", $d);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookRequestUpdate $request, $book_req_id)
    {
        $data = $request->all();
        $book_id = $data["book_id"];
        $this->book->updateRequest($book_req_id, $data);

        $book = $this->book->find($book_id);

        if ($request->returned == 1) {
            $this->book->decrementColumn(["id" => $book_id], "issued_copies");
        } elseif ($request->was_returned == 1 && $request->returned == 0) {
            if ($book->issued_copies >= $book->total_copies)
                return Qs::json(__('msg.all_book_copies_issued'), false);
            $this->book->incrementColumn(["id" => $book_id], "issued_copies");
        }

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($book_req_id)
    {
        $book_req_id = Qs::decodeHash($book_req_id);
        $book_request = $this->book->findRequest($book_req_id);
        $book_id = $book_request->book_id;
        $book_request->delete();

        $this->book->decrementColumn(["id" => $book_id], "issued_copies");

        return Qs::deleteOk('book_requests.index');
    }
}
