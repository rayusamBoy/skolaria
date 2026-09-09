<?php

namespace App\Repositories;

use App\Models\Book;
use App\Models\BookRequest;
use Illuminate\Database\Eloquent\Collection;

class BookRepo
{
    /*********** Books ***************/

    /**
     * Create a new book.
     */
    public function create(array $data): Book
    {
        return Book::create($data);
    }

    /**
     * Get all books with their requests and class, ordered by a given column.
     */
    public function getAllBooks(string $order_by = 'name'): Collection
    {
        return Book::with(['requests', 'my_class'])->orderBy($order_by)->get();
    }

    /**
     * Count all books in the system.
     */
    public function countBooks(): int
    {
        return Book::count();
    }

    /**
     * Update a book by ID.
     *
     * @return int Number of updated rows
     */
    public function update(int $id, array $data): int
    {
        return Book::where('id', $id)->update($data);
    }

    /**
     * Find a book by its primary key.
     */
    public function find(int $id): ?Book
    {
        return Book::find($id);
    }

    /**
     * Increment a numeric column for books matching conditions.
     */
    public function incrementColumn(array $where, string $column, int $amount = 1): int
    {
        return Book::where($where)->increment($column, $amount);
    }

    /**
     * Decrement a numeric column for books matching conditions.
     */
    public function decrementColumn(array $where, string $column, int $amount = 1): int
    {
        return Book::where($where)->decrement($column, $amount);
    }

    /*********** Book Requests ***************/

    /**
     * Create a new book request.
     */
    public function createBookRequest(array $data): BookRequest
    {
        return BookRequest::create($data);
    }

    /**
     * Get all book requests with related book, user, and borrower.
     */
    public function getAllBookRequestsWithRelations(): Collection
    {
        return BookRequest::with(['book', 'user', 'borrower'])->get();
    }

    /**
     * Get all book requests without any eager‑loaded relations.
     */
    public function getAllBookRequests(): Collection
    {
        return BookRequest::all();
    }

    /**
     * Update a book request by ID.
     *
     * @return int Number of updated rows
     */
    public function updateBookRequest(int $id, array $data): int
    {
        return BookRequest::where('id', $id)->update($data);
    }

    /**
     * Find a single book request by ID, with relations loaded.
     */
    public function findBookRequest(int $id): ?BookRequest
    {
        return BookRequest::with(['book', 'user', 'borrower'])->find($id);
    }
}
