<?php
namespace Tests;

use App\Models\Book;

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
        $book->filename = 'filename-1';
        $book->created_date = '2014-01-01 00:00:00';
        $book->updated_date = '2014-01-01 00:00:00';
        $book->save();
        $books[] = $book;

        $book = new Book();
        $book->title = 'title book #2';
        $book->filename = 'filename-2';
        $book->favorite = 1.0;
        $book->created_date = '2014-01-01 00:00:00';
        $book->updated_date = '2014-01-01 00:00:00';
        $book->save();
        $books[] = $book;

        $book = new Book();
        $book->title = 'title book #3';
        $book->filename = 'filename-3';
        $book->favorite = 0.0;
        $book->created_date = '2014-01-01 00:00:00';
        $book->updated_date = '2014-01-01 00:00:00';
        $book->save();
        $books[] = $book;

        return $books;
    }
}