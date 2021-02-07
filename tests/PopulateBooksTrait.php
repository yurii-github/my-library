<?php
namespace Tests;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Support\Carbon;

trait PopulateBooksTrait
{
    /**
     * @return Book[]
     */
    protected function populateBooks()
    {
        $books = [];
        $book = new Book();
        $book->title = 'title book #1';
        $book->file = new BookFile('filename-1');
        $book->created_date = Carbon::now()->toDateTimeString();
        $book->updated_date = Carbon::now()->toDateTimeString();
        $book->save();
        $books[] = $book;

        $book = new Book();
        $book->title = 'title book #2';
        $book->file = new BookFile('filename-2');
        $book->favorite = 1.0;
        $book->created_date = Carbon::now()->toDateTimeString();
        $book->updated_date = Carbon::now()->toDateTimeString();
        $book->save();
        $books[] = $book;

        $book = new Book();
        $book->title = 'title book #3';
        $book->file = new BookFile('filename-3');
        $book->favorite = 0.0;
        $book->created_date = Carbon::now()->toDateTimeString();
        $book->updated_date = Carbon::now()->toDateTimeString();
        $book->save();
        $books[] = $book;

        return $books;
    }
}